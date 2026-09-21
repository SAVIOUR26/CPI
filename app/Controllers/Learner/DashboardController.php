<?php

namespace App\Controllers\Learner;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\FeeLedger;
use App\Models\Timetable;
use App\Models\User;

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

    public function profile(Request $request): void
    {
        $user = $this->requireAuth();
        $this->view('learner.profile', [
            'pageTitle' => 'My Profile — CPI',
            'user' => $user,
        ], 'layouts.dashboard');
    }

    public function updateProfile(Request $request): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'full_name' => 'required|max:150',
            'phone' => 'max:30',
        ]);

        User::update((int) $user['id'], $data);
        Auth::refreshAbilities();
        $this->flash('success', 'Profile updated.');
        $this->redirect('/learner/profile');
    }

    public function updatePassword(Request $request): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($request);

        $current = (string) $request->input('current_password');
        $data = $this->validate($request, ['password' => 'required|min:8|confirmed']);

        if (!password_verify($current, $user['password_hash'])) {
            $this->flash('error', 'Your current password is incorrect.');
            $this->redirect('/learner/profile');
            return;
        }

        User::update((int) $user['id'], ['password_hash' => password_hash($data['password'], PASSWORD_BCRYPT)]);
        $this->flash('success', 'Password updated.');
        $this->redirect('/learner/profile');
    }
}
