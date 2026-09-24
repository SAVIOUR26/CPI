<?php

namespace App\Controllers\Learner;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Upload;
use App\Models\AcademicApplication;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\FeeLedger;
use App\Models\Timetable;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $user = $this->requireAuth();
        $enrollments = Enrollment::forUser((int) $user['id']);
        $certificates = Certificate::forUser((int) $user['id']);
        $timetable = Timetable::forUser((int) $user['id']);

        $active = array_filter($enrollments, fn($e) => $e['status'] === 'active');
        $pending = array_filter($enrollments, fn($e) => $e['status'] === 'pending_payment');

        $this->view('learner.dashboard', [
            'pageTitle' => 'My Dashboard — CPI',
            'active' => $active,
            'pending' => $pending,
            'certificates' => array_slice($certificates, 0, 3),
            'timetable' => array_slice($timetable, 0, 5),
            'announcements' => Announcement::forStudent((int) $user['id'], 3),
            'dates' => CalendarEvent::forStudent((int) $user['id'], date('Y-m-d'), 4),
            'applications' => AcademicApplication::forUser((int) $user['id']),
        ], 'layouts.dashboard');
    }

    public function courses(Request $request): void
    {
        $user = $this->requireAuth();
        $enrollments = Enrollment::forUser((int) $user['id']);

        $this->view('learner.courses', [
            'pageTitle' => 'My Courses — CPI',
            'enrollments' => $enrollments,
        ], 'layouts.dashboard');
    }

    public function certificates(Request $request): void
    {
        $user = $this->requireAuth();
        $certificates = Certificate::forUser((int) $user['id']);

        $this->view('learner.certificates', [
            'pageTitle' => 'My Certificates — CPI',
            'certificates' => $certificates,
        ], 'layouts.dashboard');
    }

    public function downloadCertificate(Request $request): void
    {
        $user = $this->requireAuth();
        $code = (string) $request->param('code');
        $certificate = Certificate::findByCode($code);

        if (!$certificate || (int) $certificate['user_id'] !== (int) $user['id'] || !$certificate['pdf_path'] || !Upload::exists($certificate['pdf_path'])) {
            $this->abort(404, 'Certificate not found.');
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $code . '.pdf"');
        readfile(Upload::absolutePath($certificate['pdf_path']));
        exit;
    }

    public function fees(Request $request): void
    {
        $user = $this->requireAuth();
        $ledger = FeeLedger::forUser((int) $user['id']);

        $this->view('learner.fees', [
            'pageTitle' => 'My Fees — CPI',
            'ledger' => $ledger,
        ], 'layouts.dashboard');
    }
}
