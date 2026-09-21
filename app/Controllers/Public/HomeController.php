<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Course;
use App\Models\Pillar;

class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $pillars = Pillar::all('sort_order');
        $featured = Course::catalogue();

        $this->view('public.home', [
            'pageTitle' => 'Crawford Professionals Institute (CPI) — Empowering Skills, Transforming Lives.',
            'pillars' => $pillars,
            'featured' => array_slice($featured, 0, 3),
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
