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

        $perPage = 24;
        $total = count($courses);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($pages, max(1, (int) $request->input('page', 1)));

        $this->view('public.courses.index', [
            'pageTitle' => 'Course Catalogue — CPI',
            'courses' => array_slice($courses, ($page - 1) * $perPage, $perPage),
            'categories' => $categories,
            'activeCategory' => $category,
            'search' => $search,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
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
        $category = $course['category_id'] ? CourseCategory::find((int) $course['category_id']) : null;

        $this->view('public.courses.show', [
            'pageTitle' => $course['title'] . ' — CPI',
            'course' => $course,
            'intakes' => $intakes,
            'pillars' => $pillars,
            'category' => $category,
        ]);
    }
}
