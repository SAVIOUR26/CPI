<?php

namespace App\Models;

use App\Core\Model;

class Assignment extends Model
{
    protected static string $table = 'assignments';

    public static function forIntake(int $intakeId): array
    {
        return static::query('SELECT * FROM assignments WHERE intake_id = ? ORDER BY due_at', [$intakeId]);
    }

    public static function submissionFor(int $assignmentId, int $userId): ?array
    {
        $rows = static::query(
            'SELECT * FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?',
            [$assignmentId, $userId]
        );
        return $rows[0] ?? null;
    }

    public static function submissions(int $assignmentId): array
    {
        return static::query(
            'SELECT s.*, u.full_name, u.email FROM assignment_submissions s
             JOIN users u ON u.id = s.user_id WHERE s.assignment_id = ? ORDER BY s.submitted_at',
            [$assignmentId]
        );
    }
}
