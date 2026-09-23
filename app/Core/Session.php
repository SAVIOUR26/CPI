<?php

namespace App\Core;

class Session
{
    /** Input flashed by the previous request; kept for this request only. */
    private static ?array $old = null;

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => (Env::get('APP_URL', '') && str_starts_with((string) Env::get('APP_URL'), 'https://')),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('cpi_session');
            session_start();
        }
        if (self::$old === null) {
            self::$old = $_SESSION['_old'] ?? [];
            unset($_SESSION['_old']);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /** Re-fills forms on the next request only (secrets are never kept). */
    public static function flashInput(array $input): void
    {
        unset($input['_csrf'], $input['password'], $input['password_confirmation'], $input['current_password']);
        $_SESSION['_old'] = $input;
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return self::$old[$key] ?? $default;
    }

    public static function clearOld(): void
    {
        self::$old = [];
        unset($_SESSION['_old']);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
