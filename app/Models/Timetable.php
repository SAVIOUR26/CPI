<?php

namespace App\Models;

use App\Core\Model;

class Timetable extends Model
{
    protected static string $table = 'timetable_entries';

    private const DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

    public static function forIntake(int $intakeId): array
    {
        return static::query(
            'SELECT t.*, u.full_name AS lecturer_name FROM timetable_entries t
             LEFT JOIN users u ON u.id = t.lecturer_id
             WHERE t.intake_id = ? ORDER BY t.day_of_week, t.start_time',
            [$intakeId]
        );
    }

    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT t.*, c.title AS course_title, i.code AS intake_code FROM timetable_entries t
             JOIN intakes i ON i.id = t.intake_id
             JOIN courses c ON c.id = i.course_id
             JOIN enrollments e ON e.intake_id = i.id
             WHERE e.user_id = ? AND e.status IN ("active","completed")
             ORDER BY t.day_of_week, t.start_time',
            [$userId]
        );
    }

    /** 1 => 'Monday' … 7 => 'Sunday' */
    public static function days(): array
    {
        return self::DAYS;
    }

    public static function dayName(int $day): string
    {
        return self::DAYS[$day] ?? 'Day ' . $day;
    }
}
