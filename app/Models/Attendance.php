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

    public static function rateFor(int $intakeId, int $userId): float
    {
        $rows = static::query(
            'SELECT
                SUM(status IN ("present","late")) as attended,
                COUNT(*) as total
             FROM attendance WHERE intake_id = ? AND user_id = ?',
            [$intakeId, $userId]
        );
        $total = (int) ($rows[0]['total'] ?? 0);
        if ($total === 0) {
            return 0;
        }
        return round(((int) $rows[0]['attended'] / $total) * 100, 1);
    }
}
