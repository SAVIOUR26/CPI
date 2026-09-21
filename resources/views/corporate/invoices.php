<?php $sidebar = 'partials.sidebar-corporate'; ?>
<div class="dash-header"><h1>Invoices</h1></div>
<div class="table-card">
  <table>
    <thead><tr><th>Invoice #</th><th>Total</th><th>Paid</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($invoices as $inv): ?>
      <tr>
        <td><?= e($inv['invoice_number']) ?></td>
        <td><?= money($inv['amount_total'], $inv['currency']) ?></td>
        <td><?= money($inv['amount_paid'], $inv['currency']) ?></td>
        <td><span class="status status-<?= e($inv['status']) ?>"><?= e(str_replace('_',' ',$inv['status'])) ?></span></td>
        <td><?= date_pretty($inv['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$invoices): ?><tr><td colspan="5">No invoices yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
