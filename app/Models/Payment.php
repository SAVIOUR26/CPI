<?php

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';

    public const METHODS = [
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Bank transfer',
        'flutterwave' => 'Online (Flutterwave)',
        'cash' => 'Cash',
        'other' => 'Other',
    ];

    /** status => [label, status class] */
    public const STATUSES = [
        'pending_review' => ['Awaiting approval', 'pending_review'],
        'successful' => ['Approved', 'successful'],
        'failed' => ['Not approved', 'failed'],
        'initiated' => ['Not completed', 'draft'],
    ];

    /** Payments with who paid, who reviewed them and what they were for. */
    private const SELECT = 'SELECT p.*, u.full_name, u.email, u.phone AS user_phone, r.full_name AS reviewer_name,
                                   i.invoice_number, i.amount_total AS invoice_total, i.amount_paid AS invoice_paid,
                                   c.title AS course_title, it.code AS intake_code
                            FROM payments p
                            LEFT JOIN users u ON u.id = p.user_id
                            LEFT JOIN users r ON r.id = p.confirmed_by
                            LEFT JOIN invoices i ON i.id = p.invoice_id
                            LEFT JOIN enrollments e ON i.billable_type = "enrollment" AND e.id = i.billable_id
                            LEFT JOIN intakes it ON it.id = e.intake_id
                            LEFT JOIN courses c ON c.id = it.course_id';

    public static function withDetails(int $id): ?array
    {
        return static::query(self::SELECT . ' WHERE p.id = ?', [$id])[0] ?? null;
    }

    public static function forInvoice(int $invoiceId): array
    {
        return static::query(self::SELECT . ' WHERE p.invoice_id = ? AND p.status <> "initiated" ORDER BY p.created_at DESC, p.id DESC', [$invoiceId]);
    }

    public static function forUser(int $userId): array
    {
        return static::query(self::SELECT . ' WHERE p.user_id = ? AND p.status <> "initiated" ORDER BY p.created_at DESC, p.id DESC', [$userId]);
    }

    /** Waiting for Finance, oldest first. */
    public static function pendingReview(): array
    {
        return static::query(self::SELECT . ' WHERE p.status = "pending_review" ORDER BY p.created_at, p.id');
    }

    /** Already decided, newest first. */
    public static function reviewed(int $limit = 60): array
    {
        return static::query(self::SELECT . ' WHERE p.status IN ("successful", "failed") ORDER BY COALESCE(p.confirmed_at, p.created_at) DESC, p.id DESC LIMIT ' . (int) $limit);
    }

    public static function methodLabel(?string $method): string
    {
        return self::METHODS[$method] ?? ucfirst(str_replace('_', ' ', (string) $method));
    }

    /** @return array{0: string, 1: string} [label, status class] */
    public static function statusLabel(string $status): array
    {
        return self::STATUSES[$status] ?? [ucfirst(str_replace('_', ' ', $status)), $status];
    }
}
