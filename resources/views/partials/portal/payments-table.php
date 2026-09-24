<?php
/**
 * A student's payments and where each one stands.
 * @var array $payments rows from App\Models\Payment
 * @var bool|null $showFor show what each payment was for
 */
use App\Models\Payment;

$showFor = $showFor ?? true;
?>
<div class="table-card table-stack">
  <table>
    <thead><tr><?php if ($showFor): ?><th>For</th><?php endif; ?><th>Sent</th><th>Amount</th><th>From</th><th>Transaction ID</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
      <?php [$label, $class] = Payment::statusLabel($p['status']); ?>
      <tr>
        <?php if ($showFor): ?><td><strong><?= e($p['course_title'] ?: 'CPI fees') ?></strong><span class="cell-sub"><?= e(trim(($p['intake_code'] ? $p['intake_code'] . ' · ' : '') . ($p['invoice_number'] ?? ''))) ?></span></td><?php endif; ?>
        <td class="nowrap" data-label="Sent"><?= date_pretty($p['created_at'], 'j M Y') ?><span class="cell-sub"><?= e(Payment::methodLabel($p['method'])) ?></span></td>
        <td class="nowrap" data-label="Amount"><strong><?= money($p['amount'], $p['currency']) ?></strong></td>
        <td class="nowrap" data-label="From"><?= e($p['payer_phone'] ?: '—') ?></td>
        <td data-label="Transaction ID"><?= e($p['provider_ref'] ?: '—') ?></td>
        <td data-label="Status">
          <span class="status status-<?= e($class) ?>"><?= e($label) ?></span>
          <?php if ($p['status'] === 'successful' && $p['confirmed_at']): ?><span class="cell-sub">Approved <?= date_pretty($p['confirmed_at'], 'j M Y') ?></span><?php endif; ?>
          <?php if ($p['status'] === 'failed' && $p['review_note']): ?><span class="cell-sub review-reason"><?= e($p['review_note']) ?></span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
