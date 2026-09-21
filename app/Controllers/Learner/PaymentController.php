<?php

namespace App\Controllers\Learner;

use App\Core\Auth;
use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Sms;
use App\Core\Upload;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\Flutterwave;
use App\Support\Str;

class PaymentController extends Controller
{
    public function show(Request $request): void
    {
        $user = $this->requireAuth();
        $invoice = Invoice::find((int) $request->param('invoice'));

        if (!$invoice || (int) $invoice['user_id'] !== (int) $user['id']) {
            $this->abort(404, 'Invoice not found.');
            return;
        }

        $enrollment = $invoice['billable_type'] === 'enrollment' ? Enrollment::find((int) $invoice['billable_id']) : null;
        $intake = $enrollment ? Intake::withCourse((int) $enrollment['intake_id']) : null;

        $this->view('learner.pay', [
            'pageTitle' => 'Complete Payment — CPI',
            'invoice' => $invoice,
            'intake' => $intake,
        ]);
    }

    public function initiateFlutterwave(Request $request): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($request);
        $invoice = Invoice::find((int) $request->param('invoice'));

        if (!$invoice || (int) $invoice['user_id'] !== (int) $user['id']) {
            $this->abort(404, 'Invoice not found.');
            return;
        }
        if ($invoice['status'] === 'paid') {
            $this->flash('info', 'This invoice is already fully paid.');
            $this->redirect('/learner/courses');
            return;
        }

        $balance = (float) $invoice['amount_total'] - (float) $invoice['amount_paid'];
        $txRef = 'CPI-' . $invoice['id'] . '-' . Str::random(10);

        $paymentId = Payment::insert([
            'invoice_id' => $invoice['id'],
            'user_id' => $user['id'],
            'method' => 'flutterwave',
            'provider_ref' => $txRef,
            'amount' => $balance,
            'currency' => $invoice['currency'],
            'status' => 'initiated',
        ]);

        $result = Flutterwave::initializePayment([
            'tx_ref' => $txRef,
            'amount' => $balance,
            'currency' => $invoice['currency'],
            'redirect_url' => url('/learner/pay/flutterwave/callback'),
            'customer' => [
                'email' => $user['email'],
                'name' => $user['full_name'],
                'phonenumber' => $user['phone'] ?? '',
            ],
        ]);

        if (!$result['ok']) {
            Payment::update($paymentId, ['status' => 'failed']);
            $this->flash('error', 'We could not start the payment: ' . $result['error']);
            $this->redirect('/learner/pay/' . $invoice['id']);
            return;
        }

        $this->redirect($result['link']);
    }

    public function flutterwaveCallback(Request $request): void
    {
        $this->requireAuth();
        $status = $request->input('status');
        $txRef = (string) $request->input('tx_ref');
        $transactionId = (string) $request->input('transaction_id');

        $payment = Payment::byReference($txRef);
        if (!$payment) {
            $this->flash('error', 'We could not find that payment.');
            $this->redirect('/learner/courses');
            return;
        }

        if ($status !== 'successful' || !$transactionId) {
            Payment::update($payment['id'], ['status' => 'failed']);
            $this->flash('error', 'Payment was not completed. You can try again below.');
            $this->redirect('/learner/pay/' . $payment['invoice_id']);
            return;
        }

        $verification = Flutterwave::verifyTransaction($transactionId);

        if (!$verification['ok'] || $verification['status'] !== 'successful' || $verification['tx_ref'] !== $txRef) {
            Payment::update($payment['id'], ['status' => 'failed', 'raw_payload' => json_encode($verification)]);
            $this->flash('error', 'We could not verify this payment. Please contact support if you were charged.');
            $this->redirect('/learner/pay/' . $payment['invoice_id']);
            return;
        }

        Payment::update($payment['id'], [
            'status' => 'successful',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'raw_payload' => json_encode($verification),
        ]);

        $this->fulfil((int) $payment['invoice_id']);

        $this->flash('success', 'Payment received — you are now enrolled!');
        $this->redirect('/learner/courses');
    }

    /** Manual bank transfer: learner uploads proof, Finance confirms later from the admin portal. */
    public function bankTransfer(Request $request): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($request);
        $invoice = Invoice::find((int) $request->param('invoice'));

        if (!$invoice || (int) $invoice['user_id'] !== (int) $user['id']) {
            $this->abort(404, 'Invoice not found.');
            return;
        }

        $file = $request->file('proof');
        if (!$file) {
            $this->flash('error', 'Please attach your proof of payment (PDF or image).');
            $this->redirect('/learner/pay/' . $invoice['id']);
            return;
        }

        try {
            $path = Upload::store($file, 'payment-proofs');
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/learner/pay/' . $invoice['id']);
            return;
        }

        $balance = (float) $invoice['amount_total'] - (float) $invoice['amount_paid'];

        Payment::insert([
            'invoice_id' => $invoice['id'],
            'user_id' => $user['id'],
            'method' => 'bank_transfer',
            'amount' => $balance,
            'currency' => $invoice['currency'],
            'status' => 'pending_review',
            'proof_path' => $path,
        ]);

        $this->flash('success', 'Thank you — your proof of payment was submitted and is awaiting confirmation from our Finance team.');
        $this->redirect('/learner/courses');
    }

    /** Marks the underlying enrolment active, updates invoice totals, and notifies the learner. */
    public static function fulfil(int $invoiceId): void
    {
        Invoice::recalculate($invoiceId);
        $invoice = Invoice::find($invoiceId);
        if (!$invoice || $invoice['status'] !== 'paid') {
            return;
        }

        if ($invoice['billable_type'] === 'enrollment') {
            $enrollment = Enrollment::find((int) $invoice['billable_id']);
            if ($enrollment && $enrollment['status'] !== 'active') {
                Enrollment::activate((int) $enrollment['id']);
                Intake::incrementSeats((int) $enrollment['intake_id']);

                $user = User::find((int) $enrollment['user_id']);
                $intake = Intake::withCourse((int) $enrollment['intake_id']);

                if ($user) {
                    Mailer::send(
                        $user['email'],
                        $user['full_name'],
                        'Enrolment confirmed — ' . ($intake['course_title'] ?? 'CPI'),
                        '<p>Dear ' . e($user['full_name']) . ',</p>'
                        . '<p>Your payment has been received and your enrolment in <strong>' . e($intake['course_title'] ?? '') . '</strong> ('
                        . e($intake['code'] ?? '') . ') is now confirmed.</p>'
                        . '<p>You can access your course materials from your learner dashboard.</p>'
                    );
                    if (!empty($user['phone'])) {
                        Sms::send($user['phone'], 'CPI: Payment received. Your enrolment in ' . ($intake['course_title'] ?? 'your course') . ' is confirmed.');
                    }
                }

                AuditLog::record('enrollment.activate', 'enrollment', $enrollment['id']);
            }
        }
    }
}
