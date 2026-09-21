<?php

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';

    public static function byReference(string $ref): ?array
    {
        return static::first('provider_ref', $ref);
    }

    public static function pendingReview(): array
    {
        return static::query(
            "SELECT p.*, u.full_name, u.email FROM payments p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.status = 'pending_review' ORDER BY p.created_at"
        );
    }
}
