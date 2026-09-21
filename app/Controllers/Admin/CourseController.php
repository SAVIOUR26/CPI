<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Intake;
use App\Models\Pillar;
use App\Models\User;
use App\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $courses = Course::query(
            'SELECT c.*, cat.name AS category_name FROM courses c LEFT JOIN course_categories cat ON cat.id = c.category_id ORDER BY c.created_at DESC'
        );

        $this->view('admin.courses.index', [
            'pageTitle' => 'Courses & Intakes — CPI Admin',
            'courses' => $courses,
        ], 'layouts.dashboard');
    }

    public function create(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $this->view('admin.courses.form', [
            'pageTitle' => 'New Course — CPI Admin',
            'course' => null,
            'categories' => CourseCategory::all('sort_order'),
            'pillars' => Pillar::all('sort_order'),
            'selectedPillars' => [],
        ], 'layouts.dashboard');
    }

    public function store(Request $request): void
    {
        $user = $this->requirePermissionAndUser('courses.manage');
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'summary' => 'max:500',
            'programme_type' => 'required|in:short_course,corporate,academic',
            'level' => 'required|in:foundation,intermediate,advanced',
            'price_amount' => 'numeric',
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (Course::findBySlug($slug)) {
            $slug = $slugBase . '-' . (++$i);
        }

        $courseId = Course::insert([
            'category_id' => $request->input('category_id') ?: null,
            'title' => $data['title'],
            'slug' => $slug,
            'summary' => $data['summary'],
            'description' => $request->input('description'),
            'programme_type' => $data['programme_type'],
            'level' => $data['level'],
            'duration_note' => $request->input('duration_note'),
            'price_amount' => $data['price_amount'] ?: 0,
            'price_currency' => $request->input('price_currency', 'UGX'),
            'is_public' => $request->input('is_public') ? 1 : 0,
            'status' => $request->input('status', 'draft'),
            'created_by' => $user['id'],
        ]);

        $pillarIds = array_map('intval', (array) $request->input('pillars', []));
        Course::setPillars($courseId, $pillarIds);

        $this->flash('success', 'Course created.');
        $this->redirect('/admin/courses/' . $courseId);
    }

    public function edit(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $course = Course::find((int) $request->param('course'));
        if (!$course) {
            $this->abort(404, 'Course not found.');
            return;
        }
        $selectedPillars = array_column(Course::pillars((int) $course['id']), 'id');
        $intakes = Course::query('SELECT * FROM intakes WHERE course_id = ? ORDER BY start_date DESC', [$course['id']]);
        $lecturers = User::query(
            "SELECT u.id, u.full_name FROM users u JOIN role_user ru ON ru.user_id = u.id JOIN roles r ON r.id = ru.role_id
             WHERE r.slug = 'lecturer' ORDER BY u.full_name"
        );

        $this->view('admin.courses.form', [
            'pageTitle' => 'Edit Course — CPI Admin',
            'course' => $course,
            'categories' => CourseCategory::all('sort_order'),
            'pillars' => Pillar::all('sort_order'),
            'selectedPillars' => $selectedPillars,
            'intakes' => $intakes,
            'lecturers' => $lecturers,
        ], 'layouts.dashboard');
    }

    public function update(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $this->verifyCsrf($request);
        $courseId = (int) $request->param('course');
        $course = Course::find($courseId);
        if (!$course) {
            $this->abort(404, 'Course not found.');
            return;
        }

        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'summary' => 'max:500',
            'programme_type' => 'required|in:short_course,corporate,academic',
            'level' => 'required|in:foundation,intermediate,advanced',
            'price_amount' => 'numeric',
        ]);

        Course::update($courseId, [
            'category_id' => $request->input('category_id') ?: null,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description' => $request->input('description'),
            'programme_type' => $data['programme_type'],
            'level' => $data['level'],
            'duration_note' => $request->input('duration_note'),
            'price_amount' => $data['price_amount'] ?: 0,
            'price_currency' => $request->input('price_currency', 'UGX'),
            'is_public' => $request->input('is_public') ? 1 : 0,
            'status' => $request->input('status', 'draft'),
        ]);

        $pillarIds = array_map('intval', (array) $request->input('pillars', []));
        Course::setPillars($courseId, $pillarIds);

        $this->flash('success', 'Course updated.');
        $this->redirect('/admin/courses/' . $courseId);
    }

    public function addIntake(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $this->verifyCsrf($request);
        $courseId = (int) $request->param('course');
        $course = Course::find($courseId);
        if (!$course) {
            $this->abort(404, 'Course not found.');
            return;
        }

        $data = $this->validate($request, [
            'code' => 'required|max:60',
            'mode' => 'required|in:online,in_person,hybrid',
            'start_date' => 'required|date',
        ]);

        Intake::insert([
            'course_id' => $courseId,
            'code' => $data['code'],
            'mode' => $data['mode'],
            'venue' => $request->input('venue'),
            'start_date' => $data['start_date'],
            'end_date' => $request->input('end_date') ?: null,
            'capacity' => $request->input('capacity') ?: null,
            'primary_lecturer_id' => $request->input('primary_lecturer_id') ?: null,
            'status' => $request->input('status', 'scheduled'),
        ]);

        $this->flash('success', 'Intake added.');
        $this->redirect('/admin/courses/' . $courseId);
    }

    public function updateIntakeStatus(Request $request): void
    {
        $this->requirePermission('courses.manage');
        $this->verifyCsrf($request);
        $intakeId = (int) $request->param('intake');
        $intake = Intake::find($intakeId);
        if (!$intake) {
            $this->abort(404, 'Intake not found.');
            return;
        }
        $update = ['status' => $request->input('status')];
        if ($request->input('primary_lecturer_id') !== null) {
            $update['primary_lecturer_id'] = $request->input('primary_lecturer_id') ?: null;
        }
        Intake::update($intakeId, $update);
        $this->flash('success', 'Intake updated.');
        $this->redirect('/admin/courses/' . $intake['course_id']);
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
