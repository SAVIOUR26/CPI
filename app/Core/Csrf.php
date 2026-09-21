<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf', $token);
        }
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function verify(?string $token): bool
    {
        $sessionToken = Session::get('_csrf');
        return $sessionToken && $token && hash_equals($sessionToken, $token);
    }
}
