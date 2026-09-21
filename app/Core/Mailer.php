<?php

namespace App\Core;

/**
 * Minimal SMTP client (no Composer dependency) with STARTTLS + AUTH LOGIN
 * support, falling back to PHP's mail() when SMTP is not configured.
 * Good enough for transactional email on shared/cPanel hosting.
 */
class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        $fromEmail = Env::get('MAIL_FROM_ADDRESS', 'no-reply@crawfordinstitute.online');
        $fromName = Env::get('MAIL_FROM_NAME', 'Crawford Professionals Institute');
        $textBody ??= trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody)));

        $ok = false;
        $error = null;

        try {
            if (Env::get('MAIL_HOST')) {
                $ok = self::sendViaSmtp($fromEmail, $fromName, $toEmail, $toName, $subject, $htmlBody, $textBody);
            } else {
                $ok = self::sendViaPhpMail($fromEmail, $fromName, $toEmail, $subject, $htmlBody);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $ok = false;
        }

        self::log($toEmail, $subject, $ok, $error);
        return $ok;
    }

    private static function sendViaPhpMail(string $fromEmail, string $fromName, string $toEmail, string $subject, string $htmlBody): bool
    {
        $boundary = uniqid('cpi_');
        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($toEmail, $subject, $htmlBody, $headers);
    }

    private static function sendViaSmtp(
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody
    ): bool {
        $host = Env::get('MAIL_HOST');
        $port = (int) Env::get('MAIL_PORT', 587);
        $username = Env::get('MAIL_USERNAME');
        $password = Env::get('MAIL_PASSWORD');
        $encryption = strtolower((string) Env::get('MAIL_ENCRYPTION', 'tls')); // tls | ssl | none

        $transport = $encryption === 'ssl' ? "ssl://$host" : $host;
        $fp = @fsockopen($transport, $port, $errno, $errstr, 15);
        if (!$fp) {
            throw new \RuntimeException("SMTP connection failed: $errstr ($errno)");
        }
        stream_set_timeout($fp, 15);

        $read = function () use ($fp) {
            $data = '';
            while ($line = fgets($fp, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $write = function (string $cmd) use ($fp) {
            fwrite($fp, $cmd . "\r\n");
        };
        $expect = function (string $code) use ($read) {
            $resp = $read();
            if (!str_starts_with($resp, $code)) {
                throw new \RuntimeException("Unexpected SMTP response: $resp");
            }
            return $resp;
        };

        $expect('220');
        $write('EHLO crawfordinstitute.online');
        $expect('250');

        if ($encryption === 'tls') {
            $write('STARTTLS');
            $expect('220');
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('STARTTLS negotiation failed.');
            }
            $write('EHLO crawfordinstitute.online');
            $expect('250');
        }

        if ($username && $password) {
            $write('AUTH LOGIN');
            $expect('334');
            $write(base64_encode($username));
            $expect('334');
            $write(base64_encode($password));
            $expect('235');
        }

        $write("MAIL FROM:<$fromEmail>");
        $expect('250');
        $write("RCPT TO:<$toEmail>");
        $expect('250');
        $write('DATA');
        $expect('354');

        $boundary = uniqid('cpi_');
        $headers = [
            'From: ' . self::encodeHeader($fromName) . " <$fromEmail>",
            'To: ' . self::encodeHeader($toName) . " <$toEmail>",
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
            'Date: ' . date('r'),
        ];

        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$textBody\r\n";
        $body .= "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$htmlBody\r\n";
        $body .= "--$boundary--\r\n";

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $message = str_replace("\r\n.\r\n", "\r\n..\r\n", $message); // dot-stuffing safety

        $write($message . "\r\n.");
        $expect('250');
        $write('QUIT');
        fclose($fp);

        return true;
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private static function log(string $recipient, string $subject, bool $ok, ?string $error): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO notifications_log (user_id, channel, event, recipient, subject, status, error, created_at)
                 VALUES (?, "email", "generic", ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([Auth::id(), $recipient, $subject, $ok ? 'sent' : 'failed', $error]);
        } catch (\Throwable $e) {
            error_log('Mailer log failure: ' . $e->getMessage());
        }
    }
}
