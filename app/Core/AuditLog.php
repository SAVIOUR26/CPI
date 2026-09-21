<?php

namespace App\Core;

class AuditLog
{
    public static function record(string $action, ?string $subjectType = null, int|string|null $subjectId = null, array $meta = []): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO audit_log (user_id, action, subject_type, subject_id, meta, ip_address, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                Auth::id(),
                $action,
                $subjectType,
                $subjectId,
                $meta ? json_encode($meta) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the request.
            error_log('AuditLog failure: ' . $e->getMessage());
        }
    }
}
