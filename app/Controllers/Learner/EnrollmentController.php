<?php

namespace App\Controllers\Learner;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\Invoice;

class EnrollmentController extends Controller
{
    /** Kick off self-enrolment into an intake: create (or reuse) the enrollment + invoice, then send to payment. */
    public function enroll(Request $request): void
    {
        $this->requireAuth();

        $intakeId = (int) $request->param('intake');
        $intake = Intake::withCourse($intakeId);
        if (!$intake) {
            $this->abort(404, 'Intake not found.');
            return;
        }
        if (!in_array($intake['status'], ['scheduled', 'open'], true)) {
            $this->flash('error', 'This intake is no longer open for enrolment.');
            $this->redirect('/courses');
            return;
        }
        if ($intake['capacity'] && $intake['seats_taken'] >= $intake['capacity']) {
            $this->flash('error', 'This intake is fully booked. Please check other available dates.');
            $this->redirect('/courses');
            return;
        }

        $userId = (int) Auth::id();
        $existing = Enrollment::existing($userId, $intakeId);

        if ($existing && in_array($existing['status'], ['active', 'completed'], true)) {
            $this->flash('info', 'You are already enrolled in this intake.');
            $this->redirect('/learner/courses');
            return;
        }

        if ($existing) {
            $enrollmentId = (int) $existing['id'];
        } else {
            $enrollmentId = Enrollment::insert([
                'user_id' => $userId,
                'intake_id' => $intakeId,
                'source' => 'self',
                'status' => 'pending_payment',
            ]);
        }

        // Reuse the open invoice for this enrolment if one already exists.
        $invoice = Invoice::forEnrollment($enrollmentId);
        $invoiceId = $invoice
            ? (int) $invoice['id']
            : Invoice::createForEnrollment($enrollmentId, $userId, (float) ($intake['price_amount'] ?? 0), (string) ($intake['price_currency'] ?? 'UGX'));

        $this->redirect('/learner/pay/' . $invoiceId);
    }
}
