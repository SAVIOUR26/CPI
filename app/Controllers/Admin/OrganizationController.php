<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\Organization;
use App\Models\User;
use App\Support\NewAccounts;

class OrganizationController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('corporate.orgs.manage');
        $orgs = Organization::query(
            'SELECT o.*, (SELECT COUNT(*) FROM intakes i WHERE i.organization_id = o.id) AS cohort_count FROM organizations o ORDER BY o.name'
        );

        $this->view('admin.organizations.index', [
            'pageTitle' => 'Organizations — CPI Admin',
            'orgs' => $orgs,
        ], 'layouts.dashboard');
    }

    public function store(Request $request): void
    {
        $this->requirePermission('corporate.orgs.manage');
        $this->verifyCsrf($request);
        $data = $this->validate($request, ['name' => 'required|max:190']);

        Organization::insert([
            'name' => $data['name'],
            'sector' => $request->input('sector'),
            'address' => $request->input('address'),
        ]);

        $this->flash('success', 'Organization added.');
        $this->redirect('/admin/organizations');
    }

    public function show(Request $request): void
    {
        $this->requirePermission('corporate.orgs.manage');
        $org = Organization::find((int) $request->param('org'));
        if (!$org) {
            $this->abort(404, 'Organization not found.');
            return;
        }

        $cohorts = Intake::query(
            'SELECT i.*, c.title AS course_title FROM intakes i JOIN courses c ON c.id = i.course_id
             WHERE i.organization_id = ? ORDER BY i.start_date DESC',
            [$org['id']]
        );
        $members = Organization::query(
            'SELECT u.id, u.full_name, u.email, om.title FROM organization_members om
             JOIN users u ON u.id = om.user_id WHERE om.organization_id = ?',
            [$org['id']]
        );

        $this->view('admin.organizations.show', [
            'pageTitle' => $org['name'] . ' — CPI Admin',
            'org' => $org,
            'cohorts' => $cohorts,
            'members' => $members,
        ], 'layouts.dashboard');
    }

    /** Set (or change) the organization's portal contact — creates the account if needed. */
    public function setContact(Request $request): void
    {
        $this->requirePermission('corporate.orgs.manage');
        $this->verifyCsrf($request);
        $orgId = (int) $request->param('org');
        $org = Organization::find($orgId);
        if (!$org) {
            $this->abort(404, 'Organization not found.');
            return;
        }

        $data = $this->validate($request, [
            'contact_name' => 'required|max:150',
            'contact_email' => 'required|email',
        ]);

        $user = User::findByEmail($data['contact_email']);
        $tempPassword = null;
        if (!$user) {
            $tempPassword = bin2hex(random_bytes(5));
            $userId = User::insert([
                'full_name' => $data['contact_name'],
                'email' => $data['contact_email'],
                'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
                'status' => 'active',
            ]);
        } else {
            $userId = (int) $user['id'];
        }

        User::assignRole($userId, 'corporate_contact');
        Organization::update($orgId, ['contact_user_id' => $userId]);
        Organization::statement(
            'INSERT IGNORE INTO organization_members (organization_id, user_id, title) VALUES (?, ?, ?)',
            [$orgId, $userId, 'Primary Contact']
        );

        $loginNote = $tempPassword
            ? "<p>Temporary password: <strong>" . e($tempPassword) . "</strong> — please change it after logging in.</p>"
            : '';
        $emailed = Mailer::send(
            $data['contact_email'],
            $data['contact_name'],
            'Your CPI Corporate Portal access — ' . $org['name'],
            "<p>Dear " . e($data['contact_name']) . ",</p>
             <p>You now have access to the CPI Corporate Portal for <strong>" . e($org['name']) . "</strong>, where you can track your
             organization's training cohorts and invoices.</p>
             <p>Login: <a href=\"" . url('/login') . "\">" . url('/login') . "</a><br>Email: " . e($data['contact_email']) . "</p>"
             . $loginNote
        );

        if ($tempPassword) {
            NewAccounts::add($data['contact_name'], $data['contact_email'], $tempPassword, 'corporate', $emailed);
        }
        $this->flash('success', 'Corporate contact set' . ($emailed ? ' and notified.' : ' — the email could not be sent, so let them know directly.'));
        $this->redirect('/admin/organizations/' . $orgId);
    }

    /** Bulk-enrol staff into a cohort — paste "Full Name, email" lines, or upload a CSV with the same two columns. */
    public function bulkEnrol(Request $request): void
    {
        $this->requirePermission('corporate.orgs.manage');
        $this->verifyCsrf($request);
        $orgId = (int) $request->param('org');
        $intakeId = (int) $request->input('intake_id');
        $org = Organization::find($orgId);
        $intake = Intake::find($intakeId);

        if (!$org || !$intake || (int) $intake['organization_id'] !== $orgId) {
            $this->abort(404, 'Cohort not found for this organization.');
            return;
        }

        $rows = [];
        $file = $request->file('csv');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
                while (($line = fgetcsv($handle)) !== false) {
                    if (count($line) >= 2) {
                        $rows[] = [trim($line[0]), trim($line[1])];
                    }
                }
                fclose($handle);
            }
        } else {
            $pasted = (string) $request->input('roster_text', '');
            foreach (explode("\n", $pasted) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode(',', $line, 2));
                if (count($parts) === 2) {
                    $rows[] = $parts;
                }
            }
        }

        $enrolled = 0;
        foreach ($rows as [$name, $email]) {
            if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $user = User::findByEmail($email);
            if (!$user) {
                $tempPassword = bin2hex(random_bytes(5));
                $userId = User::insert([
                    'full_name' => $name,
                    'email' => $email,
                    'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
                    'status' => 'active',
                ]);
                User::assignRole($userId, 'learner');
                $emailed = Mailer::send(
                    $email,
                    $name,
                    'Your CPI training account — ' . $intake['code'],
                    "<p>Dear " . e($name) . ",</p><p>An account has been created for you to access training via "
                    . e($org['name']) . " with Crawford Professionals Institute.</p>"
                    . "<p>Login: <a href=\"" . url('/login') . "\">" . url('/login') . "</a><br>Email: " . e($email)
                    . "<br>Temporary password: <strong>" . e($tempPassword) . "</strong></p>"
                );
                NewAccounts::add($name, $email, $tempPassword, 'student', $emailed);
            } else {
                $userId = (int) $user['id'];
            }

            Organization::statement(
                'INSERT IGNORE INTO organization_members (organization_id, user_id) VALUES (?, ?)',
                [$orgId, $userId]
            );

            if (!Enrollment::existing($userId, $intakeId)) {
                Enrollment::insert([
                    'user_id' => $userId,
                    'intake_id' => $intakeId,
                    'source' => 'corporate',
                    'status' => 'active',
                ]);
                Intake::incrementSeats($intakeId);
                $enrolled++;
            }
        }

        AuditLog::record('organization.bulk_enrol', 'intake', $intakeId, ['count' => $enrolled]);
        $this->flash('success', "$enrolled staff member(s) enrolled into {$intake['code']}.");
        $this->redirect('/admin/organizations/' . $orgId);
    }
}
