<?php

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected static string $table = 'invoices';

    public static function generateNumber(): string
    {
        $year = date('Y');
        $count = static::count('invoice_number LIKE ?', ["CPI-INV-$year-%"]) + 1;
        return sprintf('CPI-INV-%s-%05d', $year, $count);
    }

    /** A student's invoices (not void) with what each is for and how much is waiting for approval. */
    public static function forUser(int $userId): array
    {
        return static::query(
            "SELECT i.*, c.title AS course_title, it.code AS intake_code, c.programme_type,
                    (SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE p.invoice_id = i.id AND p.status = 'pending_review') AS pending_amount
             FROM invoices i
             LEFT JOIN enrollments e ON i.billable_type = 'enrollment' AND e.id = i.billable_id
             LEFT JOIN intakes it ON it.id = e.intake_id
             LEFT JOIN courses c ON c.id = it.course_id
             WHERE i.user_id = ? AND i.status <> 'void'
             ORDER BY i.status = 'paid', i.created_at DESC, i.id DESC",
            [$userId]
        );
    }

    /** The open (not void) invoice for an enrolment, if there is one. */
    public static function forEnrollment(int $enrollmentId): ?array
    {
        return static::query(
            "SELECT * FROM invoices WHERE billable_type = 'enrollment' AND billable_id = ? AND status <> 'void' ORDER BY id LIMIT 1",
            [$enrollmentId]
        )[0] ?? null;
    }

    /** enrolment id => its open invoice, for all of a student's enrolments. */
    public static function byEnrollment(int $userId): array
    {
        $map = [];
        foreach (static::query("SELECT * FROM invoices WHERE user_id = ? AND billable_type = 'enrollment' AND status <> 'void' ORDER BY id", [$userId]) as $invoice) {
            $map[(int) $invoice['billable_id']] ??= $invoice;
        }
        return $map;
    }

    public static function createForEnrollment(int $enrollmentId, int $userId, float $amount, string $currency = 'UGX'): int
    {
        return static::insert([
            'invoice_number' => static::generateNumber(),
            'billable_type' => 'enrollment',
            'billable_id' => $enrollmentId,
            'user_id' => $userId,
            'amount_total' => $amount,
            'currency' => $currency ?: 'UGX',
            'status' => 'unpaid',
        ]);
    }

    /** What an invoice is for, in words: "Course title (INTAKE)". */
    public static function describe(array $invoice): string
    {
        if ($invoice['billable_type'] === 'enrollment') {
            $row = static::query(
                'SELECT c.title, it.code FROM enrollments e JOIN intakes it ON it.id = e.intake_id JOIN courses c ON c.id = it.course_id WHERE e.id = ?',
                [(int) $invoice['billable_id']]
            )[0] ?? null;
            if ($row) {
                return $row['title'] . ' (' . $row['code'] . ')';
            }
        }
        return $invoice['billable_type'] === 'corporate_request' ? 'Corporate training' : 'CPI fees';
    }

    public static function balance(array $invoice): float
    {
        return max(0, round((float) $invoice['amount_total'] - (float) $invoice['amount_paid'], 2));
    }

    public static function recalculate(int $invoiceId): void
    {
        $rows = static::query(
            "SELECT COALESCE(SUM(amount),0) paid FROM payments WHERE invoice_id = ? AND status = 'successful'",
            [$invoiceId]
        );
        $paid = (float) $rows[0]['paid'];
        $invoice = static::findOrFail($invoiceId);
        $status = 'unpaid';
        if ($paid >= (float) $invoice['amount_total']) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        }
        static::update($invoiceId, ['amount_paid' => $paid, 'status' => $status]);
    }
}
