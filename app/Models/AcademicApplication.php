<?php

namespace App\Models;

use App\Core\Model;

class AcademicApplication extends Model
{
    protected static string $table = 'academic_applications';

    /** A student's own applications (linked on admission, or when they applied while signed in). */
    public static function forUser(int $userId): array
    {
        return static::query(
            'SELECT aa.*, c.title AS programme_title, ap.award_level, ap.awarding_body, ap.duration_note,
                    i.code AS intake_code, i.start_date AS intake_start
             FROM academic_applications aa
             JOIN academic_programmes ap ON ap.id = aa.programme_id
             JOIN courses c ON c.id = ap.course_id
             LEFT JOIN intakes i ON i.id = aa.intake_id
             WHERE aa.user_id = ?
             ORDER BY aa.created_at DESC',
            [$userId]
        );
    }

    public static function queue(): array
    {
        return static::query(
            'SELECT aa.*, c.title AS programme_title FROM academic_applications aa
             JOIN academic_programmes ap ON ap.id = aa.programme_id
             JOIN courses c ON c.id = ap.course_id
             WHERE aa.status IN ("submitted","under_review")
             ORDER BY aa.created_at'
        );
    }
}
