<?php

namespace App\Controllers\Learner;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Session;
use App\Core\Sms;
use App\Core\Upload;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;

/**
 * Students pay by Mobile Money to CPI's numbers (App\Support\Institute), then
 * upload a screenshot of the confirmation here. Finance approves it in
 * Admin → Payments, which settles the invoice and opens the class.
 */
class PaymentController extends Controller
{
    /** Screenshot or PDF of the Mobile Money confirmation: extension => accepted content types. */
    private const PROOF_TYPES = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf', 'application/x-pdf'],
    ];
    private const PROOF_MAX_BYTES = 8 * 1024 * 1024;

    public function show(Request $request): void
    {
        $user = $this->requireAuth();
        $invoice = $this->ownInvoice($request, (int) $user['id']);

        $this->view('learner.pay', [
            'pageTitle' => 'Pay ' . $invoice['invoice_number'] . ' — CPI',
            'invoice' => $invoice,
            'payments' => Payment::forInvoice((int) $invoice['id']),
            'phone' => (string) ($user['phone'] ?? ''),
            'maxBytes' => Upload::maxBytes(self::PROOF_MAX_BYTES),
        ], 'layouts.dashboard');
    }

    public function submitMobileMoney(Request $request): void
    {
        $user = $this->requireAuth();
        // Over post_max_size PHP drops the whole form (CSRF token included), so explain instead of failing the session check.
        if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->flash('error', 'That screenshot is too large to send. Please send an image smaller than '
                . Upload::humanSize(Upload::maxBytes(self::PROOF_MAX_BYTES)) . '.');
            $this->redirect('/learner/pay/' . (int) $request->param('invoice'));
        }
        $this->verifyCsrf($request);
        $invoice = $this->ownInvoice($request, (int) $user['id']);
        $back = '/learner/pay/' . (int) $invoice['id'];

        if (in_array($invoice['status'], ['paid', 'void'], true)) {
            $this->flash('info', 'This invoice has nothing left to pay.');
            $this->redirect('/learner/fees');
        }

        $balance = Invoice::balance($invoice);
        $pending = array_sum(array_map(fn ($p) => $p['status'] === 'pending_review' ? (float) $p['amount'] : 0.0, Payment::forInvoice((int) $invoice['id'])));
        $outstanding = max(0, round($balance - $pending, 2));
        $rawAmount = str_replace([',', ' '], '', (string) $request->input('amount'));
        $amount = is_numeric($rawAmount) ? round((float) $rawAmount, 2) : 0.0;
        $phone = trim((string) $request->input('payer_phone'));
        $reference = trim((string) $request->input('transaction_id'));

        $errors = [];
        if ($amount <= 0) {
            $errors['amount'] = ['Enter the amount you sent.'];
        } elseif ($amount > $outstanding) {
            $errors['amount'] = [$pending > 0
                ? money($pending, $invoice['currency']) . ' is already waiting for approval, so at most ' . money($outstanding, $invoice['currency']) . ' is left to pay.'
                : 'That is more than the balance of ' . money($balance, $invoice['currency']) . '. Enter the amount you sent for this invoice.'];
        }
        if (strlen((string) preg_replace('/\D/', '', $phone)) < 9 || strlen($phone) > 40) {
            $errors['payer_phone'] = ['Enter the phone number you sent the money from.'];
        }
        if (strlen($reference) > 100) {
            $errors['transaction_id'] = ['The transaction ID is too long.'];
        }
        $file = $request->file('screenshot');
        if (!$file) {
            $errors['screenshot'] = ['Attach the screenshot of your Mobile Money confirmation.'];
        } elseif ($problem = $this->proofProblem($file)) {
            $errors['screenshot'] = [$problem];
        }
        if ($errors) {
            $this->failBack($back, $request, $errors);
        }

        try {
            $path = Upload::store($file, 'payment-proofs', Upload::maxBytes(self::PROOF_MAX_BYTES));
        } catch (\RuntimeException $e) {
            $this->failBack($back, $request, ['screenshot' => [$e->getMessage()]]);
        }

        $paymentId = Payment::insert([
            'invoice_id' => $invoice['id'],
            'user_id' => $user['id'],
            'method' => 'mobile_money',
            'provider_ref' => $reference !== '' ? $reference : null,
            'payer_phone' => $phone,
            'amount' => $amount,
            'currency' => $invoice['currency'],
            'status' => 'pending_review',
            'proof_path' => $path,
        ]);
        AuditLog::record('payment.submit', 'payment', $paymentId, ['invoice' => $invoice['invoice_number'], 'amount' => $amount]);

        $item = Invoice::describe($invoice);
        Mailer::send(
            Setting::get('support_email', 'info@crawfordinstitute.online'),
            'CPI Finance',
            'Mobile Money payment to approve: ' . money($amount, $invoice['currency']) . ' from ' . $user['full_name'],
            '<p>' . e($user['full_name']) . ' (' . e($user['email']) . ') uploaded a Mobile Money payment for approval.</p>'
            . '<p><strong>Amount:</strong> ' . e(money($amount, $invoice['currency'])) . '<br><strong>Sent from:</strong> ' . e($phone)
            . ($reference !== '' ? '<br><strong>Transaction ID:</strong> ' . e($reference) : '')
            . '<br><strong>For:</strong> ' . e($item) . ' — invoice ' . e($invoice['invoice_number']) . '</p>'
            . '<p>Check that the money has arrived, then approve or decline it: <a href="' . e(url('/admin/payments')) . '">' . e(url('/admin/payments')) . '</a></p>'
        );

        $this->flash('success', 'Thank you — your payment of ' . money($amount, $invoice['currency'])
            . ' was sent to the Finance office for approval. We will email you when it is approved.');
        $this->redirect('/learner/fees');
    }

    /**
     * Settles the invoice from approved payments; a fully paid enrolment invoice opens the class
     * and emails the student. Returns true when this opened the class.
     */
    public static function fulfil(int $invoiceId): bool
    {
        Invoice::recalculate($invoiceId);
        $invoice = Invoice::find($invoiceId);
        if (!$invoice || $invoice['status'] !== 'paid') {
            return false;
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
                        . '<p>Your payment has been approved and your enrolment in <strong>' . e($intake['course_title'] ?? '') . '</strong> ('
                        . e($intake['code'] ?? '') . ') is now confirmed.</p>'
                        . '<p>You can open your class from the Student Portal: <a href="' . e(url('/learner/courses')) . '">' . e(url('/learner/courses')) . '</a></p>'
                    );
                    if (!empty($user['phone'])) {
                        Sms::send($user['phone'], 'CPI: Payment approved. Your enrolment in ' . ($intake['course_title'] ?? 'your course') . ' is confirmed.');
                    }
                }

                AuditLog::record('enrollment.activate', 'enrollment', $enrollment['id']);
                return true;
            }
        }
        return false;
    }

    /** The signed-in student's own invoice, or 404. */
    private function ownInvoice(Request $request, int $userId): array
    {
        $invoice = Invoice::find((int) $request->param('invoice'));
        if (!$invoice || (int) $invoice['user_id'] !== $userId) {
            $this->abort(404, 'Invoice not found.');
        }
        return $invoice;
    }

    /** Why an uploaded proof can't be accepted, or null if it's fine. */
    private function proofProblem(array $file): ?string
    {
        $max = Upload::maxBytes(self::PROOF_MAX_BYTES);
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || ($file['error'] === UPLOAD_ERR_OK && $file['size'] > $max)) {
            return 'The screenshot is too large. Send an image smaller than ' . Upload::humanSize($max) . '.';
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'The screenshot did not upload. Please try again.';
        }
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!isset(self::PROOF_TYPES[$ext])) {
            return 'Upload the screenshot as a JPG, PNG or WEBP image, or a PDF.';
        }
        // The extension decides how the file is served to Finance, so the content must match it.
        if (class_exists(\finfo::class) && is_uploaded_file((string) $file['tmp_name'])) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
            if ($mime !== false && !in_array($mime, self::PROOF_TYPES[$ext], true)) {
                return 'That file does not look like a valid ' . strtoupper($ext) . ' image or document.';
            }
        }
        return null;
    }

    private function failBack(string $to, Request $request, array $errors): void
    {
        Session::flashInput($request->all());
        Session::flash('errors', $errors);
        $this->flash('error', 'Please check the payment details below.');
        $this->redirect($to . '#upload');
    }
}
