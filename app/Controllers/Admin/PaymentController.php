<?php

namespace App\Controllers\Admin;

use App\Controllers\Learner\PaymentController as LearnerPaymentController;
use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('payments.manage');
        $pending = Payment::pendingReview();
        $recent = Payment::query(
            'SELECT p.*, u.full_name FROM payments p LEFT JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 40'
        );

        $this->view('admin.payments.index', [
            'pageTitle' => 'Payments — CPI Admin',
            'pending' => $pending,
            'recent' => $recent,
        ], 'layouts.dashboard');
    }

    public function proof(Request $request): void
    {
        $this->requirePermission('payments.manage');
        $payment = Payment::find((int) $request->param('payment'));
        if (!$payment || !$payment['proof_path'] || !Upload::exists($payment['proof_path'])) {
            $this->abort(404, 'Proof not found.');
            return;
        }
        $path = Upload::absolutePath($payment['proof_path']);
        header('Content-Type: ' . Upload::mimeFor($payment['proof_path']));
        header('Content-Disposition: inline; filename="proof-' . $payment['id'] . '"');
        readfile($path);
        exit;
    }

    public function confirm(Request $request): void
    {
        $user = $this->requirePermissionAndUser('payments.manage');
        $this->verifyCsrf($request);
        $paymentId = (int) $request->param('payment');
        $payment = Payment::find($paymentId);
        if (!$payment) {
            $this->abort(404, 'Payment not found.');
            return;
        }

        Payment::update($paymentId, [
            'status' => 'successful',
            'confirmed_by' => $user['id'],
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);

        if ($payment['invoice_id']) {
            LearnerPaymentController::fulfil((int) $payment['invoice_id']);
        }

        AuditLog::record('payment.confirm', 'payment', $paymentId);
        $this->flash('success', 'Payment confirmed.');
        $this->redirect('/admin/payments');
    }

    public function reject(Request $request): void
    {
        $user = $this->requirePermissionAndUser('payments.manage');
        $this->verifyCsrf($request);
        $paymentId = (int) $request->param('payment');
        $payment = Payment::find($paymentId);
        if (!$payment) {
            $this->abort(404, 'Payment not found.');
            return;
        }

        Payment::update($paymentId, ['status' => 'failed', 'confirmed_by' => $user['id']]);
        AuditLog::record('payment.reject', 'payment', $paymentId);
        $this->flash('success', 'Payment marked as failed.');
        $this->redirect('/admin/payments');
    }

    private function requirePermissionAndUser(string $permission): array
    {
        $this->requirePermission($permission);
        return Auth::user();
    }
}
