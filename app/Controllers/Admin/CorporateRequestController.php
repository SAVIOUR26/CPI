<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Upload;
use App\Models\CorporateRequest;
use App\Models\Course;
use App\Models\Intake;
use App\Models\Organization;
use App\Support\Str;

class CorporateRequestController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('corporate.requests.manage');
        $requests = CorporateRequest::query('SELECT * FROM corporate_requests ORDER BY created_at DESC');

        $this->view('admin.corporate-requests.index', [
            'pageTitle' => 'Corporate Requests — CPI Admin',
            'requests' => $requests,
        ], 'layouts.dashboard');
    }

    public function show(Request $request): void
    {
        $this->requirePermission('corporate.requests.manage');
        $cr = CorporateRequest::find((int) $request->param('request'));
        if (!$cr) {
            $this->abort(404, 'Request not found.');
            return;
        }
        $courses = Course::query('SELECT id, title FROM courses WHERE status = "published" ORDER BY title');
        $organizations = Organization::all('name');

        $this->view('admin.corporate-requests.show', [
            'pageTitle' => 'Corporate Request — ' . $cr['organization_name'],
            'cr' => $cr,
            'courses' => $courses,
            'organizations' => $organizations,
        ], 'layouts.dashboard');
    }

    public function quoteFile(Request $request): void
    {
        $this->requirePermission('corporate.requests.manage');
        $cr = CorporateRequest::find((int) $request->param('request'));
        if (!$cr || !$cr['quote_pdf_path'] || !Upload::exists($cr['quote_pdf_path'])) {
            $this->abort(404, 'Quote not found.');
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="quote-' . $cr['id'] . '.pdf"');
        readfile(Upload::absolutePath($cr['quote_pdf_path']));
        exit;
    }

    public function updateStatus(Request $request): void
    {
        $user = $this->requirePermissionAndUser('corporate.requests.manage');
        $this->verifyCsrf($request);
        $id = (int) $request->param('request');
        $cr = CorporateRequest::find($id);
        if (!$cr) {
            $this->abort(404, 'Request not found.');
            return;
        }

        $status = $request->input('status');
        CorporateRequest::update($id, ['status' => $status, 'handled_by' => $user['id']]);

        if (in_array($status, ['quoted', 'accepted', 'declined'], true)) {
            Mailer::send(
                $cr['contact_email'],
                $cr['contact_name'],
                'Update on your CPI training request — ' . $cr['topic'],
                '<p>Dear ' . e($cr['contact_name']) . ',</p><p>Your training request status is now: <strong>' . e(ucfirst($status)) . '</strong>.</p>'
                . '<p>Our team will follow up with next steps.</p>'
            );
        }

        AuditLog::record('corporate_request.status', 'corporate_request', $id, ['status' => $status]);
        $this->flash('success', 'Status updated.');
        $this->redirect('/admin/corporate-requests/' . $id);
    }

    public function uploadQuote(Request $request): void
    {
        $this->requirePermission('corporate.requests.manage');
        $this->verifyCsrf($request);
        $id = (int) $request->param('request');
        $cr = CorporateRequest::find($id);
        if (!$cr) {
            $this->abort(404, 'Request not found.');
            return;
        }

        $file = $request->file('quote');
        if (!$file) {
            $this->flash('error', 'Please attach a quote PDF.');
            $this->redirect('/admin/corporate-requests/' . $id);
            return;
        }

        try {
            $path = Upload::store($file, 'quotes');
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/corporate-requests/' . $id);
            return;
        }

        CorporateRequest::update($id, ['quote_pdf_path' => $path, 'status' => 'quoted']);

        Mailer::send(
            $cr['contact_email'],
            $cr['contact_name'],
            'Your CPI training quote — ' . $cr['topic'],
            '<p>Dear ' . e($cr['contact_name']) . ',</p><p>Please find attached our quotation for your training request. Reply to this email to accept or discuss further.</p>'
        );

        $this->flash('success', 'Quote uploaded and client notified.');
        $this->redirect('/admin/corporate-requests/' . $id);
    }

    /** Converts an accepted corporate request into an organization + a dedicated cohort (intake). */
    public function convert(Request $request): void
    {
        $user = $this->requirePermissionAndUser('corporate.requests.manage');
        $this->verifyCsrf($request);
        $id = (int) $request->param('request');
        $cr = CorporateRequest::find($id);
        if (!$cr) {
            $this->abort(404, 'Request not found.');
            return;
        }

        $courseId = (int) $request->input('course_id');
        $course = Course::find($courseId);
        if (!$course) {
            $this->flash('error', 'Select a course to base this cohort on.');
            $this->redirect('/admin/corporate-requests/' . $id);
            return;
        }

        $organizationId = $request->input('organization_id') ?: null;
        if (!$organizationId) {
            $organizationId = Organization::insert([
                'name' => $cr['organization_name'],
                'address' => $cr['location'],
            ]);
        }

        $code = 'CORP-' . strtoupper(Str::random(6));
        $intakeId = Intake::insert([
            'course_id' => $courseId,
            'organization_id' => $organizationId,
            'code' => $code,
            // Cohorts are online, physical or hybrid; on-site and in-house training is delivered in person.
            'mode' => in_array($cr['mode'], ['online', 'in_person', 'hybrid'], true) ? $cr['mode'] : 'in_person',
            'venue' => $cr['location'],
            'start_date' => $request->input('start_date', date('Y-m-d', strtotime('+2 weeks'))),
            'capacity' => $cr['headcount'],
            'status' => 'scheduled',
        ]);

        CorporateRequest::update($id, [
            'organization_id' => $organizationId,
            'resulting_intake_id' => $intakeId,
            'status' => 'converted',
            'handled_by' => $user['id'],
        ]);

        AuditLog::record('corporate_request.convert', 'corporate_request', $id, ['intake_id' => $intakeId]);
        $this->flash('success', 'Converted into a dedicated cohort. You can now bulk-enrol staff into it from the organization page.');
        $this->redirect('/admin/organizations/' . $organizationId);
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
