<?php

namespace App\Models;

use App\Core\Model;

class AcademicProgramme extends Model
{
    protected static string $table = 'academic_programmes';

    public static function published(): array
    {
        return static::query(
            'SELECT ap.*, c.title, c.slug, c.summary, c.description
             FROM academic_programmes ap JOIN courses c ON c.id = ap.course_id
             WHERE c.status = "published" ORDER BY c.title'
        );
    }

    public static function withCourse(int $id): ?array
    {
        $rows = static::query(
            'SELECT ap.*, c.title, c.slug, c.summary, c.description
             FROM academic_programmes ap JOIN courses c ON c.id = ap.course_id WHERE ap.id = ?',
            [$id]
        );
        return $rows[0] ?? null;
    }
}
