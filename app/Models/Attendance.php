<?php

namespace App\Models;

use App\Core\Model;

class Attendance extends Model
{
    protected static string $table = 'attendance';

    public static function forSession(int $intakeId, string $date): array
    {
        return static::query(
            'SELECT a.*, u.full_name FROM attendance a JOIN users u ON u.id = a.user_id
             WHERE a.intake_id = ? AND a.session_date = ? ORDER BY u.full_name',
            [$intakeId, $date]
        );
    }

    public static function mark(int $intakeId, int $userId, string $date, string $status, ?int $markedBy): void
    {
        static::statement(
            'INSERT INTO attendance (intake_id, user_id, session_date, status, marked_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)',
            [$intakeId, $userId, $date, $status, $markedBy]
        );
    }
}
