<?php

namespace App\Models;

use App\Core\Model;

class Certificate extends Model
{
    protected static string $table = 'certificates';

    public static function findByCode(string $code): ?array
    {
        $rows = static::query(
            'SELECT ct.*, u.full_name, i.code AS intake_code, c.title AS course_title
             FROM certificates ct
             JOIN users u ON u.id = ct.user_id
             JOIN intakes i ON i.id = ct.intake_id
             JOIN courses c ON c.id = i.course_id
             WHERE ct.code = ?',
            [$code]
        );
        return $rows[0] ?? null;
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        do {
            $candidate = sprintf('CPI-%s-%06d', $year, random_int(1, 999999));
        } while (static::first('code', $candidate));
        return $candidate;
    }

    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT ct.*, c.title AS course_title FROM certificates ct
             JOIN intakes i ON i.id = ct.intake_id
             JOIN courses c ON c.id = i.course_id
             WHERE ct.user_id = ? AND ct.revoked = 0
             ORDER BY ct.issued_at DESC',
            [$userId]
        );
    }
}
