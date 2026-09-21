<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Course;
use App\Models\CourseCategory;

class CourseController extends Controller
{
    public function index(Request $request): void
    {
        $category = $request->input('category');
        $search = $request->input('q');

        $courses = Course::catalogue($category ?: null, $search ?: null);
        $categories = CourseCategory::all('sort_order');

        $this->view('public.courses.index', [
            'pageTitle' => 'Course Catalogue — CPI',
            'courses' => $courses,
            'categories' => $categories,
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }

    public function show(Request $request): void
    {
        $course = Course::findBySlug((string) $request->param('slug'));
        if (!$course || $course['status'] !== 'published' || !$course['is_public']) {
            $this->abort(404, 'Course not found.');
            return;
        }

        $intakes = Course::openIntakes((int) $course['id']);
        $pillars = Course::pillars((int) $course['id']);

        $this->view('public.courses.show', [
            'pageTitle' => $course['title'] . ' — CPI',
            'course' => $course,
            'intakes' => $intakes,
            'pillars' => $pillars,
        ]);
    }
}
