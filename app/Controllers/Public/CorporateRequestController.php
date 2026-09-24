<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Support\Institute;
use App\Models\Course;
use App\Models\CorporateRequest;
use App\Models\Setting;

class CorporateRequestController extends Controller
{
    public function create(Request $request): void
    {
        $courses = Course::query(
            'SELECT id, title FROM courses WHERE status = "published" ORDER BY title'
        );

        $this->view('public.corporate-request', [
            'pageTitle' => 'Request Corporate Training — CPI',
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'organization_name' => 'required|max:190',
            'contact_name' => 'required|max:150',
            'contact_email' => 'required|email',
            'contact_phone' => 'max:30',
            'topic' => 'required|max:190',
            'headcount' => 'numeric',
            'location' => 'max:190',
            'mode' => 'required|in:' . implode(',', array_keys(Institute::MODES)),
            'preferred_dates' => 'max:190',
            'message' => 'max:2000',
        ]);

        $data['course_id'] = $request->input('course_id') ?: null;
        $data['budget_note'] = $request->input('budget_note');
        $data['headcount'] = $data['headcount'] !== '' ? (int) $data['headcount'] : null;

        $id = CorporateRequest::insert($data);

        $supportEmail = Setting::get('support_email', 'info@crawfordinstitute.online');
        Mailer::send(
            $supportEmail,
            'CPI Admissions',
            "New corporate training request: {$data['organization_name']}",
            "<p>A new corporate training request was submitted.</p>
             <p><strong>Organization:</strong> " . e($data['organization_name']) . "<br>
             <strong>Contact:</strong> " . e($data['contact_name']) . " (" . e($data['contact_email']) . ")<br>
             <strong>Topic:</strong> " . e($data['topic']) . "<br>
             <strong>Headcount:</strong> " . e((string) ($data['headcount'] ?? '—')) . "</p>
             <p>Review it in the admin portal (Corporate Requests).</p>"
        );

        Mailer::send(
            $data['contact_email'],
            $data['contact_name'],
            'We received your training request — CPI',
            "<p>Dear " . e($data['contact_name']) . ",</p>
             <p>Thank you for your interest in training with Crawford Professionals Institute (CPI). We have received your request for
             <strong>" . e($data['topic']) . "</strong> and our team will reach out with a proposal shortly.</p>
             <p>— Crawford Professionals Institute</p>"
        );

        $this->flash('success', 'Thank you — your training request has been received. Our team will contact you shortly with a proposal.');
        $this->redirect('/corporate-training');
    }
}
