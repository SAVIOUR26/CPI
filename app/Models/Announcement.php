<?php

namespace App\Models;

use App\Core\Model;

/**
 * Class announcements (intake_id set, posted by the class lecturer or an admin)
 * and institute-wide ones (intake_id NULL, aimed at students, lecturers or everyone).
 */
class Announcement extends Model
{
    protected static string $table = 'announcements';

    public const AUDIENCES = [
        'everyone' => 'All students and lecturers',
        'students' => 'All students',
        'lecturers' => 'All lecturers',
    ];

    private const SELECT = 'SELECT a.*, u.full_name AS author_name, i.code AS intake_code, c.title AS course_title
                            FROM announcements a
                            LEFT JOIN users u ON u.id = a.created_by
                            LEFT JOIN intakes i ON i.id = a.intake_id
                            LEFT JOIN courses c ON c.id = i.course_id';

    /** Institute-wide notices for students plus those of the classes the student is in. */
    public static function forStudent(int $userId, int $limit = 0): array
    {
        return static::query(
            self::SELECT . ' WHERE (a.intake_id IS NULL AND a.audience IN ("everyone", "students"))
                OR a.intake_id IN (SELECT intake_id FROM enrollments WHERE user_id = ? AND status IN ("active", "completed"))
             ORDER BY a.pinned DESC, a.created_at DESC, a.id DESC' . ($limit ? ' LIMIT ' . (int) $limit : ''),
            [$userId]
        );
    }

    /** Institute-wide notices for lecturers. */
    public static function forLecturers(int $limit = 0): array
    {
        return static::query(
            self::SELECT . ' WHERE a.intake_id IS NULL AND a.audience IN ("everyone", "lecturers")
             ORDER BY a.pinned DESC, a.created_at DESC, a.id DESC' . ($limit ? ' LIMIT ' . (int) $limit : '')
        );
    }

    public static function forIntake(int $intakeId): array
    {
        return static::query(
            self::SELECT . ' WHERE a.intake_id = ? ORDER BY a.pinned DESC, a.created_at DESC, a.id DESC',
            [$intakeId]
        );
    }

    public static function recent(int $limit = 100): array
    {
        return static::query(self::SELECT . ' ORDER BY a.created_at DESC, a.id DESC LIMIT ' . (int) $limit);
    }

    /** Who an announcement is for, in words. */
    public static function audienceLabel(array $a): string
    {
        if ($a['intake_id']) {
            return trim(($a['course_title'] ?? 'Class') . ' (' . ($a['intake_code'] ?? '') . ')');
        }
        return self::AUDIENCES[$a['audience']] ?? 'Everyone';
    }
}
