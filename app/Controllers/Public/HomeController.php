<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Pillar;

class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $pillars = Pillar::all('sort_order');
        $catalogue = Course::catalogue();

        $perCategory = [];
        $featured = [];
        foreach ($catalogue as $course) {
            $id = (int) ($course['category_id'] ?? 0);
            $perCategory[$id] = ($perCategory[$id] ?? 0) + 1;
            if ($perCategory[$id] === 1 && count($featured) < 6) {
                $featured[] = $course;
            }
        }
        $categories = [];
        foreach (CourseCategory::all('sort_order') as $cat) {
            $cat['course_count'] = $perCategory[(int) $cat['id']] ?? 0;
            if ($cat['course_count'] > 0) {
                $categories[] = $cat;
            }
        }

        $pillarCounts = [];
        $rows = Pillar::query(
            'SELECT cp.pillar_id, COUNT(*) AS n FROM course_pillars cp
             JOIN courses c ON c.id = cp.course_id
             WHERE c.status = "published" AND c.is_public = 1
             GROUP BY cp.pillar_id'
        );
        foreach ($rows as $row) {
            $pillarCounts[(int) $row['pillar_id']] = (int) $row['n'];
        }

        $this->view('public.home', [
            'pageTitle' => 'Crawford Professionals Institute (CPI) — Empowering Skills, Transforming Lives.',
            'pillars' => $pillars,
            'pillarCounts' => $pillarCounts,
            'categories' => $categories,
            'courseCount' => count($catalogue),
            'featured' => $featured,
        ]);
    }

    public function about(Request $request): void
    {
        $this->view('public.about', ['pageTitle' => 'About CPI — Crawford Professionals Institute']);
    }

    public function contact(Request $request): void
    {
        $this->view('public.contact', ['pageTitle' => 'Contact CPI — Crawford Professionals Institute']);
    }
}
