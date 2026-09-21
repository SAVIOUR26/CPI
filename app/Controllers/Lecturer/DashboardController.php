<?php

namespace App\Controllers\Lecturer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
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
        ], 'layouts.dashboard');
    }
}
