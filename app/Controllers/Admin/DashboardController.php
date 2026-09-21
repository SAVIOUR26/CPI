<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireRole('super_admin', 'admissions', 'finance', 'registrar', 'content_manager');
        $db = Database::connection();

        $stats = [
            'active_enrollments' => (int) $db->query("SELECT COUNT(*) c FROM enrollments WHERE status='active'")->fetch()['c'],
            'pending_payments' => (int) $db->query("SELECT COUNT(*) c FROM payments WHERE status='pending_review'")->fetch()['c'],
            'new_corporate_requests' => (int) $db->query("SELECT COUNT(*) c FROM corporate_requests WHERE status='new'")->fetch()['c'],
            'pending_admissions' => (int) $db->query("SELECT COUNT(*) c FROM academic_applications WHERE status IN ('submitted','under_review')")->fetch()['c'],
            'revenue_total' => (float) $db->query("SELECT COALESCE(SUM(amount),0) t FROM payments WHERE status='successful'")->fetch()['t'],
            'published_courses' => (int) $db->query("SELECT COUNT(*) c FROM courses WHERE status='published'")->fetch()['c'],
        ];

        $recentPayments = $db->query(
            "SELECT p.*, u.full_name FROM payments p LEFT JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC LIMIT 8"
        )->fetchAll();

        $recentRequests = $db->query(
            "SELECT * FROM corporate_requests ORDER BY created_at DESC LIMIT 6"
        )->fetchAll();

        $this->view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard — CPI',
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'recentRequests' => $recentRequests,
        ], 'layouts.dashboard');
    }
}
