<?php

namespace App\Support;

use App\Models\Grade;

/**
 * A student's marks in a class: assignment and lecturer-recorded grades, plus
 * the best submitted attempt at each quiz or exam. Used by the class page,
 * My Results and the lecturer's class list, so they all agree. The average is
 * the mean of the percentages, so every assessment counts equally whatever it
 * is marked out of.
 */
final class Results
{
    /**
     * @return array{rows: array, average: ?float, attendance: ?float, sessions: int}
     *   rows: kind (assignment|quiz|exam|grade), title, score, max, pct, date
     */
    public static function forStudent(int $intakeId, int $userId): array
    {
        $rows = [];
        foreach (self::gradeRows($intakeId, $userId) as $g) {
            $isAssignment = str_starts_with($g['component'], 'assignment:');
            $rows[] = self::row(
                $isAssignment ? 'assignment' : 'grade',
                $isAssignment ? ($g['assignment_title'] ?: 'Assignment') : ucfirst($g['component']),
                (float) $g['score'], (float) $g['max_score'], $g['recorded_at']
            );
        }
        foreach (self::bestAttempts($intakeId, $userId) as $q) {
            $rows[] = self::row($q['is_exam'] ? 'exam' : 'quiz', $q['title'], (float) $q['score'], (float) $q['max_score'], $q['submitted_at']);
        }
        usort($rows, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        [$attended, $sessions] = self::attendanceCounts($intakeId, $userId);
        return [
            'rows' => $rows,
            'average' => self::average($rows),
            'attendance' => $sessions ? round($attended / $sessions * 100, 1) : null,
            'sessions' => $sessions,
        ];
    }

    /** Per student in a class: user_id => [average, assessed, attendance, sessions]. */
    public static function classSummary(int $intakeId): array
    {
        $scores = [];
        $latest = [];
        foreach (Grade::query('SELECT user_id, component, score, max_score FROM grades WHERE intake_id = ? ORDER BY recorded_at, id', [$intakeId]) as $n => $g) {
            // An assignment graded more than once counts once, at its latest mark.
            $key = str_starts_with($g['component'], 'assignment:') ? $g['user_id'] . '|' . $g['component'] : '#' . $n;
            $latest[$key] = $g;
        }
        foreach ($latest as $g) {
            $scores[$g['user_id']][] = ['score' => (float) $g['score'], 'max' => (float) $g['max_score']];
        }
        $best = Grade::query(
            'SELECT qa.user_id, MAX(qa.score) AS score, MAX(qa.max_score) AS max_score
             FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
             WHERE q.intake_id = ? AND qa.submitted_at IS NOT NULL AND qa.max_score > 0
             GROUP BY qa.user_id, q.id',
            [$intakeId]
        );
        foreach ($best as $q) {
            $scores[$q['user_id']][] = ['score' => (float) $q['score'], 'max' => (float) $q['max_score']];
        }

        $summary = [];
        foreach ($scores as $userId => $list) {
            $summary[$userId] = [
                'average' => self::average($list),
                'assessed' => count($list),
                'attendance' => null,
                'sessions' => 0,
            ];
        }
        $attendance = Grade::query(
            'SELECT user_id, SUM(status IN ("present", "late")) AS attended, COUNT(*) AS total
             FROM attendance WHERE intake_id = ? GROUP BY user_id',
            [$intakeId]
        );
        foreach ($attendance as $a) {
            $summary[$a['user_id']] ??= ['average' => null, 'assessed' => 0, 'attendance' => null, 'sessions' => 0];
            $summary[$a['user_id']]['attendance'] = $a['total'] ? round($a['attended'] / $a['total'] * 100, 1) : null;
            $summary[$a['user_id']]['sessions'] = (int) $a['total'];
        }
        return $summary;
    }

    /** Readable status for a percentage: [label, status class]. */
    public static function band(?float $pct): array
    {
        return match (true) {
            $pct === null => ['Not yet assessed', 'draft'],
            $pct >= 70 => ['Strong', 'active'],
            $pct >= 50 => ['On track', 'scheduled'],
            default => ['Needs support', 'failed'],
        };
    }

    private static function gradeRows(int $intakeId, int $userId): array
    {
        $rows = Grade::query(
            'SELECT g.*, a.title AS assignment_title FROM grades g
             LEFT JOIN assignments a ON g.component LIKE "assignment:%" AND a.id = CAST(SUBSTRING(g.component, 12) AS UNSIGNED)
             WHERE g.intake_id = ? AND g.user_id = ? ORDER BY g.recorded_at, g.id',
            [$intakeId, $userId]
        );
        $latest = [];
        foreach ($rows as $n => $g) {
            $latest[str_starts_with($g['component'], 'assignment:') ? $g['component'] : '#' . $n] = $g;
        }
        return array_values($latest);
    }

    private static function bestAttempts(int $intakeId, int $userId): array
    {
        return Grade::query(
            'SELECT q.id, q.title, q.is_exam, MAX(qa.score) AS score, MAX(qa.max_score) AS max_score, MAX(qa.submitted_at) AS submitted_at
             FROM quizzes q JOIN quiz_attempts qa ON qa.quiz_id = q.id
             WHERE q.intake_id = ? AND qa.user_id = ? AND qa.submitted_at IS NOT NULL AND qa.max_score > 0
             GROUP BY q.id, q.title, q.is_exam
             ORDER BY submitted_at',
            [$intakeId, $userId]
        );
    }

    /** @return array{0: int, 1: int} sessions attended (present or late), sessions marked */
    private static function attendanceCounts(int $intakeId, int $userId): array
    {
        $row = Grade::query(
            'SELECT SUM(status IN ("present", "late")) AS attended, COUNT(*) AS total FROM attendance WHERE intake_id = ? AND user_id = ?',
            [$intakeId, $userId]
        )[0] ?? [];
        return [(int) ($row['attended'] ?? 0), (int) ($row['total'] ?? 0)];
    }

    private static function row(string $kind, string $title, float $score, float $max, ?string $date): array
    {
        return [
            'kind' => $kind, 'title' => $title, 'score' => $score, 'max' => $max,
            'pct' => $max > 0 ? round($score / $max * 100, 1) : null, 'date' => $date,
        ];
    }

    /** Mean percentage of rows with score and max. */
    private static function average(array $rows): ?float
    {
        $percentages = [];
        foreach ($rows as $r) {
            if ($r['max'] > 0) {
                $percentages[] = $r['score'] / $r['max'] * 100;
            }
        }
        return $percentages ? round(array_sum($percentages) / count($percentages), 1) : null;
    }
}
