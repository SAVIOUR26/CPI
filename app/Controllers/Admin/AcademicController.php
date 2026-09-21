<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\AcademicApplication;
use App\Models\AcademicProgramme;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeeLedger;
use App\Models\Intake;
use App\Models\Timetable;
use App\Models\User;
use App\Support\Str;

class AcademicController extends Controller
{
    public function programmes(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $programmes = AcademicProgramme::published();
        $allProgrammes = AcademicProgramme::query(
            'SELECT ap.*, c.title, c.status FROM academic_programmes ap JOIN courses c ON c.id = ap.course_id ORDER BY c.title'
        );

        $this->view('admin.academic.programmes', [
            'pageTitle' => 'Academic Programmes — CPI Admin',
            'programmes' => $allProgrammes,
        ], 'layouts.dashboard');
    }

    public function createProgramme(Request $request): void
    {
        $user = $this->requirePermissionAndUser('academic.manage');
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'award_level' => 'required|in:certificate,diploma,degree',
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (Course::findBySlug($slug)) {
            $slug = $slugBase . '-' . (++$i);
        }

        $courseId = Course::insert([
            'title' => $data['title'],
            'slug' => $slug,
            'summary' => $request->input('summary'),
            'description' => $request->input('description'),
            'programme_type' => 'academic',
            'level' => 'advanced',
            'duration_note' => $request->input('duration_note'),
            'price_amount' => 0,
            'is_public' => 0,
            'status' => $request->input('status', 'published'),
            'created_by' => $user['id'],
        ]);

        AcademicProgramme::insert([
            'course_id' => $courseId,
            'award_level' => $data['award_level'],
            'duration_note' => $request->input('duration_note'),
            'entry_requirements' => $request->input('entry_requirements'),
        ]);

        $this->flash('success', 'Academic programme created. It is reachable only via direct link at /academic.');
        $this->redirect('/admin/academic/programmes');
    }

    public function applications(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $applications = AcademicApplication::query(
            'SELECT aa.*, c.title AS programme_title FROM academic_applications aa
             JOIN academic_programmes ap ON ap.id = aa.programme_id JOIN courses c ON c.id = ap.course_id
             ORDER BY aa.created_at DESC'
        );

        $this->view('admin.academic.applications', [
            'pageTitle' => 'Academic Admissions — CPI Admin',
            'applications' => $applications,
        ], 'layouts.dashboard');
    }

    public function showApplication(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $app = AcademicApplication::find((int) $request->param('application'));
        if (!$app) {
            $this->abort(404, 'Application not found.');
            return;
        }
        $programme = AcademicProgramme::withCourse((int) $app['programme_id']);
        $intakes = Intake::query('SELECT * FROM intakes WHERE course_id = ? ORDER BY start_date DESC', [$programme['course_id']]);

        $this->view('admin.academic.application-show', [
            'pageTitle' => 'Application — ' . $app['applicant_name'],
            'app' => $app,
            'programme' => $programme,
            'intakes' => $intakes,
        ], 'layouts.dashboard');
    }

    public function applicationDocuments(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $app = AcademicApplication::find((int) $request->param('application'));
        if (!$app || !$app['documents_path'] || !\App\Core\Upload::exists($app['documents_path'])) {
            $this->abort(404, 'Documents not found.');
            return;
        }
        header('Content-Type: ' . \App\Core\Upload::mimeFor($app['documents_path']));
        header('Content-Disposition: inline');
        readfile(\App\Core\Upload::absolutePath($app['documents_path']));
        exit;
    }

    public function decide(Request $request): void
    {
        $user = $this->requirePermissionAndUser('admissions.manage');
        $this->verifyCsrf($request);
        $appId = (int) $request->param('application');
        $app = AcademicApplication::find($appId);
        if (!$app) {
            $this->abort(404, 'Application not found.');
            return;
        }

        $decision = $request->input('decision'); // 'admitted' | 'rejected'
        $note = $request->input('decision_note');
        $intakeId = $request->input('intake_id') ?: null;

        $update = [
            'status' => $decision,
            'decision_note' => $note,
            'reviewed_by' => $user['id'],
            'decided_at' => date('Y-m-d H:i:s'),
        ];

        if ($decision === 'admitted') {
            $existingUser = User::findByEmail($app['email']);
            $tempPassword = null;
            if (!$existingUser) {
                $tempPassword = bin2hex(random_bytes(5));
                $userId = User::insert([
                    'full_name' => $app['applicant_name'],
                    'email' => $app['email'],
                    'phone' => $app['phone'],
                    'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
                    'status' => 'active',
                ]);
            } else {
                $userId = (int) $existingUser['id'];
            }
            User::assignRole($userId, 'learner');
            $update['user_id'] = $userId;
            $update['intake_id'] = $intakeId;

            if ($intakeId && !Enrollment::existing($userId, (int) $intakeId)) {
                Enrollment::insert([
                    'user_id' => $userId,
                    'intake_id' => $intakeId,
                    'source' => 'academic_admission',
                    'status' => 'pending_payment',
                ]);
            }

            $loginNote = $tempPassword ? "<p>Temporary password: <strong>" . e($tempPassword) . "</strong></p>" : '';
            Mailer::send(
                $app['email'],
                $app['applicant_name'],
                'Admission decision — Crawford Professionals Institute',
                "<p>Dear " . e($app['applicant_name']) . ",</p><p>Congratulations — you have been admitted.</p>"
                . "<p>Login to complete your enrolment and payment: <a href=\"" . url('/login') . "\">" . url('/login') . "</a></p>"
                . $loginNote
            );
        } else {
            Mailer::send(
                $app['email'],
                $app['applicant_name'],
                'Admission decision — Crawford Professionals Institute',
                "<p>Dear " . e($app['applicant_name']) . ",</p><p>Thank you for applying. After review, we are unable to offer you a place at this time.</p>"
            );
        }

        AcademicApplication::update($appId, $update);
        AuditLog::record('academic_application.decide', 'academic_application', $appId, ['decision' => $decision]);
        $this->flash('success', 'Decision recorded and applicant notified.');
        $this->redirect('/admin/academic/applications');
    }

    public function addFee(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'user_id' => 'required|integer',
            'intake_id' => 'required|integer',
            'description' => 'required|max:190',
            'amount_due' => 'required|numeric',
        ]);

        FeeLedger::insert([
            'user_id' => $data['user_id'],
            'intake_id' => $data['intake_id'],
            'description' => $data['description'],
            'amount_due' => $data['amount_due'],
            'due_date' => $request->input('due_date') ?: null,
        ]);

        $this->flash('success', 'Fee ledger entry added.');
        $this->back();
    }

    public function addTimetableEntry(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'intake_id' => 'required|integer',
            'day_of_week' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        Timetable::insert([
            'intake_id' => $data['intake_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'venue' => $request->input('venue'),
            'lecturer_id' => $request->input('lecturer_id') ?: null,
        ]);

        $this->flash('success', 'Timetable entry added.');
        $this->back();
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
