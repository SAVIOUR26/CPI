<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\User;
use App\Support\CertificatePdf;

class CertificateController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('certificates.issue');
        $certificates = Certificate::query(
            'SELECT ct.*, u.full_name, u.email, c.title AS course_title FROM certificates ct
             JOIN users u ON u.id = ct.user_id JOIN intakes i ON i.id = ct.intake_id JOIN courses c ON c.id = i.course_id
             ORDER BY ct.issued_at DESC LIMIT 100'
        );
        $intakes = Intake::query(
            'SELECT i.*, c.title AS course_title FROM intakes i JOIN courses c ON c.id = i.course_id ORDER BY i.start_date DESC'
        );

        $this->view('admin.certificates.index', [
            'pageTitle' => 'Certificates — CPI Admin',
            'certificates' => $certificates,
            'intakes' => $intakes,
        ], 'layouts.dashboard');
    }

    public function rosterFor(Request $request): void
    {
        $this->requirePermission('certificates.issue');
        $intakeId = (int) $request->param('intake');
        $intake = Intake::withCourse($intakeId);
        if (!$intake) {
            $this->abort(404, 'Intake not found.');
            return;
        }
        $roster = Intake::roster($intakeId);

        $this->view('admin.certificates.roster', [
            'pageTitle' => 'Issue Certificates — ' . ($intake['course_title'] ?? ''),
            'intake' => $intake,
            'roster' => $roster,
        ], 'layouts.dashboard');
    }

    public function issue(Request $request): void
    {
        $user = $this->requirePermissionAndUser('certificates.issue');
        $this->verifyCsrf($request);

        $intakeId = (int) $request->param('intake');
        $intake = Intake::withCourse($intakeId);
        if (!$intake) {
            $this->abort(404, 'Intake not found.');
            return;
        }

        $userIds = array_map('intval', (array) $request->input('user_ids', []));
        $title = $request->input('title') ?: ('Certificate in ' . $intake['course_title']);
        $issued = 0;

        foreach ($userIds as $learnerId) {
            $learner = User::find($learnerId);
            if (!$learner || !Enrollment::isActiveFor($learnerId, $intakeId)) {
                continue;
            }

            $existing = Certificate::query('SELECT id FROM certificates WHERE user_id = ? AND intake_id = ?', [$learnerId, $intakeId]);
            if ($existing) {
                continue;
            }

            $code = Certificate::generateCode();
            $certId = Certificate::insert([
                'code' => $code,
                'user_id' => $learnerId,
                'intake_id' => $intakeId,
                'title' => $title,
                'issued_at' => date('Y-m-d'),
                'issued_by' => $user['id'],
            ]);

            $relativePath = 'certificates/' . $code . '.pdf';
            $absolutePath = \App\Core\Upload::absolutePath($relativePath);
            CertificatePdf::saveTo($absolutePath, [
                'code' => $code,
                'title' => $title,
                'issued_at' => date('Y-m-d'),
            ], $learner['full_name'], $intake['course_title'], $intake['code']);

            Certificate::update($certId, ['pdf_path' => $relativePath]);
            Enrollment::statement(
                'UPDATE enrollments SET status = "completed", completed_at = NOW() WHERE user_id = ? AND intake_id = ?',
                [$learnerId, $intakeId]
            );

            Mailer::send(
                $learner['email'],
                $learner['full_name'],
                'Your CPI certificate is ready — ' . $intake['course_title'],
                '<p>Dear ' . e($learner['full_name']) . ',</p><p>Congratulations! Your certificate for <strong>'
                . e($intake['course_title']) . '</strong> has been issued.</p>'
                . '<p>You can download it from your learner dashboard, or verify it any time at '
                . '<a href="' . url('/verify/' . $code) . '">' . url('/verify/' . $code) . '</a>.</p>'
            );

            AuditLog::record('certificate.issue', 'certificate', $certId, ['code' => $code]);
            $issued++;
        }

        $this->flash('success', "$issued certificate(s) issued.");
        $this->redirect('/admin/certificates/roster/' . $intakeId);
    }

    public function revoke(Request $request): void
    {
        $this->requirePermission('certificates.issue');
        $this->verifyCsrf($request);
        $id = (int) $request->param('certificate');
        Certificate::update($id, ['revoked' => 1]);
        AuditLog::record('certificate.revoke', 'certificate', $id);
        $this->flash('success', 'Certificate revoked.');
        $this->redirect('/admin/certificates');
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
