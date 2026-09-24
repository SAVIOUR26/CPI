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
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\FeeLedger;
use App\Models\Intake;
use App\Models\User;
use App\Support\ApplicationDocuments;
use App\Support\NewAccounts;
use App\Support\Str;

class AcademicController extends Controller
{
    public const APPLICATION_STATUSES = ['submitted', 'under_review', 'admitted', 'rejected'];
    private const PROGRAMME_STATUSES = ['published', 'draft', 'archived'];
    /** courses.level used for each award level (the course row only backs the programme). */
    private const COURSE_LEVELS = ['certificate' => 'foundation', 'diploma' => 'intermediate', 'degree' => 'advanced', 'postgraduate' => 'advanced'];

    public function programmes(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $programmes = AcademicProgramme::listAll();
        $counts = [];
        foreach (AcademicApplication::query('SELECT programme_id, COUNT(*) AS n FROM academic_applications GROUP BY programme_id') as $row) {
            $counts[(int) $row['programme_id']] = (int) $row['n'];
        }

        $this->view('admin.academic.programmes', [
            'pageTitle' => 'Academic Programmes — CPI Admin',
            'programmes' => $programmes,
            'groups' => AcademicProgramme::grouped($programmes),
            'levels' => AcademicProgramme::levels(),
            'applicationCounts' => $counts,
        ], 'layouts.dashboard');
    }

    public function newProgramme(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->view('admin.academic.programme-form', [
            'pageTitle' => 'New academic programme — CPI Admin',
            'programme' => null,
            'levels' => AcademicProgramme::levels(),
            'categories' => CourseCategory::all('sort_order'),
            'applicationCount' => 0,
        ], 'layouts.dashboard');
    }

    public function createProgramme(Request $request): void
    {
        $user = $this->requirePermissionAndUser('academic.manage');
        $this->verifyCsrf($request);
        $data = $this->validate($request, $this->programmeRules());
        $fields = $this->programmeFields($request, $data);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (Course::findBySlug($slug)) {
            $slug = $slugBase . '-' . (++$i);
        }

        $courseId = Course::insert($fields['course'] + [
            'slug' => $slug,
            'programme_type' => 'academic',
            'price_amount' => null,
            'is_public' => 0, // listed on /academic, not in the short-course catalogue
            'created_by' => $user['id'],
        ]);
        $id = AcademicProgramme::insert($fields['programme'] + ['course_id' => $courseId]);
        AuditLog::record('academic_programme.create', 'academic_programme', $id, ['title' => $data['title']]);

        $this->flash('success', $data['status'] === 'published'
            ? 'Programme created and listed on the public Academic Programmes page.'
            : 'Programme saved as ' . $data['status'] . ' — it is not shown publicly until you publish it.');
        $this->redirect('/admin/academic/programmes/' . $id);
    }

    public function editProgramme(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $programme = $this->findProgramme((int) $request->param('programme'));

        $this->view('admin.academic.programme-form', [
            'pageTitle' => 'Edit — ' . $programme['title'],
            'programme' => $programme,
            'levels' => AcademicProgramme::levels(),
            'categories' => CourseCategory::all('sort_order'),
            'applicationCount' => AcademicApplication::count('programme_id = ?', [$programme['id']]),
        ], 'layouts.dashboard');
    }

    public function updateProgramme(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $programme = $this->findProgramme((int) $request->param('programme'));
        $data = $this->validate($request, $this->programmeRules());
        $fields = $this->programmeFields($request, $data);

        // The slug is kept so existing links keep working; public pages use the programme id.
        Course::update((int) $programme['course_id'], $fields['course']);
        AcademicProgramme::update((int) $programme['id'], $fields['programme']);
        AuditLog::record('academic_programme.update', 'academic_programme', (int) $programme['id']);

        $this->flash('success', 'Programme updated.');
        $this->redirect('/admin/academic/programmes/' . $programme['id']);
    }

    private function programmeRules(): array
    {
        return [
            'title' => 'required|max:190',
            'award_level' => 'required|in:' . implode(',', array_keys(self::COURSE_LEVELS)),
            'summary' => 'max:500',
            'awarding_body' => 'max:190',
            'duration_note' => 'max:100',
            'sort_order' => 'integer',
            'status' => 'required|in:' . implode(',', self::PROGRAMME_STATUSES),
        ];
    }

