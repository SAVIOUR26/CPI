<?php

namespace App\Controllers\Lecturer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Discussion;
use App\Models\Grade;
use App\Models\Intake;
use App\Models\Material;
use App\Models\Quiz;

class IntakeController extends Controller
{
    private function authorizeIntake(int $intakeId): array
    {
        $this->requireRole('lecturer', 'super_admin');
        $user = Auth::user();

        if (!Auth::hasRole('super_admin')) {
            $assigned = Intake::query(
                'SELECT 1 FROM intakes i LEFT JOIN intake_lecturers il ON il.intake_id = i.id
                 WHERE i.id = ? AND (i.primary_lecturer_id = ? OR il.user_id = ?) LIMIT 1',
                [$intakeId, $user['id'], $user['id']]
            );
            if (!$assigned) {
                $this->abort(403, 'You are not assigned to this class.');
            }
        }
        return $user;
    }

    public function show(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $this->authorizeIntake($intakeId);

        $intake = Intake::withCourse($intakeId);
        $roster = Intake::roster($intakeId);
        $materials = Material::forIntake($intakeId);
        $assignments = Assignment::forIntake($intakeId);
        $quizzes = Quiz::forIntake($intakeId);
        $discussions = Discussion::forIntake($intakeId);
        $today = date('Y-m-d');
        $attendanceToday = Attendance::forSession($intakeId, $today);
        $attendanceMap = [];
        foreach ($attendanceToday as $a) {
            $attendanceMap[$a['user_id']] = $a['status'];
        }

        $this->view('lecturer.intake-room', [
            'pageTitle' => ($intake['course_title'] ?? 'Class') . ' — CPI',
            'intake' => $intake,
            'roster' => $roster,
            'materials' => $materials,
            'assignments' => $assignments,
            'quizzes' => $quizzes,
            'discussions' => $discussions,
            'today' => $today,
            'attendanceMap' => $attendanceMap,
        ], 'layouts.dashboard');
    }

    public function addMaterial(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $type = $request->input('type', 'note');
        $title = trim((string) $request->input('title'));
        $videoUrl = $request->input('video_url');
        $body = $request->input('body');
        $path = null;

        $file = $request->file('file');
        if ($file) {
            try {
                $path = Upload::store($file, 'materials');
            } catch (\RuntimeException $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/lecturer/classes/' . $intakeId);
                return;
            }
        }

        Material::insert([
            'intake_id' => $intakeId,
            'type' => $type,
            'title' => $title ?: 'Untitled material',
            'file_path' => $path,
            'video_url' => $type === 'video' ? $videoUrl : null,
            'body' => $body,
            'uploaded_by' => $user['id'],
        ]);

        $this->flash('success', 'Material added.');
        $this->redirect('/lecturer/classes/' . $intakeId);
    }

