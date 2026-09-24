<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\CalendarEvent;
use App\Models\Intake;
use App\Models\Timetable;
use App\Models\User;

/** The academic calendar: key dates for everyone or one class, and each class's weekly timetable. */
class CalendarController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $intakes = Intake::forSelect();
        $selected = (int) $request->input('intake', 0);
        $selectedIntake = null;
        foreach ($intakes as $i) {
            if ((int) $i['id'] === $selected) {
                $selectedIntake = $i;
            }
        }

        $this->view('admin.calendar.index', [
            'pageTitle' => 'Calendar & timetable — CPI Admin',
            'events' => CalendarEvent::listAll(),
            'intakes' => $intakes,
            'selectedIntake' => $selectedIntake,
            'slots' => $selectedIntake ? Timetable::forIntake((int) $selectedIntake['id']) : [],
            'lecturers' => User::lecturers(),
        ], 'layouts.dashboard');
    }

    public function storeEvent(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'title' => 'required|max:190',
            'category' => 'required|in:' . implode(',', array_keys(CalendarEvent::CATEGORIES)),
            'starts_on' => 'required|date',
            'ends_on' => 'date',
            'notes' => 'max:500',
        ]);
        $starts = date('Y-m-d', strtotime($data['starts_on']));
        $ends = $data['ends_on'] ? date('Y-m-d', strtotime($data['ends_on'])) : null;
        if ($ends !== null && $ends < $starts) {
            $this->flash('error', 'The end date must be on or after the start date.');
            $this->redirect('/admin/calendar#dates');
        }
        $intakeId = (int) $request->input('intake_id', 0) ?: null;
        if ($intakeId && !Intake::find($intakeId)) {
            $intakeId = null;
        }

        $id = CalendarEvent::insert([
            'intake_id' => $intakeId,
            'category' => $data['category'],
            'title' => trim($data['title']),
            'starts_on' => $starts,
            'ends_on' => $ends !== $starts ? $ends : null,
            'notes' => trim((string) $data['notes']) ?: null,
            'created_by' => Auth::id(),
        ]);
        AuditLog::record('calendar_event.create', 'calendar_event', $id);
        $this->flash('success', 'Date added to the calendar.');
        $this->redirect('/admin/calendar#dates');
    }

    public function destroyEvent(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $event = CalendarEvent::find((int) $request->param('event'));
        if (!$event) {
            $this->abort(404, 'Date not found.');
        }
        CalendarEvent::delete((int) $event['id']);
        AuditLog::record('calendar_event.delete', 'calendar_event', (int) $event['id']);
        $this->flash('success', 'Date removed.');
        $this->redirect('/admin/calendar#dates');
    }

    public function storeSlot(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $data = $this->validate($request, [
            'intake_id' => 'required|integer',
            'day_of_week' => 'required|in:1,2,3,4,5,6,7',
            'start_time' => 'required',
            'end_time' => 'required',
            'venue' => 'max:190',
        ]);
        $intakeId = (int) $data['intake_id'];
        $back = '/admin/calendar?intake=' . $intakeId . '#timetable';
        if (!Intake::find($intakeId)) {
            $this->abort(404, 'Class not found.');
        }
        $time = '/^([01]\d|2[0-3]):[0-5]\d$/';
        if (!preg_match($time, (string) $data['start_time']) || !preg_match($time, (string) $data['end_time']) || $data['end_time'] <= $data['start_time']) {
            $this->flash('error', 'Enter a start and end time, with the end after the start.');
            $this->redirect($back);
        }
        $lecturerId = (int) $request->input('lecturer_id', 0) ?: null;

        Timetable::insert([
            'intake_id' => $intakeId,
            'day_of_week' => (int) $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'venue' => trim((string) $data['venue']) ?: null,
            'lecturer_id' => $lecturerId,
        ]);
        $this->flash('success', 'Class time added.');
        $this->redirect($back);
    }

    public function destroySlot(Request $request): void
    {
        $this->requirePermission('academic.manage');
        $this->verifyCsrf($request);
        $slot = Timetable::find((int) $request->param('slot'));
        if (!$slot) {
            $this->abort(404, 'Class time not found.');
        }
        Timetable::delete((int) $slot['id']);
        $this->flash('success', 'Class time removed.');
        $this->redirect('/admin/calendar?intake=' . (int) $slot['intake_id'] . '#timetable');
    }
}
