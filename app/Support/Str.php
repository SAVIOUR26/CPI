<?php

namespace App\Support;

class Str
{
    public static function slug(string $text): string
    {
        $text = trim($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') ?: 'item';
    }

    public static function money(float|string $amount, string $currency = 'UGX'): string
    {
        return $currency . ' ' . number_format((float) $amount, 0);
    }

    public static function limit(?string $text, int $length = 140): string
    {
        $text = $text ?? '';
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '…';
    }

    public static function random(int $length = 16): string
    {
        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }
}
