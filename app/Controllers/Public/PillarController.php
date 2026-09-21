<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Pillar;

class PillarController extends Controller
{
    private array $meta = [
        'professional-training' => [
            'title' => 'Professional Training',
            'lead' => 'Short, practical courses that build in-demand professional skills — for individuals investing in their own careers.',
        ],
        'capacity-building' => [
            'title' => 'Capacity Building',
            'lead' => 'Programmes for NGOs, government agencies and donor-funded projects to strengthen institutional and staff capacity.',
        ],
        'corporate-training' => [
            'title' => 'Corporate Training',
            'lead' => 'A public catalogue your organization can enrol staff into directly, plus customized training built around your team\'s needs.',
        ],
    ];

    public function forSlug(string $slug): void
    {
        $pillar = Pillar::findBySlug($slug);
        if (!$pillar || !isset($this->meta[$slug])) {
            $this->abort(404, 'Pillar not found.');
            return;
        }

        $courses = Course::publishedForPillar($slug);

        $this->view('public.pillar', [
            'pageTitle' => $this->meta[$slug]['title'] . ' — CPI',
            'pillar' => $pillar,
            'meta' => $this->meta[$slug],
            'slug' => $slug,
            'courses' => $courses,
        ]);
    }
}
