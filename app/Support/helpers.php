<?php

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) Env::get('APP_URL', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return (string) Session::old($key, $default);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('all_errors')) {
    /**
     * Pulls the flashed validation errors array exactly once per request
     * (flash messages are popped on read), caching it for repeated calls
     * to field_errors() within the same request/response cycle.
     */
    function all_errors(): array
    {
        static $errors = null;
        if ($errors === null) {
            $errors = Session::getFlash('errors', []);
        }
        return $errors;
    }
}

if (!function_exists('field_errors')) {
    /** @return string[] */
    function field_errors(string $field): array
    {
        return all_errors()[$field] ?? [];
    }
}

if (!function_exists('money')) {
    function money(float|string|null $amount, string $currency = 'UGX'): string
    {
        return \App\Support\Str::money((float) ($amount ?? 0), $currency);
    }
}

if (!function_exists('date_pretty')) {
    function date_pretty(?string $date, string $format = 'j M Y'): string
    {
        if (!$date) {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : '—';
    }
}
