<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function showForgot(Request $request): void
    {
        $this->view('auth.forgot-password', ['pageTitle' => 'Forgot password — CPI']);
    }

    public function sendLink(Request $request): void
    {
        $this->verifyCsrf($request);
        $email = trim((string) $request->input('email'));
        $user = User::findByEmail($email);

        // Always show the same message, whether or not the account exists, to avoid leaking who has an account.
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $stmt = Database::connection()->prepare(
                'INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 60 MINUTE))'
            );
            $stmt->execute([$email, password_hash($token, PASSWORD_BCRYPT)]);

            $link = url('/reset-password?email=' . rawurlencode($email) . '&token=' . $token);
            Mailer::send(
                $email,
                $user['full_name'],
                'Reset your CPI password',
                "<p>Dear " . e($user['full_name']) . ",</p>
                 <p>We received a request to reset your password. This link expires in 60 minutes:</p>
                 <p><a href=\"" . $link . "\">Reset your password</a></p>
                 <p>If you didn't request this, you can safely ignore this email.</p>"
            );
        }

        $this->flash('success', 'If that email is registered with us, a reset link is on its way.');
        $this->redirect('/forgot-password');
    }

    public function showReset(Request $request): void
    {
        $this->view('auth.reset-password', [
            'pageTitle' => 'Reset password — CPI',
            'email' => $request->input('email', ''),
            'token' => $request->input('token', ''),
        ]);
    }

    public function reset(Request $request): void
    {
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $rows = Database::connection()->prepare(
            'SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 5'
        );
        $rows->execute([$data['email']]);
        $candidates = $rows->fetchAll();

        $valid = false;
        foreach ($candidates as $row) {
            if (password_verify($data['token'], $row['token_hash'])) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            $this->flash('error', 'That reset link is invalid or has expired. Please request a new one.');
            $this->redirect('/forgot-password');
            return;
        }

        $user = User::findByEmail($data['email']);
        if (!$user) {
            $this->flash('error', 'Account not found.');
            $this->redirect('/forgot-password');
            return;
        }

        User::update((int) $user['id'], ['password_hash' => password_hash($data['password'], PASSWORD_BCRYPT)]);
        Database::connection()->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$data['email']]);

        $this->flash('success', 'Your password has been reset. You can now log in.');
        $this->redirect('/login');
    }
}
