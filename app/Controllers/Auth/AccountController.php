<?php

namespace App\Controllers\Auth;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

/**
 * "My account" — name, email, phone and password — for every portal. The same
 * page is served under each portal's URL so the dashboard layout shows that
 * portal's menu (it picks the sidebar from the URL prefix).
 */
class AccountController extends Controller
{
    /** Account page per portal => roles that may open it (null: any signed-in user). */
    private const PAGES = [
        '/admin/account' => ['super_admin', 'admissions', 'finance', 'registrar', 'content_manager'],
        '/lecturer/account' => ['lecturer', 'super_admin'],
        '/corporate/portal/account' => ['corporate_contact', 'super_admin'],
        '/learner/profile' => null,
    ];

    /** The account page of the portal a user with these roles normally uses. */
    public static function pageFor(array $roles): string
    {
        foreach (self::PAGES as $page => $allowed) {
            if ($allowed === null || array_intersect($allowed, $roles)) {
                return $page;
            }
        }
        return '/learner/profile';
    }

    public function show(Request $request): void
    {
        [$user, $page] = $this->context();
        $this->view('account.show', [
            'pageTitle' => 'My account — CPI',
            'user' => $user,
            'page' => $page,
        ], 'layouts.dashboard');
    }

    public function updateProfile(Request $request): void
    {
        [$user, $page] = $this->context();
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'full_name' => 'required|max:150',
            'email' => 'required|email|max:190',
            'phone' => 'max:30',
        ]);

        $update = ['full_name' => trim($data['full_name']), 'phone' => trim((string) $data['phone']) ?: null];
        $email = strtolower(trim($data['email']));
        if ($email !== strtolower($user['email'])) {
            // The email is the sign-in name, so changing it needs the password too.
            if (!password_verify((string) $request->input('password_check'), $user['password_hash'])) {
                $this->failBack($page, $request, 'password_check', 'Enter your current password to change your email address.');
            }
            $other = User::findByEmail($email);
            if ($other && (int) $other['id'] !== (int) $user['id']) {
                $this->failBack($page, $request, 'email', 'Another account already uses this email address.');
            }
            $update['email'] = $email;
        }

        User::update((int) $user['id'], $update);
        AuditLog::record('account.update', 'user', (int) $user['id'], isset($update['email']) ? ['email_changed' => true] : []);
        $this->flash('success', isset($update['email'])
            ? 'Details saved. Sign in with ' . $email . ' from now on.'
            : 'Details saved.');
        $this->redirect($page);
    }

    public function updatePassword(Request $request): void
    {
        [$user, $page] = $this->context();
        $this->verifyCsrf($request);
        $data = $this->validate($request, ['password' => 'required|min:8|confirmed']);

        if (!password_verify((string) $request->input('current_password'), $user['password_hash'])) {
            $this->failBack($page, $request, 'current_password', 'Your current password is incorrect.');
        }
        if (password_verify($data['password'], $user['password_hash'])) {
            $this->failBack($page, $request, 'password', 'Choose a new password that is different from your current one.');
        }

        User::update((int) $user['id'], ['password_hash' => password_hash($data['password'], PASSWORD_BCRYPT)]);
        Session::regenerate();
        AuditLog::record('account.password_change', 'user', (int) $user['id']);
        $this->flash('success', 'Password changed.');
        $this->redirect($page);
    }

    /** @return array{0: array, 1: string} the signed-in user and the account page for the portal in the URL */
    private function context(): array
    {
        $user = $this->requireAuth();
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $page = '/learner/profile';
        foreach (array_keys(self::PAGES) as $candidate) {
            if (str_starts_with($path, $candidate)) {
                $page = $candidate;
                break;
            }
        }
        $allowed = self::PAGES[$page];
        if ($allowed !== null && !array_intersect($allowed, Auth::roles())) {
            $this->redirect(self::pageFor(Auth::roles()));
        }
        return [$user, $page];
    }

    private function failBack(string $page, Request $request, string $field, string $message): void
    {
        Session::flashInput($request->all());
        Session::flash('errors', [$field => [$message]]);
        $this->redirect($page);
    }
}
