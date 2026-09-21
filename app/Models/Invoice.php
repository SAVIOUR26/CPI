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
