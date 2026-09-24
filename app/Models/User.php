<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return static::first('email', $email);
    }

    /** @return string[] role slugs */
    public static function roles(int $userId): array
    {
        $rows = static::query(
            'SELECT r.slug FROM roles r JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = ?',
            [$userId]
        );
        return array_column($rows, 'slug');
    }

    /** @return string[] permission slugs, from all of the user's roles */
    public static function permissions(int $userId): array
    {
        $rows = static::query(
            'SELECT DISTINCT p.slug FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN role_user ru ON ru.role_id = rp.role_id
             WHERE ru.user_id = ?',
            [$userId]
        );
        return array_column($rows, 'slug');
    }

    /** Everyone with the lecturer role, for pick-lists. */
    public static function lecturers(): array
    {
        return static::query(
            "SELECT u.id, u.full_name FROM users u JOIN role_user ru ON ru.user_id = u.id JOIN roles r ON r.id = ru.role_id
             WHERE r.slug = 'lecturer' ORDER BY u.full_name"
        );
    }

    public static function assignRole(int $userId, string $roleSlug): void
    {
        $role = static::query('SELECT id FROM roles WHERE slug = ?', [$roleSlug]);
        if (!$role) {
            return;
        }
        static::statement(
            'INSERT IGNORE INTO role_user (role_id, user_id) VALUES (?, ?)',
            [$role[0]['id'], $userId]
        );
    }

    public static function removeRole(int $userId, string $roleSlug): void
    {
        static::statement(
            'DELETE ru FROM role_user ru JOIN roles r ON r.id = ru.role_id WHERE r.slug = ? AND ru.user_id = ?',
            [$roleSlug, $userId]
        );
    }
}
