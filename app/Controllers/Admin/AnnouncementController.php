<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Announcement;
use App\Models\Intake;

/** Institute-wide and class announcements, shown in the Student and Lecturer portals. */
class AnnouncementController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->view('admin.announcements.index', [
            'pageTitle' => 'Announcements — CPI Admin',
            'announcements' => Announcement::recent(),
            'intakes' => Intake::forSelect(),
        ], 'layouts.dashboard');
    }

    public function store(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'body' => 'required|max:5000',
            'target' => 'required',
        ]);

        // "everyone" / "students" / "lecturers" for the whole institute, or "intake:ID" for one class.
        $target = (string) $data['target'];
        $intakeId = null;
        $audience = 'students';
        if (isset(Announcement::AUDIENCES[$target])) {
            $audience = $target;
        } elseif (preg_match('/^intake:(\d+)$/', $target, $m) && Intake::find((int) $m[1])) {
            $intakeId = (int) $m[1];
        } else {
            $this->flash('error', 'Choose who should see this announcement.');
            $this->redirect('/admin/announcements');
        }

        $id = Announcement::insert([
            'intake_id' => $intakeId,
            'audience' => $audience,
            'title' => trim($data['title']),
            'body' => trim($data['body']),
            'pinned' => $request->input('pinned') ? 1 : 0,
            'created_by' => Auth::id(),
        ]);
        AuditLog::record('announcement.create', 'announcement', $id, ['target' => $target]);
        $this->flash('success', 'Announcement published.');
        $this->redirect('/admin/announcements');
    }

    public function destroy(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $announcement = Announcement::find((int) $request->param('announcement'));
        if (!$announcement) {
            $this->abort(404, 'Announcement not found.');
        }
        Announcement::delete((int) $announcement['id']);
        AuditLog::record('announcement.delete', 'announcement', (int) $announcement['id']);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('/admin/announcements');
    }
}
