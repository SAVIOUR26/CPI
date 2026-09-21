<?php

namespace App\Controllers\Academic;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Upload;
use App\Models\AcademicApplication;
use App\Models\AcademicProgramme;
use App\Models\Setting;

class GatewayController extends Controller
{
    public function index(Request $request): void
    {
        $programmes = AcademicProgramme::published();
        $this->view('academic.index', [
            'pageTitle' => 'Academic Programmes — CPI',
            'noindex' => true,
            'programmes' => $programmes,
        ]);
    }

    public function showApply(Request $request): void
    {
        $programme = AcademicProgramme::withCourse((int) $request->param('programme'));
        if (!$programme) {
            $this->abort(404, 'Programme not found.');
            return;
        }

        $this->view('academic.apply', [
            'pageTitle' => 'Apply — ' . $programme['title'],
            'noindex' => true,
            'programme' => $programme,
        ]);
    }

    public function submitApply(Request $request): void
    {
        $this->verifyCsrf($request);
        $programmeId = (int) $request->param('programme');
        $programme = AcademicProgramme::withCourse($programmeId);
        if (!$programme) {
            $this->abort(404, 'Programme not found.');
            return;
        }

        $data = $this->validate($request, [
            'applicant_name' => 'required|max:150',
            'email' => 'required|email',
            'phone' => 'max:30',
        ]);

        $documentsPath = null;
        $file = $request->file('documents');
        if ($file) {
            try {
                $documentsPath = Upload::store($file, 'academic-applications', 15 * 1024 * 1024);
            } catch (\RuntimeException $e) {
                $this->flash('error', $e->getMessage());
                $this->redirect('/academic/apply/' . $programmeId);
                return;
            }
        }

        AcademicApplication::insert([
            'programme_id' => $programmeId,
            'applicant_name' => $data['applicant_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'documents_path' => $documentsPath,
            'status' => 'submitted',
        ]);

        $supportEmail = Setting::get('support_email', 'info@crawfordinstitute.online');
        Mailer::send(
            $supportEmail,
            'CPI Admissions',
            'New academic application: ' . $programme['title'],
            '<p>New application from ' . e($data['applicant_name']) . ' (' . e($data['email']) . ') for '
            . e($programme['title']) . '.</p>'
        );

        Mailer::send(
            $data['email'],
            $data['applicant_name'],
            'Application received — Crawford Professionals Institute',
            '<p>Dear ' . e($data['applicant_name']) . ',</p><p>We have received your application for <strong>'
            . e($programme['title']) . '</strong>. Our Admissions team will review it and be in touch.</p>'
        );

        $this->flash('success', 'Your application has been submitted. We will contact you with a decision soon.');
        $this->redirect('/academic');
    }
}
