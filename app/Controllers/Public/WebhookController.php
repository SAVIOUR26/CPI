<?php

namespace App\Controllers\Public;

use App\Controllers\Learner\PaymentController;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Payment;
use App\Support\Flutterwave;

class WebhookController extends Controller
{
    /**
     * Server-to-server confirmation from Flutterwave — the source of truth,
     * independent of whether the learner's browser made it back to our
     * redirect callback.
     */
    public function flutterwave(Request $request): void
    {
        $signature = $_SERVER['HTTP_VERIF_HASH'] ?? null;
        if (!Flutterwave::verifyWebhookSignature($signature)) {
            http_response_code(401);
            echo 'invalid signature';
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];
        $data = $payload['data'] ?? [];
        $txRef = $data['tx_ref'] ?? null;
        $transactionId = $data['id'] ?? null;

        if (!$txRef || !$transactionId) {
            http_response_code(200); // acknowledge, nothing to do
            echo 'ignored';
            return;
        }

        $payment = Payment::byReference($txRef);
        if (!$payment || $payment['status'] === 'successful') {
            http_response_code(200);
            echo 'ok';
            return;
        }

        $verification = Flutterwave::verifyTransaction((string) $transactionId);
        if ($verification['ok'] && $verification['status'] === 'successful' && $verification['tx_ref'] === $txRef) {
            Payment::update($payment['id'], [
                'status' => 'successful',
                'confirmed_at' => date('Y-m-d H:i:s'),
                'raw_payload' => json_encode($verification),
            ]);
            PaymentController::fulfil((int) $payment['invoice_id']);
        }

        http_response_code(200);
        echo 'ok';
    }
}
