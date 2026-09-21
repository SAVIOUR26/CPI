<?php

namespace App\Models;

use App\Core\Model;

class Quiz extends Model
{
    protected static string $table = 'quizzes';

    public static function forIntake(int $intakeId): array
    {
        return static::query('SELECT * FROM quizzes WHERE intake_id = ? ORDER BY created_at DESC', [$intakeId]);
    }

    public static function questions(int $quizId): array
    {
        return static::query('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order', [$quizId]);
    }

    public static function options(int $questionId): array
    {
        return static::query('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY sort_order', [$questionId]);
    }

    public static function attemptsUsed(int $quizId, int $userId): int
    {
        $rows = static::query(
            'SELECT COUNT(*) c FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? AND submitted_at IS NOT NULL',
            [$quizId, $userId]
        );
        return (int) $rows[0]['c'];
    }

    public static function attempts(int $quizId): array
    {
        return static::query(
            'SELECT qa.*, u.full_name, u.email FROM quiz_attempts qa
             JOIN users u ON u.id = qa.user_id WHERE qa.quiz_id = ? ORDER BY qa.submitted_at DESC',
            [$quizId]
        );
    }
}
