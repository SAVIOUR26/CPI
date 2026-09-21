<?php

namespace App\Models;

use App\Core\Model;

class AcademicApplication extends Model
{
    protected static string $table = 'academic_applications';

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
