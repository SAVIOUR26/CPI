<?php

namespace App\Controllers\Learner;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Discussion;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Intake;
use App\Models\Material;
use App\Models\Quiz;

class CourseRoomController extends Controller
{
    private function authorizeIntake(int $intakeId): array
    {
        $user = $this->requireAuth();
        if (!Enrollment::isActiveFor((int) $user['id'], $intakeId)) {
            $this->abort(403, "You're not enrolled in this course.");
        }
        return $user;
    }

    public function show(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);

        $intake = Intake::withCourse($intakeId);
        $materials = Material::forIntake($intakeId);
        $assignments = Assignment::forIntake($intakeId);
        $quizzes = Quiz::forIntake($intakeId);
        $discussions = Discussion::forIntake($intakeId);
        $grades = Grade::forUserInIntake($intakeId, (int) $user['id']);
        $average = Grade::averageFor($intakeId, (int) $user['id']);
        $attendanceRate = Attendance::rateFor($intakeId, (int) $user['id']);

        $submissions = [];
        foreach ($assignments as $a) {
            $submissions[$a['id']] = Assignment::submissionFor((int) $a['id'], (int) $user['id']);
        }

        $this->view('learner.course-room', [
            'pageTitle' => ($intake['course_title'] ?? 'Course') . ' — CPI',
            'intake' => $intake,
            'materials' => $materials,
            'assignments' => $assignments,
            'submissions' => $submissions,
            'quizzes' => $quizzes,
            'discussions' => $discussions,
            'grades' => $grades,
            'average' => $average,
            'attendanceRate' => $attendanceRate,
        ], 'layouts.dashboard');
    }

    public function downloadMaterial(Request $request): void
    {
        $materialId = (int) $request->param('material');
        $material = Material::find($materialId);
        if (!$material || !$material['file_path']) {
            $this->abort(404, 'File not found.');
            return;
        }
        $this->authorizeIntake((int) $material['intake_id']);

        if (!Upload::exists($material['file_path'])) {
            $this->abort(404, 'File not found.');
            return;
        }

        header('Content-Type: ' . Upload::mimeFor($material['file_path']));
        header('Content-Disposition: inline; filename="' . basename($material['title']) . '"');
        readfile(Upload::absolutePath($material['file_path']));
        exit;
    }

    public function submitAssignment(Request $request): void
    {
        $assignmentId = (int) $request->param('assignment');
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            $this->abort(404, 'Assignment not found.');
            return;
        }
        $user = $this->authorizeIntake((int) $assignment['intake_id']);
        $this->verifyCsrf($request);

        $file = $request->file('file');
        $notes = $request->input('notes');
        $path = null;

        if ($file) {
            try {
                $path = Upload::store($file, 'submissions');
            } catch (\RuntimeException $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/learner/courses/' . $assignment['intake_id']);
                return;
            }
        }

        $existing = Assignment::submissionFor($assignmentId, (int) $user['id']);
        if ($existing) {
            $update = ['notes' => $notes, 'submitted_at' => date('Y-m-d H:i:s')];
            if ($path) {
                $update['file_path'] = $path;
            }
            Assignment::statement(
                'UPDATE assignment_submissions SET notes = ?, submitted_at = ?' . ($path ? ', file_path = ?' : '') . ' WHERE id = ?',
                $path ? [$notes, date('Y-m-d H:i:s'), $path, $existing['id']] : [$notes, date('Y-m-d H:i:s'), $existing['id']]
            );
        } else {
            Assignment::statement(
                'INSERT INTO assignment_submissions (assignment_id, user_id, file_path, notes, submitted_at) VALUES (?, ?, ?, ?, NOW())',
                [$assignmentId, $user['id'], $path, $notes]
            );
        }

        $this->flash('success', 'Your submission has been received.');
        $this->redirect('/learner/courses/' . $assignment['intake_id']);
    }

    public function postDiscussion(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $body = trim((string) $request->input('body'));
        if ($body !== '') {
            Discussion::insert(['intake_id' => $intakeId, 'user_id' => $user['id'], 'body' => $body]);
        }

        $this->redirect('/learner/courses/' . $intakeId . '#discussion');
    }

    // ── Quizzes ──────────────────────────────────────────────────────────

    public function showQuiz(Request $request): void
    {
        $quizId = (int) $request->param('quiz');
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $this->abort(404, 'Quiz not found.');
            return;
        }
        $user = $this->authorizeIntake((int) $quiz['intake_id']);

        $used = Quiz::attemptsUsed($quizId, (int) $user['id']);
        $ongoing = Quiz::query(
            'SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ? AND submitted_at IS NULL ORDER BY id DESC LIMIT 1',
            [$quizId, $user['id']]
        );

        $this->view('learner.quiz-start', [
            'pageTitle' => $quiz['title'] . ' — CPI',
            'quiz' => $quiz,
            'attemptsUsed' => $used,
            'ongoingAttempt' => $ongoing[0] ?? null,
        ], 'layouts.dashboard');
    }

    public function startQuiz(Request $request): void
    {
        $quizId = (int) $request->param('quiz');
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $this->abort(404, 'Quiz not found.');
            return;
        }
        $user = $this->authorizeIntake((int) $quiz['intake_id']);
        $this->verifyCsrf($request);

        $used = Quiz::attemptsUsed($quizId, (int) $user['id']);
        if ($used >= (int) $quiz['max_attempts']) {
            $this->flash('error', 'You have used all your attempts for this quiz.');
            $this->redirect('/learner/courses/' . $quiz['intake_id']);
            return;
        }

        Quiz::statement(
            'INSERT INTO quiz_attempts (quiz_id, user_id, attempt_no) VALUES (?, ?, ?)',
            [$quizId, $user['id'], $used + 1]
        );
        $attemptId = (int) \App\Core\Database::connection()->lastInsertId();

        $this->redirect("/learner/quizzes/$quizId/attempt/$attemptId");
    }

    public function showAttempt(Request $request): void
    {
        $attemptId = (int) $request->param('attempt');
        $attempt = Quiz::query('SELECT * FROM quiz_attempts WHERE id = ?', [$attemptId])[0] ?? null;
        if (!$attempt) {
            $this->abort(404, 'Attempt not found.');
            return;
        }
        $user = $this->requireAuth();
        if ((int) $attempt['user_id'] !== (int) $user['id']) {
            $this->abort(403, 'Not your attempt.');
            return;
        }
        $quiz = Quiz::find((int) $attempt['quiz_id']);

        if ($attempt['submitted_at']) {
            $this->view('learner.quiz-result', [
                'pageTitle' => 'Quiz Result — CPI',
                'quiz' => $quiz,
                'attempt' => $attempt,
            ], 'layouts.dashboard');
            return;
        }

        $questions = Quiz::questions((int) $quiz['id']);
        foreach ($questions as &$q) {
            $q['options'] = Quiz::options((int) $q['id']);
        }
        unset($q);

        $this->view('learner.quiz-attempt', [
            'pageTitle' => $quiz['title'] . ' — CPI',
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
        ], 'layouts.dashboard');
    }

    public function submitAttempt(Request $request): void
    {
        $attemptId = (int) $request->param('attempt');
        $attempt = Quiz::query('SELECT * FROM quiz_attempts WHERE id = ?', [$attemptId])[0] ?? null;
        if (!$attempt) {
            $this->abort(404, 'Attempt not found.');
            return;
        }
        $user = $this->requireAuth();
        if ((int) $attempt['user_id'] !== (int) $user['id']) {
            $this->abort(403, 'Not your attempt.');
            return;
        }
        if ($attempt['submitted_at']) {
            $this->redirect('/learner/quizzes/' . $attempt['quiz_id'] . '/attempt/' . $attemptId);
            return;
        }
        $this->verifyCsrf($request);

        $quiz = Quiz::find((int) $attempt['quiz_id']);
        $questions = Quiz::questions((int) $quiz['id']);

        $totalScore = 0;
        $maxScore = 0;
        $answers = $request->input('answers', []);

        foreach ($questions as $q) {
            $maxScore += (float) $q['points'];
            $options = Quiz::options((int) $q['id']);
            $given = $answers[$q['id']] ?? null;

            if ($q['type'] === 'single') {
                $selectedId = is_array($given) ? ($given[0] ?? null) : $given;
                $correct = null;
                foreach ($options as $o) {
                    if ($o['is_correct']) {
                        $correct = $o['id'];
                    }
                }
                $isCorrect = $selectedId && (int) $selectedId === (int) $correct;
                $points = $isCorrect ? (float) $q['points'] : 0;
                $totalScore += $points;
                Quiz::statement(
                    'INSERT INTO quiz_answers (attempt_id, question_id, option_id, is_correct, points_awarded) VALUES (?, ?, ?, ?, ?)',
                    [$attemptId, $q['id'], $selectedId ?: null, $isCorrect ? 1 : 0, $points]
                );
            } elseif ($q['type'] === 'multiple') {
                $selectedIds = array_map('intval', is_array($given) ? $given : []);
                $correctIds = array_map(fn($o) => (int) $o['id'], array_filter($options, fn($o) => $o['is_correct']));
                sort($selectedIds);
                sort($correctIds);
                $isCorrect = $selectedIds === $correctIds && $correctIds !== [];
                $points = $isCorrect ? (float) $q['points'] : 0;
                $totalScore += $points;
                foreach ($selectedIds ?: [null] as $optId) {
                    Quiz::statement(
                        'INSERT INTO quiz_answers (attempt_id, question_id, option_id, is_correct, points_awarded) VALUES (?, ?, ?, ?, ?)',
                        [$attemptId, $q['id'], $optId, $isCorrect ? 1 : 0, $optId ? $points / max(count($selectedIds), 1) : 0]
                    );
                }
            } else { // short_text — left for the lecturer to grade manually
                $text = is_string($given) ? $given : '';
                Quiz::statement(
                    'INSERT INTO quiz_answers (attempt_id, question_id, answer_text, points_awarded) VALUES (?, ?, ?, NULL)',
                    [$attemptId, $q['id'], $text]
                );
            }
        }

        Quiz::statement(
            'UPDATE quiz_attempts SET submitted_at = NOW(), score = ?, max_score = ? WHERE id = ?',
            [$totalScore, $maxScore, $attemptId]
        );

        $this->flash('success', 'Your answers have been submitted.');
        $this->redirect('/learner/quizzes/' . $quiz['id'] . '/attempt/' . $attemptId);
    }
}
