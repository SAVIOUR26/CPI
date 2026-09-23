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

if (!function_exists('course_price')) {
    /** Courses without a price are quoted on request (typically corporate/custom programmes). */
    function course_price(array $course): string
    {
        $amount = (float) ($course['price_amount'] ?? 0);
        return $amount > 0 ? money($amount, $course['price_currency'] ?? 'UGX') : 'Price on request';
    }
}

if (!function_exists('category_icon')) {
    function category_icon(?string $slug): string
    {
        return [
            'accounting-audit-taxation' => 'fa-calculator',
            'agriculture-environmental-management' => 'fa-seedling',
            'business-management' => 'fa-briefcase',
            'communication-soft-skills' => 'fa-comments',
            'education-training' => 'fa-chalkboard-user',
            'governance-risk-compliance' => 'fa-scale-balanced',
            'human-resource-management' => 'fa-users-gear',
            'information-technology-ai' => 'fa-microchip',
            'leadership-executive-development' => 'fa-user-tie',
            'legal-public-policy' => 'fa-gavel',
            'meal' => 'fa-chart-line',
            'procurement-logistics-supply-chain' => 'fa-truck-fast',
            'project-management' => 'fa-diagram-project',
            'public-health-healthcare-management' => 'fa-heart-pulse',
            'languages' => 'fa-language',
            'specialized-programmes' => 'fa-star',
        ][$slug ?? ''] ?? 'fa-book-open';
    }
}

if (!function_exists('pillar_icon')) {
    function pillar_icon(?string $slug): string
    {
        return [
            'professional-training' => 'fa-user-graduate',
            'capacity-building' => 'fa-people-group',
            'corporate-training' => 'fa-building',
        ][$slug ?? ''] ?? 'fa-star';
    }
}

if (!function_exists('initials')) {
    function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $letters = array_map(fn ($p) => mb_substr($p, 0, 1), array_filter($parts, fn ($p) => $p !== '' && !str_ends_with($p, '.')));
        $letters = array_values($letters);
        $out = ($letters[0] ?? '') . (count($letters) > 1 ? end($letters) : '');
        return mb_strtoupper($out ?: '?');
    }
}

if (!function_exists('role_label')) {
    function role_label(array $roles): string
    {
        $order = ['super_admin', 'admissions', 'finance', 'registrar', 'content_manager', 'lecturer', 'corporate_contact', 'learner'];
        foreach ($order as $role) {
            if (in_array($role, $roles, true)) {
                return $role === 'corporate_contact' ? 'Corporate Contact' : ucwords(str_replace('_', ' ', $role));
            }
        }
        return 'Member';
    }
}
