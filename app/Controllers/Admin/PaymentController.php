<?php

namespace App\Controllers\Admin;

use App\Controllers\Learner\PaymentController as LearnerPaymentController;
use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Sms;
use App\Core\Upload;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Institute;

/** Finance approves or declines the Mobile Money payments students upload. */
class PaymentController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('payments.manage');
        $this->view('admin.payments.index', [
            'pageTitle' => 'Payments — CPI Admin',
            'pending' => Payment::pendingReview(),
            'reviewed' => Payment::reviewed(),
        ], 'layouts.dashboard');
    }

    /** The student's screenshot (or PDF) of the payment. */
    public function proof(Request $request): void
    {
        $this->requirePermission('payments.manage');
        $payment = Payment::find((int) $request->param('payment'));
        $path = $payment['proof_path'] ?? null;
        if (!$path || str_contains($path, '..') || !Upload::exists($path)) {
            $this->abort(404, 'Screenshot not found.');
        }
        $file = Upload::absolutePath($path);
        header('Content-Type: ' . Upload::mimeFor($path));
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: inline; filename="payment-' . (int) $payment['id'] . '.' . strtolower(pathinfo($path, PATHINFO_EXTENSION)) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($file);
        exit;
    }

    public function confirm(Request $request): void
    {
        $user = $this->requirePermissionAndUser('payments.manage');
        $this->verifyCsrf($request);
        $payment = $this->pendingPayment($request);

        // Finance records what actually arrived, which can be less than the student entered.
        $raw = str_replace([',', ' '], '', (string) $request->input('amount', (string) $payment['amount']));
        if (!is_numeric($raw) || (float) $raw <= 0) {
            $this->flash('error', 'Enter the amount that arrived, then approve.');
            $this->redirect('/admin/payments#payment-' . (int) $payment['id']);
        }
        $amount = round((float) $raw, 2);
        $claimed = (float) $payment['amount'];

        Payment::update((int) $payment['id'], [
            'status' => 'successful',
            'amount' => $amount,
            'confirmed_by' => $user['id'],
            'confirmed_at' => date('Y-m-d H:i:s'),
            'review_note' => abs($amount - $claimed) >= 0.01 ? 'Approved ' . money($amount, $payment['currency']) . '; the student entered ' . money($claimed, $payment['currency']) . '.' : null,
        ]);
        AuditLog::record('payment.approve', 'payment', (int) $payment['id'], ['amount' => $amount, 'claimed' => $claimed]);

        $opened = false;
        $invoice = null;
        if ($payment['invoice_id']) {
            $opened = LearnerPaymentController::fulfil((int) $payment['invoice_id']);
            $invoice = Invoice::find((int) $payment['invoice_id']);
        }
        $balance = $invoice ? Invoice::balance($invoice) : 0.0;

        // Opening the class sends its own confirmation; otherwise tell the student where they stand.
        if (!$opened && $payment['email']) {
            Mailer::send(
                $payment['email'],
                $payment['full_name'],
                'Payment approved — ' . money($amount, $payment['currency']) . ' received',
                '<p>Dear ' . e($payment['full_name']) . ',</p>'
                . '<p>We have received and approved your Mobile Money payment of <strong>' . e(money($amount, $payment['currency'])) . '</strong>'
                . ($payment['course_title'] ? ' for <strong>' . e($payment['course_title']) . '</strong>' : '') . '.</p>'
                . ($balance > 0
                    ? '<p>Balance remaining: <strong>' . e(money($balance, $payment['currency'])) . '</strong>. Pay it the same way and upload the screenshot under '
                      . 'Fees &amp; Payments in the Student Portal: <a href="' . e(url('/learner/fees')) . '">' . e(url('/learner/fees')) . '</a></p>'
                    : '<p>Your fee is now fully paid. Thank you.</p>')
                . '<p>Crawford Professionals Institute — Finance</p>'
            );
        }

        $this->flash('success', 'Payment of ' . money($amount, $payment['currency']) . ' approved'
            . ($opened ? ' — the fee is fully paid and the student\'s class is now open.' : ($invoice ? ($balance > 0 ? ' — ' . money($balance, $payment['currency']) . ' still to pay.' : ' — the fee is fully paid.') : '.')));
        $this->redirect('/admin/payments');
    }

    public function reject(Request $request): void
    {
        $user = $this->requirePermissionAndUser('payments.manage');
        $this->verifyCsrf($request);
        $payment = $this->pendingPayment($request);
        $reason = mb_substr(trim((string) $request->input('reason')), 0, 255);

        Payment::update((int) $payment['id'], [
            'status' => 'failed',
            'confirmed_by' => $user['id'],
            'confirmed_at' => date('Y-m-d H:i:s'),
            'review_note' => $reason !== '' ? $reason : null,
        ]);
        AuditLog::record('payment.decline', 'payment', (int) $payment['id'], ['reason' => $reason]);

        $amount = money($payment['amount'], $payment['currency']);
        if ($payment['email']) {
            Mailer::send(
                $payment['email'],
                $payment['full_name'],
                'Payment not approved — Crawford Professionals Institute',
                '<p>Dear ' . e($payment['full_name']) . ',</p>'
                . '<p>We could not approve the Mobile Money payment of <strong>' . e($amount) . '</strong> you uploaded on ' . e(date_pretty($payment['created_at'])) . '.</p>'
                . ($reason !== '' ? '<p><strong>Reason:</strong> ' . e($reason) . '</p>' : '')
                . '<p>Please check the details and upload the right screenshot under Fees &amp; Payments in the Student Portal '
                . '(<a href="' . e(url('/learner/fees')) . '">' . e(url('/learner/fees')) . '</a>), or contact the Finance office on '
                . e(implode(' / ', Institute::PHONES)) . '.</p>'
                . '<p>Crawford Professionals Institute — Finance</p>'
            );
        }
        if (!empty($payment['user_phone'])) {
            Sms::send($payment['user_phone'], 'CPI: Your payment of ' . $amount . ' was not approved' . ($reason !== '' ? ': ' . $reason : '') . '. See Fees & Payments in the Student Portal.');
        }

        $this->flash('success', 'Payment declined' . ($payment['email'] ? ' and ' . $payment['full_name'] . ' was emailed' . ($reason !== '' ? ' the reason' : '') : '') . '.');
        $this->redirect('/admin/payments');
    }

    /** A payment still waiting for a decision; anything already decided is left alone. */
    private function pendingPayment(Request $request): array
    {
        $payment = Payment::withDetails((int) $request->param('payment'));
        if (!$payment) {
            $this->abort(404, 'Payment not found.');
        }
        if ($payment['status'] !== 'pending_review') {
            $this->flash('error', 'That payment was already ' . ($payment['status'] === 'successful' ? 'approved' : 'declined') . '.');
            $this->redirect('/admin/payments');
        }
        return $payment;
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
