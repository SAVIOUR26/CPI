<?php

namespace App\Core;

/**
 * Africa's Talking SMS via plain cURL — no SDK required.
 */
class Sms
{
    public static function send(string $phone, string $message): bool
    {
        $username = Env::get('AT_USERNAME');
        $apiKey = Env::get('AT_API_KEY');
        $senderId = Env::get('AT_SENDER_ID', '');

        if (!$username || !$apiKey) {
            self::log($phone, $message, false, 'SMS gateway not configured.');
            return false;
        }

        $endpoint = Env::get('AT_ENV') === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        $payload = [
            'username' => $username,
            'to' => $phone,
            'message' => $message,
        ];
        if ($senderId) {
            $payload['from'] = $senderId;
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => [
                'apiKey: ' . $apiKey,
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $ok = $response !== false && $httpCode >= 200 && $httpCode < 300;
        self::log($phone, $message, $ok, $ok ? null : ($error ?: "HTTP $httpCode: $response"));
        return $ok;
    }

    private static function log(string $recipient, string $message, bool $ok, ?string $error): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO notifications_log (user_id, channel, event, recipient, subject, status, error, created_at)
                 VALUES (?, "sms", "generic", ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([Auth::id(), $recipient, mb_substr($message, 0, 60), $ok ? 'sent' : 'failed', $error]);
        } catch (\Throwable $e) {
            error_log('Sms log failure: ' . $e->getMessage());
        }
    }
}
