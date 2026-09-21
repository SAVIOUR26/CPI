<?php

namespace App\Support;

use App\Core\Env;

/**
 * Thin wrapper around the Flutterwave Standard v3 API via cURL — no SDK
 * dependency. Docs: https://developer.flutterwave.com/docs/collecting-payments/standard
 */
class Flutterwave
{
    private static function secretKey(): string
    {
        return (string) Env::get('FLW_SECRET_KEY', '');
    }

    /**
     * @param array{tx_ref:string, amount:float|int, currency:string, redirect_url:string,
     *              customer:array{email:string,name:string,phonenumber?:string}, title?:string} $payload
     * @return array{ok:bool, link?:string, error?:string}
     */
    public static function initializePayment(array $payload): array
    {
        $body = [
            'tx_ref' => $payload['tx_ref'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'redirect_url' => $payload['redirect_url'],
            'customer' => $payload['customer'],
            'customizations' => [
                'title' => $payload['title'] ?? 'Crawford Professionals Institute',
                'description' => 'Course / programme payment',
            ],
        ];

        $response = self::request('POST', 'https://api.flutterwave.com/v3/payments', $body);

        if (($response['status'] ?? null) === 'success' && !empty($response['data']['link'])) {
            return ['ok' => true, 'link' => $response['data']['link']];
        }

        return ['ok' => false, 'error' => $response['message'] ?? 'Could not initialize payment.'];
    }

    /**
     * @return array{ok:bool, status?:string, amount?:float, currency?:string, tx_ref?:string, error?:string}
     */
    public static function verifyTransaction(string $transactionId): array
    {
        $response = self::request('GET', "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

        if (($response['status'] ?? null) === 'success' && isset($response['data'])) {
            $data = $response['data'];
            return [
                'ok' => true,
                'status' => $data['status'] ?? 'failed',
                'amount' => (float) ($data['amount'] ?? 0),
                'currency' => $data['currency'] ?? 'UGX',
                'tx_ref' => $data['tx_ref'] ?? '',
                'flw_ref' => $data['flw_ref'] ?? '',
            ];
        }

        return ['ok' => false, 'error' => $response['message'] ?? 'Verification failed.'];
    }

    public static function verifyWebhookSignature(?string $signatureHeader): bool
    {
        $expected = (string) Env::get('FLW_SECRET_HASH', '');
        return $expected !== '' && $signatureHeader !== null && hash_equals($expected, $signatureHeader);
    }

    private static function request(string $method, string $url, array $body = []): array
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::secretKey(),
                'Content-Type: application/json',
            ],
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['status' => 'error', 'message' => $error ?: 'Network error contacting Flutterwave.'];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Invalid response from Flutterwave.'];
    }
}
