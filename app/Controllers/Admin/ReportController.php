<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ReportController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('reports.view');
        $db = Database::connection();

        $enrolmentsByMonth = $db->query(
            "SELECT DATE_FORMAT(enrolled_at, '%Y-%m') ym, COUNT(*) c FROM enrollments
             GROUP BY ym ORDER BY ym DESC LIMIT 12"
        )->fetchAll();

        $revenueByMonth = $db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COALESCE(SUM(amount),0) total FROM payments
             WHERE status = 'successful' GROUP BY ym ORDER BY ym DESC LIMIT 12"
        )->fetchAll();

        $topCourses = $db->query(
            "SELECT c.title, COUNT(e.id) enrolments FROM enrollments e
             JOIN intakes i ON i.id = e.intake_id JOIN courses c ON c.id = i.course_id
             GROUP BY c.id ORDER BY enrolments DESC LIMIT 10"
        )->fetchAll();

        $this->view('admin.reports.index', [
            'pageTitle' => 'Reports — CPI Admin',
            'enrolmentsByMonth' => $enrolmentsByMonth,
            'revenueByMonth' => $revenueByMonth,
            'topCourses' => $topCourses,
        ], 'layouts.dashboard');
    }
}