    /** Splits the programme form into its courses row and academic_programmes row. */
    private function programmeFields(Request $request, array $data): array
    {
        $text = fn (string $key): ?string => trim((string) $request->input($key, '')) !== '' ? trim((string) $request->input($key)) : null;
        $duration = $text('duration_note');

        return [
            'course' => [
                'category_id' => $request->input('category_id') ?: null,
                'title' => trim($data['title']),
                'summary' => $text('summary'),
                'description' => $text('description'),
                'level' => self::COURSE_LEVELS[$data['award_level']],
                'duration_note' => $duration,
                'status' => $data['status'],
            ],
            'programme' => [
                'award_level' => $data['award_level'],
                'awarding_body' => $text('awarding_body'),
                'duration_note' => $duration,
                'entry_requirements' => $text('entry_requirements'),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ],
        ];
    }

    private function findProgramme(int $id): array
    {
        $programme = AcademicProgramme::withCourse($id);
        if (!$programme) {
            $this->abort(404, 'Programme not found.');
        }
        return $programme;
    }

    public function applications(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $status = (string) $request->input('status', '');
        $status = in_array($status, self::APPLICATION_STATUSES, true) ? $status : '';

        $applications = AcademicApplication::query(
            'SELECT aa.id, aa.application_no, aa.applicant_name, aa.email, aa.phone, aa.status, aa.created_at, aa.decided_at,
                    ap.award_level, c.title AS programme_title
             FROM academic_applications aa
             JOIN academic_programmes ap ON ap.id = aa.programme_id
             JOIN courses c ON c.id = ap.course_id'
            . ($status ? ' WHERE aa.status = ?' : '') . '
             ORDER BY aa.created_at DESC, aa.id DESC',
            $status ? [$status] : []
        );
        $counts = array_fill_keys(self::APPLICATION_STATUSES, 0);
        foreach (AcademicApplication::query('SELECT status, COUNT(*) AS n FROM academic_applications GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['n'];
        }

        $this->view('admin.academic.applications', [
            'pageTitle' => 'Academic Admissions — CPI Admin',
            'applications' => $applications,
            'status' => $status,
            'counts' => $counts,
            'levels' => AcademicProgramme::levels(),
        ], 'layouts.dashboard');
    }

    public function showApplication(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $app = $this->findApplication((int) $request->param('application'));
        $programme = AcademicProgramme::withCourse((int) $app['programme_id']);
        $intakes = Intake::query('SELECT * FROM intakes WHERE course_id = ? ORDER BY start_date DESC', [$programme['course_id']]);
        $form = json_decode((string) $app['form_data'], true) ?: [];

        $this->view('admin.academic.application-show', [
            'pageTitle' => ($app['application_no'] ?: 'Application') . ' — ' . $app['applicant_name'],
            'app' => $app,
            'programme' => $programme,
            'level' => AcademicProgramme::levels()[$programme['award_level']] ?? null,
            'intakes' => $intakes,
            'form' => $form,
            'documents' => ApplicationDocuments::list($app, '/admin/academic/applications/' . (int) $app['id'] . '/files/'),
            'reviewer' => $app['reviewed_by'] ? User::find((int) $app['reviewed_by']) : null,
        ], 'layouts.dashboard');
    }

    /** Legacy single-file upload (applications made before the full online form). */
    public function applicationDocuments(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $app = $this->findApplication((int) $request->param('application'));
        $path = ApplicationDocuments::path($app, 'legacy', 0);
        if (!$path) {
            $this->abort(404, 'Documents not found.');
        }
        ApplicationDocuments::send($path, 'application-' . $app['id']);
    }

    public function applicationFile(Request $request): void
    {
        $this->requirePermission('admissions.manage');
        $app = $this->findApplication((int) $request->param('application'));
        $key = (string) $request->param('key');
        $index = max(0, (int) $request->input('i', 0));
        $path = ApplicationDocuments::path($app, $key, $index);
        if (!$path) {
            $this->abort(404, 'Document not found.');
        }
        ApplicationDocuments::send($path, ApplicationDocuments::downloadName($app, $key, $index));
    }

    private function findApplication(int $id): array
    {
        $app = AcademicApplication::find($id);
        if (!$app) {
            $this->abort(404, 'Application not found.');
        }
        return $app;
    }

    public function decide(Request $request): void
    {
        $user = $this->requirePermissionAndUser('admissions.manage');
        $this->verifyCsrf($request);
        $app = $this->findApplication((int) $request->param('application'));
        $appId = (int) $app['id'];
        $back = '/admin/academic/applications/' . $appId;

        $decision = (string) $request->input('decision');
        if (!in_array($decision, ['under_review', 'admitted', 'rejected'], true)) {
            $this->flash('error', 'Please choose a valid decision.');
            $this->redirect($back);
        }
        if (in_array($app['status'], ['admitted', 'rejected'], true)) {
            $this->flash('error', 'A decision has already been recorded for this application.');
            $this->redirect($back);
        }

        $note = trim((string) $request->input('decision_note', ''));
        $update = [
            'status' => $decision,
            'decision_note' => $note !== '' ? $note : $app['decision_note'],
            'reviewed_by' => $user['id'],
        ];

        if ($decision === 'under_review') {
            AcademicApplication::update($appId, $update);
            AuditLog::record('academic_application.review', 'academic_application', $appId);
            $this->flash('success', 'Application marked as under review. The applicant has not been notified.');
            $this->redirect($back);
        }

        $programme = AcademicProgramme::withCourse((int) $app['programme_id']);
        $ref = $app['application_no'] ? ' (' . $app['application_no'] . ')' : '';
        $greeting = '<p>Dear ' . e($app['applicant_name']) . ',</p>';
        $signoff = '<p>Crawford Professionals Institute — Admissions</p>';
        $update['decided_at'] = date('Y-m-d H:i:s');

        if ($decision === 'admitted') {
            $intakeId = (int) $request->input('intake_id', 0) ?: null;
            if ($intakeId) {
                $intake = Intake::find($intakeId);
                if (!$intake || (int) $intake['course_id'] !== (int) $programme['course_id']) {
                    $this->flash('error', 'That intake does not belong to this programme.');
                    $this->redirect($back);
                }
            }

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

            if ($intakeId && !Enrollment::existing($userId, $intakeId)) {
                Enrollment::insert([
                    'user_id' => $userId,
                    'intake_id' => $intakeId,
                    'source' => 'academic_admission',
                    'status' => 'pending_payment',
                ]);
            }

            $login = $tempPassword
                ? '<p>We have created your Student Portal account:<br>Email: <strong>' . e($app['email']) . '</strong><br>Temporary password: <strong>'
                  . e($tempPassword) . '</strong><br>Please change this password after you first log in.</p>'
                : '<p>Log in to the Student Portal with your existing CPI account (' . e($app['email']) . ').</p>';
            $emailed = Mailer::send(
                $app['email'],
                $app['applicant_name'],
                'Offer of admission' . $ref . ' — Crawford Professionals Institute',
                $greeting . '<p>Congratulations! Following review of your application' . e($ref) . ', you have been offered admission to the <strong>'
                . e($programme['title']) . '</strong>' . ($programme['awarding_body'] ? ', awarded by ' . e($programme['awarding_body']) : '') . '.</p>'
                . $login . '<p>Log in at <a href="' . e(url('/login')) . '">' . e(url('/login')) . '</a> to complete your registration and fee payment.</p>'
                . '<p>Please keep your original academic documents — you may be asked to present them for verification.</p>' . $signoff
            );
            if ($tempPassword) {
                NewAccounts::add($app['applicant_name'], $app['email'], $tempPassword, 'student', $emailed);
            }
            $message = 'Applicant admitted' . ($emailed ? ' and the offer emailed' : ' — the offer email could not be sent, so let them know directly')
                . ($intakeId ? ', with an enrolment awaiting payment.' : '. Assign an intake when one is available.');
        } else {
            Mailer::send(
                $app['email'],
                $app['applicant_name'],
                'Your application' . $ref . ' — Crawford Professionals Institute',
                $greeting . '<p>Thank you for applying for the <strong>' . e($programme['title']) . '</strong> at Crawford Professionals Institute.</p>'
                . '<p>After careful review, we are unable to offer you a place on this programme at this time. You are welcome to contact our '
                . 'Admissions team to discuss other programmes or future intakes that may suit you.</p>' . $signoff
            );
            $message = 'Decision recorded and the applicant has been notified.';
        }

        AcademicApplication::update($appId, $update);
        AuditLog::record('academic_application.decide', 'academic_application', $appId, ['decision' => $decision]);
        $this->flash('success', $message);
        $this->redirect($back);
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

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
