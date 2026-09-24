<?php

namespace App\Models;

use App\Core\Model;

/** Key dates on the academic calendar: terms, exams, holidays, deadlines and events. */
class CalendarEvent extends Model
{
    protected static string $table = 'calendar_events';

    /** category => [label, icon, tone] */
    public const CATEGORIES = [
        'term' => ['Term dates', 'fa-flag', 'tone-crimson'],
        'exam' => ['Examinations', 'fa-file-pen', 'tone-purple'],
        'holiday' => ['Holiday / break', 'fa-umbrella-beach', 'tone-green'],
        'deadline' => ['Deadline', 'fa-hourglass-end', 'tone-orange'],
        'event' => ['Event', 'fa-calendar-day', 'tone-blue'],
    ];

    private const SELECT = 'SELECT ce.*, i.code AS intake_code, c.title AS course_title
                            FROM calendar_events ce
                            LEFT JOIN intakes i ON i.id = ce.intake_id
                            LEFT JOIN courses c ON c.id = i.course_id';

    /** Dates for everyone plus the student's own classes, from $fromDate on. */
    public static function forStudent(int $userId, string $fromDate, int $limit = 0): array
    {
        return static::query(
            self::SELECT . ' WHERE COALESCE(ce.ends_on, ce.starts_on) >= ?
               AND (ce.intake_id IS NULL OR ce.intake_id IN (SELECT intake_id FROM enrollments WHERE user_id = ? AND status IN ("active", "completed", "pending_payment")))
             ORDER BY ce.starts_on, ce.id' . ($limit ? ' LIMIT ' . (int) $limit : ''),
            [$fromDate, $userId]
        );
    }

    /** Dates for everyone plus the lecturer's own classes. */
    public static function forLecturer(int $userId, string $fromDate, int $limit = 0): array
    {
        return static::query(
            self::SELECT . ' WHERE COALESCE(ce.ends_on, ce.starts_on) >= ?
               AND (ce.intake_id IS NULL OR ce.intake_id IN (
                    SELECT id FROM intakes WHERE primary_lecturer_id = ?
                    UNION SELECT intake_id FROM intake_lecturers WHERE user_id = ?))
             ORDER BY ce.starts_on, ce.id' . ($limit ? ' LIMIT ' . (int) $limit : ''),
            [$fromDate, $userId, $userId]
        );
    }

    public static function listAll(): array
    {
        return static::query(self::SELECT . ' ORDER BY ce.starts_on DESC, ce.id DESC');
    }

    public static function category(string $key): array
    {
        return self::CATEGORIES[$key] ?? self::CATEGORIES['event'];
    }

    /** "3 Feb 2027" or "3 – 14 Feb 2027" / "28 Jan – 3 Feb 2027". */
    public static function dateRange(array $e): string
    {
        $start = strtotime($e['starts_on']);
        $end = $e['ends_on'] ? strtotime($e['ends_on']) : null;
        if (!$end || $end === $start) {
            return date('j M Y', $start);
        }
        if (date('Y-m', $start) === date('Y-m', $end)) {
            return date('j', $start) . ' – ' . date('j M Y', $end);
        }
        if (date('Y', $start) === date('Y', $end)) {
            return date('j M', $start) . ' – ' . date('j M Y', $end);
        }
        return date('j M Y', $start) . ' – ' . date('j M Y', $end);
    }
}
