<?php

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected static string $table = 'settings';

    public static function get(string $key, ?string $default = null): ?string
    {
        $rows = static::query('SELECT `value` FROM settings WHERE `key` = ?', [$key]);
        return $rows[0]['value'] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        static::statement(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value]
        );
    }
}
