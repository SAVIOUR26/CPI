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
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::homeFor(Auth::roles()));
            return;
        }
        $this->view('auth.login', ['pageTitle' => 'Login — CPI'], 'layouts.app');
    }

    public function login(Request $request): void
    {
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::tooManyAttempts($data['email'])) {
            $this->flash('error', 'Too many failed attempts. Please wait a few minutes and try again.');
            $this->redirect('/login');
            return;
        }

        if (!Auth::attempt($data['email'], $data['password'])) {
            Auth::recordFailedAttempt($data['email']);
            Session::flashInput($request->all());
            $this->flash('error', 'Those credentials do not match our records.');
            $this->redirect('/login');
            return;
        }

        Auth::clearAttempts($data['email']);
        $intended = Session::get('intended_url');
        Session::forget('intended_url');
        $this->redirect($intended ?: Auth::homeFor(Auth::roles()));
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
