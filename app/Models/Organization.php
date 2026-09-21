<?php

namespace App\Models;

use App\Core\Model;

class Organization extends Model
{
    protected static string $table = 'organizations';

    public static function forContact(int $userId): array
    {
        return static::query(
            'SELECT o.* FROM organizations o
             LEFT JOIN organization_members om ON om.organization_id = o.id
             WHERE o.contact_user_id = ? OR om.user_id = ?
             GROUP BY o.id',
            [$userId, $userId]
        );
    }
}
