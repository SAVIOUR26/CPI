<?php

namespace App\Models;

use App\Core\Model;

class Discussion extends Model
{
    protected static string $table = 'discussions';

    public static function forIntake(int $intakeId): array
    {
        return static::query(
            'SELECT d.*, u.full_name FROM discussions d JOIN users u ON u.id = d.user_id
             WHERE d.intake_id = ? ORDER BY d.created_at',
            [$intakeId]
        );
    }
}
