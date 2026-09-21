<?php

namespace App\Models;

use App\Core\Model;

class Course extends Model
{
    protected static string $table = 'courses';

    public static function findBySlug(string $slug): ?array
    {
        return static::first('slug', $slug);
    }

    public static function publishedForPillar(string $pillarSlug): array
    {
        return static::query(
            'SELECT c.* FROM courses c
             JOIN course_pillars cp ON cp.course_id = c.id
             JOIN pillars p ON p.id = cp.pillar_id
             WHERE p.slug = ? AND c.status = "published" AND c.is_public = 1
             ORDER BY c.title',
            [$pillarSlug]
        );
    }

    public static function catalogue(?string $categorySlug = null, ?string $search = null): array
    {
        $sql = 'SELECT c.*, cat.name AS category_name FROM courses c
                LEFT JOIN course_categories cat ON cat.id = c.category_id
                WHERE c.status = "published" AND c.is_public = 1';
        $params = [];

        if ($categorySlug) {
            $sql .= ' AND cat.slug = ?';
            $params[] = $categorySlug;
        }
        if ($search) {
            $sql .= ' AND (c.title LIKE ? OR c.summary LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= ' ORDER BY c.title';
        return static::query($sql, $params);
    }

    public static function pillars(int $courseId): array
    {
        return static::query(
            'SELECT p.* FROM pillars p JOIN course_pillars cp ON cp.pillar_id = p.id WHERE cp.course_id = ?',
            [$courseId]
        );
    }

    public static function setPillars(int $courseId, array $pillarIds): void
    {
        static::statement('DELETE FROM course_pillars WHERE course_id = ?', [$courseId]);
        foreach ($pillarIds as $pillarId) {
            static::statement('INSERT INTO course_pillars (course_id, pillar_id) VALUES (?, ?)', [$courseId, (int) $pillarId]);
        }
    }

    public static function openIntakes(int $courseId): array
    {
        return static::query(
            'SELECT * FROM intakes WHERE course_id = ? AND status IN ("scheduled","open") ORDER BY start_date',
            [$courseId]
        );
    }
}
