<?php

namespace App\Models;

use App\Core\Model;

class Intake extends Model
{
    protected static string $table = 'intakes';

    public static function withCourse(int $id): ?array
    {
        $rows = static::query(
            'SELECT i.*, c.title AS course_title, c.slug AS course_slug, c.price_amount, c.price_currency, c.programme_type
             FROM intakes i JOIN courses c ON c.id = i.course_id WHERE i.id = ?',
            [$id]
        );
        return $rows[0] ?? null;
    }

    public static function forLecturer(int $userId): array
    {
        return static::query(
            'SELECT i.*, c.title AS course_title FROM intakes i
             JOIN courses c ON c.id = i.course_id
             LEFT JOIN intake_lecturers il ON il.intake_id = i.id
             WHERE i.primary_lecturer_id = ? OR il.user_id = ?
             GROUP BY i.id
             ORDER BY i.start_date DESC',
            [$userId, $userId]
        );
    }

    public static function roster(int $intakeId): array
    {
        return static::query(
            'SELECT u.id, u.full_name, u.email, e.status, e.progress_pct, e.enrolled_at
             FROM enrollments e JOIN users u ON u.id = e.user_id
             WHERE e.intake_id = ? ORDER BY u.full_name',
            [$intakeId]
        );
    }

    public static function incrementSeats(int $intakeId, int $by = 1): void
    {
        static::statement('UPDATE intakes SET seats_taken = seats_taken + ? WHERE id = ?', [$by, $intakeId]);
    }
}
