<?php

namespace App\Models;

use App\Core\Model;

class Material extends Model
{
    protected static string $table = 'materials';

    public static function forIntake(int $intakeId): array
    {
        return static::query('SELECT * FROM materials WHERE intake_id = ? ORDER BY created_at DESC', [$intakeId]);
    }
}