    public function createAssignment(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'max_score' => 'numeric',
        ]);

        Assignment::insert([
            'intake_id' => $intakeId,
            'title' => $data['title'],
            'instructions' => $request->input('instructions'),
            'max_score' => $data['max_score'] ?: 100,
            'due_at' => $request->input('due_at') ?: null,
            'created_by' => $user['id'],
        ]);

        $this->flash('success', 'Assignment created.');
        $this->redirect('/lecturer/classes/' . $intakeId);
    }

    public function assignmentSubmissions(Request $request): void
    {
        $assignmentId = (int) $request->param('assignment');
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            $this->abort(404, 'Assignment not found.');
            return;
        }
        $this->authorizeIntake((int) $assignment['intake_id']);
        $submissions = Assignment::submissions($assignmentId);

        $this->view('lecturer.assignment-submissions', [
            'pageTitle' => 'Submissions — ' . $assignment['title'],
            'assignment' => $assignment,
            'submissions' => $submissions,
        ], 'layouts.dashboard');
    }

    public function submissionFile(Request $request): void
    {
        $submissionId = (int) $request->param('submission');
        $rows = Assignment::query('SELECT * FROM assignment_submissions WHERE id = ?', [$submissionId]);
        if (!$rows || !$rows[0]['file_path']) {
            $this->abort(404, 'File not found.');
            return;
        }
        $submission = $rows[0];
        $assignment = Assignment::findOrFail((int) $submission['assignment_id']);
        $this->authorizeIntake((int) $assignment['intake_id']);

        if (!Upload::exists($submission['file_path'])) {
            $this->abort(404, 'File not found.');
            return;
        }
        header('Content-Type: ' . Upload::mimeFor($submission['file_path']));
        header('Content-Disposition: inline');
        readfile(Upload::absolutePath($submission['file_path']));
        exit;
    }

    public function gradeSubmission(Request $request): void
    {
        $submissionId = (int) $request->param('submission');
        $rows = Assignment::query('SELECT * FROM assignment_submissions WHERE id = ?', [$submissionId]);
        if (!$rows) {
            $this->abort(404, 'Submission not found.');
            return;
        }
        $submission = $rows[0];
        $assignment = Assignment::findOrFail((int) $submission['assignment_id']);
        $user = $this->authorizeIntake((int) $assignment['intake_id']);
        $this->verifyCsrf($request);

        $score = (float) $request->input('score');
        $feedback = $request->input('feedback');

        Assignment::statement(
            'UPDATE assignment_submissions SET score = ?, feedback = ?, graded_by = ?, graded_at = NOW() WHERE id = ?',
            [$score, $feedback, $user['id'], $submissionId]
        );

        Grade::insert([
            'intake_id' => $assignment['intake_id'],
            'user_id' => $submission['user_id'],
            'component' => 'assignment:' . $assignment['id'],
            'score' => $score,
            'max_score' => $assignment['max_score'],
            'recorded_by' => $user['id'],
        ]);

        $this->flash('success', 'Grade recorded.');
        $this->redirect('/lecturer/assignments/' . $assignment['id'] . '/submissions');
    }

    // ── Quizzes ──────────────────────────────────────────────────────────

    public function createQuiz(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $data = $this->validate($request, ['title' => 'required|max:190']);

        $quizId = Quiz::insert([
            'intake_id' => $intakeId,
            'title' => $data['title'],
            'time_limit_minutes' => $request->input('time_limit_minutes') ?: null,
            'max_attempts' => $request->input('max_attempts') ?: 1,
            'is_exam' => $request->input('is_exam') ? 1 : 0,
            'created_by' => $user['id'],
        ]);

        $this->flash('success', 'Quiz created. Now add questions.');
        $this->redirect('/lecturer/quizzes/' . $quizId);
    }

    public function showQuiz(Request $request): void
    {
        $quizId = (int) $request->param('quiz');
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $this->abort(404, 'Quiz not found.');
            return;
        }
        $this->authorizeIntake((int) $quiz['intake_id']);

        $questions = Quiz::questions($quizId);
        foreach ($questions as &$q) {
            $q['options'] = Quiz::options((int) $q['id']);
        }
        unset($q);

        $attempts = Quiz::attempts($quizId);

        $this->view('lecturer.quiz-builder', [
            'pageTitle' => $quiz['title'] . ' — CPI',
            'quiz' => $quiz,
            'questions' => $questions,
            'attempts' => $attempts,
        ], 'layouts.dashboard');
    }

    public function addQuestion(Request $request): void
    {
        $quizId = (int) $request->param('quiz');
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $this->abort(404, 'Quiz not found.');
            return;
        }
        $this->authorizeIntake((int) $quiz['intake_id']);
        $this->verifyCsrf($request);

        $question = trim((string) $request->input('question'));
        $type = $request->input('type', 'single');
        $points = (float) $request->input('points', 1);

        if ($question === '') {
            $this->flash('error', 'Question text is required.');
            $this->redirect('/lecturer/quizzes/' . $quizId);
            return;
        }

        $existingCount = Quiz::query('SELECT COUNT(*) c FROM quiz_questions WHERE quiz_id = ?', [$quizId])[0]['c'];
        Quiz::statement(
            'INSERT INTO quiz_questions (quiz_id, question, type, points, sort_order) VALUES (?, ?, ?, ?, ?)',
            [$quizId, $question, $type, $points, (int) $existingCount]
        );
        $questionId = (int) \App\Core\Database::connection()->lastInsertId();

        if ($type !== 'short_text') {
            $optionsText = (string) $request->input('options', '');
            $lines = array_values(array_filter(array_map('trim', explode("\n", $optionsText)), fn($l) => $l !== ''));
            foreach ($lines as $i => $line) {
                $isCorrect = str_starts_with($line, '*');
                $text = $isCorrect ? ltrim(substr($line, 1)) : $line;
                Quiz::statement(
                    'INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order) VALUES (?, ?, ?, ?)',
                    [$questionId, $text, $isCorrect ? 1 : 0, $i]
                );
            }
        }

        $this->flash('success', 'Question added.');
        $this->redirect('/lecturer/quizzes/' . $quizId);
    }

    // ── Attendance & grades ─────────────────────────────────────────────

    public function markAttendance(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $date = $request->input('session_date', date('Y-m-d'));
        $statuses = (array) $request->input('status', []);

        foreach ($statuses as $userId => $status) {
            Attendance::mark($intakeId, (int) $userId, $date, $status, (int) $user['id']);
        }

        $this->flash('success', 'Attendance saved for ' . $date . '.');
        $this->redirect('/lecturer/classes/' . $intakeId);
    }

    public function recordGrade(Request $request): void
    {
        $intakeId = (int) $request->param('intake');
        $user = $this->authorizeIntake($intakeId);
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'user_id' => 'required|integer',
            'component' => 'required|max:100',
            'score' => 'required|numeric',
            'max_score' => 'required|numeric',
        ]);

        Grade::insert([
            'intake_id' => $intakeId,
            'user_id' => $data['user_id'],
            'component' => $data['component'],
            'score' => $data['score'],
            'max_score' => $data['max_score'],
            'recorded_by' => $user['id'],
        ]);

        $this->flash('success', 'Grade recorded.');
        $this->redirect('/lecturer/classes/' . $intakeId);
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

        $this->redirect('/lecturer/classes/' . $intakeId . '#discussion');
    }
}
