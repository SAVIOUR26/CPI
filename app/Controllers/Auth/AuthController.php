<?php

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    /** Portals offered on the login page. Everyone signs in with the same form; this picks where they land. */
    private const PORTALS = ['student', 'lecturer', 'staff'];
    private const STAFF_ROLES = ['super_admin', 'admissions', 'finance', 'registrar', 'content_manager'];

    public function showLogin(Request $request): void
    {
        $portal = $this->portal($request);
        if (Auth::check()) {
            $this->redirect($portal ? $this->portalHome($portal) : Auth::homeFor(Auth::roles()));
            return;
        }
        $titles = ['student' => 'Student Portal', 'lecturer' => 'Lecturer Portal', 'staff' => 'Staff login'];
        $this->view('auth.login', [
            'pageTitle' => ($portal ? $titles[$portal] : 'Login') . ' — CPI',
            'portal' => $portal,
        ], 'layouts.app');
    }

    /** /student-portal: a memorable address for students (sign in first if needed). */
    public function studentPortal(Request $request): void
    {
        $this->redirect(Auth::check() ? $this->portalHome('student') : '/login?portal=student');
    }

    /** /lecturer-portal: a memorable address for lecturers (sign in first if needed). */
    public function lecturerPortal(Request $request): void
    {
        $this->redirect(Auth::check() ? $this->portalHome('lecturer') : '/login?portal=lecturer');
    }

    public function login(Request $request): void
    {
        $this->verifyCsrf($request);
        $portal = $this->portal($request);
        $back = '/login' . ($portal ? '?portal=' . $portal : '');
        $data = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::tooManyAttempts($data['email'])) {
            $this->flash('error', 'Too many failed attempts. Please wait a few minutes and try again.');
            $this->redirect($back);
            return;
        }

        if (!Auth::attempt($data['email'], $data['password'])) {
            Auth::recordFailedAttempt($data['email']);
            Session::flashInput($request->all());
            $this->flash('error', 'Those credentials do not match our records.');
            $this->redirect($back);
            return;
        }

        Auth::clearAttempts($data['email']);
        $intended = Session::get('intended_url');
        Session::forget('intended_url');
        $this->redirect($intended ?: ($portal ? $this->portalHome($portal) : Auth::homeFor(Auth::roles())));
    }

    private function portal(Request $request): ?string
    {
        $portal = (string) $request->input('portal', '');
        return in_array($portal, self::PORTALS, true) ? $portal : null;
    }

    /** Where the signed-in user lands for the portal they chose; their own portal (with a note) if the account can't use it. */
    private function portalHome(string $portal): string
    {
        $roles = Auth::roles();
        $allowed = match ($portal) {
            'student' => ['learner'],
            'lecturer' => ['lecturer', 'super_admin'],
            default => self::STAFF_ROLES,
        };
        if (array_intersect($allowed, $roles)) {
            return match ($portal) {
                'student' => '/learner',
                'lecturer' => '/lecturer',
                default => '/admin',
            };
        }
        $this->flash('info', match ($portal) {
            'lecturer' => 'Your account does not have lecturer access yet. CPI administration can add it for you — you have been taken to your own portal.',
            'student' => 'Your account does not include the Student Portal, so you have been taken to your own portal.',
            default => 'Your account is not a staff account, so you have been taken to your own portal.',
        });
        return Auth::homeFor($roles);
    }

    public function showRegister(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::homeFor(Auth::roles()));
            return;
        }
        $this->view('auth.register', ['pageTitle' => 'Create an account — CPI'], 'layouts.app');
    }

    public function register(Request $request): void
    {
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'full_name' => 'required|max:150',
            'email' => 'required|email',
            'phone' => 'max:30',
            'password' => 'required|min:8|confirmed',
        ]);

        if (User::findByEmail($data['email'])) {
            Session::flashInput($request->all());
            $this->flash('error', 'An account with that email already exists. Try logging in instead.');
            $this->redirect('/register');
            return;
        }

        $userId = User::insert([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'status' => 'active',
        ]);

        User::assignRole($userId, 'learner');

        Mailer::send(
            $data['email'],
            $data['full_name'],
            'Welcome to Crawford Professionals Institute',
            "<p>Dear " . e($data['full_name']) . ",</p>
             <p>Your CPI account has been created. You can now browse our course catalogue and enrol in a programme.</p>
             <p><a href=\"" . url('/courses') . "\">Browse courses</a></p>
             <p>— Crawford Professionals Institute</p>"
        );

        Auth::login($userId);
        $this->flash('success', 'Welcome to CPI! Your account has been created.');
        $this->redirect(Auth::homeFor(Auth::roles()));
    }

    public function logout(Request $request): void
    {
        $this->verifyCsrf($request);
        Auth::logout();
        $this->redirect('/');
    }
}
