<?php

namespace App\Controllers\Learner;

use App\Core\Controller;
use App\Core\Request;
use App\Models\AcademicApplication;
use App\Models\AcademicProgramme;
use App\Models\Announcement;
use App\Models\CalendarEvent;
use App\Models\Enrollment;
use App\Models\Timetable;
use App\Support\ApplicationDocuments;
use App\Support\Results;

/** Student Portal pages from the client's portal brief: announcements, calendar, results and admission. */
class PortalController extends Controller
{
    public function announcements(Request $request): void
    {
        $user = $this->requireAuth();
        $this->view('learner.announcements', [
            'pageTitle' => 'Announcements — CPI',
            'announcements' => Announcement::forStudent((int) $user['id']),
        ], 'layouts.dashboard');
    }

    public function calendar(Request $request): void
    {
        $user = $this->requireAuth();
        $this->view('learner.calendar', [
            'pageTitle' => 'Academic calendar — CPI',
            'events' => CalendarEvent::forStudent((int) $user['id'], date('Y-m-d')),
            'timetable' => Timetable::forUser((int) $user['id']),
        ], 'layouts.dashboard');
    }

    public function results(Request $request): void
    {
        $user = $this->requireAuth();
        $classes = [];
        foreach (Enrollment::forUser((int) $user['id']) as $e) {
            if (in_array($e['status'], ['active', 'completed'], true)) {
                $classes[] = $e + ['results' => Results::forStudent((int) $e['intake_id'], (int) $user['id'])];
            }
        }
        $averages = array_filter(array_map(fn ($c) => $c['results']['average'], $classes), fn ($a) => $a !== null);

        $this->view('learner.results', [
            'pageTitle' => 'My results — CPI',
            'classes' => $classes,
            'overall' => $averages ? round(array_sum($averages) / count($averages), 1) : null,
            'assessed' => array_sum(array_map(fn ($c) => count($c['results']['rows']), $classes)),
        ], 'layouts.dashboard');
    }

    public function admission(Request $request): void
    {
        $user = $this->requireAuth();
        $applications = AcademicApplication::forUser((int) $user['id']);
        foreach ($applications as &$app) {
            $app['files'] = ApplicationDocuments::list($app, '/learner/admission/' . (int) $app['id'] . '/files/');
        }
        unset($app);

        $this->view('learner.admission', [
            'pageTitle' => 'My admission — CPI',
            'applications' => $applications,
            'levels' => AcademicProgramme::levels(),
        ], 'layouts.dashboard');
    }

    public function admissionLetter(Request $request): void
    {
        $user = $this->requireAuth();
        $app = $this->ownApplication($request, (int) $user['id']);
        if ($app['status'] !== 'admitted') {
            $this->abort(404, 'An admission letter is available once you have been admitted.');
        }
        $this->view('learner.admission-letter', [
            'pageTitle' => 'Admission letter — ' . ($app['application_no'] ?: 'CPI'),
            'app' => $app,
            'student' => $user,
            'level' => AcademicProgramme::levels()[$app['award_level']] ?? null,
        ], 'layouts.dashboard');
    }

    public function admissionFile(Request $request): void
    {
        $user = $this->requireAuth();
        $app = $this->ownApplication($request, (int) $user['id']);
        $key = (string) $request->param('key');
        $index = max(0, (int) $request->input('i', 0));
        $path = ApplicationDocuments::path($app, $key, $index);
        if (!$path) {
            $this->abort(404, 'Document not found.');
        }
        ApplicationDocuments::send($path, ApplicationDocuments::downloadName($app, $key, $index));
    }

    /** Only the student's own applications are ever shown or served. */
    private function ownApplication(Request $request, int $userId): array
    {
        $id = (int) $request->param('application');
        foreach (AcademicApplication::forUser($userId) as $app) {
            if ((int) $app['id'] === $id) {
                return $app;
            }
        }
        $this->abort(404, 'Application not found.');
    }
}
