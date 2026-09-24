<?php

namespace App\Controllers\Lecturer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Intake;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireRole('lecturer', 'super_admin');
        $intakes = Intake::forLecturer((int) Auth::id());

        $this->view('lecturer.dashboard', [
            'pageTitle' => 'My Classes — CPI',
            'intakes' => $intakes,
            'announcements' => Announcement::forLecturers(3),
            'dates' => CalendarEvent::forLecturer((int) Auth::id(), date('Y-m-d'), 4),
        ], 'layouts.dashboard');
    }
}
