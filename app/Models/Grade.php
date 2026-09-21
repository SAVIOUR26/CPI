<?php

namespace App\Models;

use App\Core\Model;

class Grade extends Model
{
    protected static string $table = 'grades';

    public static function forUserInIntake(int $intakeId, int $userId): array
    {
        return static::query('SELECT * FROM grades WHERE intake_id = ? AND user_id = ? ORDER BY recorded_at', [$intakeId, $userId]);
    }

    public static function averageFor(int $intakeId, int $userId): ?float
    {
        $rows = static::forUserInIntake($intakeId, $userId);
        if (!$rows) {
            return null;
        }
        $totalScore = 0;
        $totalMax = 0;
        foreach ($rows as $r) {
            $totalScore += (float) $r['score'];
            $totalMax += (float) $r['max_score'];
        }
        return $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 1) : null;
    }
}
