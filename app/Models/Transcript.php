<?php

namespace App\Models;

use App\Core\Model;

class Transcript extends Model
{
    protected static string $table = 'transcripts';

    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT t.*, c.title AS course_title FROM transcripts t
             JOIN intakes i ON i.id = t.intake_id JOIN courses c ON c.id = i.course_id
             WHERE t.user_id = ? ORDER BY t.generated_at DESC',
            [$userId]
        );
    }
}
