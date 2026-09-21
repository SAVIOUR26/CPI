<?php

namespace App\Models;

use App\Core\Model;

class Enrollment extends Model
{
    protected static string $table = 'enrollments';

    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT e.*, i.code AS intake_code, i.start_date, i.end_date, c.title AS course_title, c.slug AS course_slug
             FROM enrollments e
             JOIN intakes i ON i.id = e.intake_id
             JOIN courses c ON c.id = i.course_id
             WHERE e.user_id = ?
             ORDER BY e.enrolled_at DESC',
            [$userId]
        );
    }

    public static function existing(int $userId, int $intakeId): ?array
    {
        $rows = static::query('SELECT * FROM enrollments WHERE user_id = ? AND intake_id = ?', [$userId, $intakeId]);
        return $rows[0] ?? null;
    }

    public static function activate(int $id): void
    {
        static::update($id, ['status' => 'active']);
    }

    public static function isActiveFor(int $userId, int $intakeId): bool
    {
        $row = static::existing($userId, $intakeId);
        return $row && in_array($row['status'], ['active', 'completed'], true);
    }
}
