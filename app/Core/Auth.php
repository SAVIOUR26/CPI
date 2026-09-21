<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    private const MAX_ATTEMPTS = 6;
    private const LOCKOUT_SECONDS = 300;

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        static $cached = null;
        if (!self::check()) {
            return null;
        }
        if ($cached === null || $cached['id'] !== self::id()) {
            $cached = User::find(self::id());
        }
        return $cached;
    }

    /** @return string[] */
    public static function roles(): array
    {
        if (!self::check()) {
            return [];
        }
        return Session::get('user_roles', []);
    }

    public static function hasRole(string ...$slugs): bool
    {
        return (bool) array_intersect($slugs, self::roles());
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }
        $perms = Session::get('user_permissions', []);
        return in_array($permission, $perms, true);
    }

    private static function throttleKey(string $email): string
    {
        return 'login_attempts_' . sha1(strtolower($email));
    }

    public static function tooManyAttempts(string $email): bool
    {
        $data = Session::get(self::throttleKey($email));
        if (!$data) {
            return false;
        }
        if ($data['count'] >= self::MAX_ATTEMPTS && (time() - $data['first']) < self::LOCKOUT_SECONDS) {
            return true;
        }
        return false;
    }

    public static function recordFailedAttempt(string $email): void
    {
        $key = self::throttleKey($email);
        $data = Session::get($key, ['count' => 0, 'first' => time()]);
        if ((time() - $data['first']) > self::LOCKOUT_SECONDS) {
            $data = ['count' => 0, 'first' => time()];
        }
        $data['count']++;
        Session::put($key, $data);
    }

    public static function clearAttempts(string $email): void
    {
        Session::forget(self::throttleKey($email));
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || $user['status'] !== 'active') {
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        self::login((int) $user['id']);
        return true;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::put('user_id', $userId);
        Session::put('user_roles', User::roles($userId));
        Session::put('user_permissions', User::permissions($userId));
        User::update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public static function refreshAbilities(): void
    {
        if (self::check()) {
            Session::put('user_roles', User::roles(self::id()));
            Session::put('user_permissions', User::permissions(self::id()));
        }
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    /** Where an authenticated user should land after login, based on role. */
    public static function homeFor(array $roles): string
    {
        if (array_intersect($roles, ['super_admin', 'admissions', 'finance', 'registrar', 'content_manager'])) {
            return '/admin';
        }
        if (in_array('lecturer', $roles, true)) {
            return '/lecturer';
        }
        if (in_array('corporate_contact', $roles, true)) {
            return '/corporate/portal';
        }
        return '/learner';
    }
}
