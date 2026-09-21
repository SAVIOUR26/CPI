<?php

namespace App\Models;

use App\Core\Model;

class FeeLedger extends Model
{
    protected static string $table = 'fee_ledger';

    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT f.*, i.code AS intake_code, c.title AS course_title
             FROM fee_ledger f JOIN intakes i ON i.id = f.intake_id JOIN courses c ON c.id = i.course_id
             WHERE f.user_id = ? ORDER BY f.created_at',
            [$userId]
        );
    }

    public static function balanceFor(int $userId, int $intakeId): float
    {
        $rows = static::query(
            'SELECT COALESCE(SUM(amount_due - amount_paid), 0) balance FROM fee_ledger
             WHERE user_id = ? AND intake_id = ? AND hold_release = 0',
            [$userId, $intakeId]
        );
        return (float) $rows[0]['balance'];
    }

    public static function isCleared(int $userId, int $intakeId): bool
    {
        return self::balanceFor($userId, $intakeId) <= 0;
    }
}
