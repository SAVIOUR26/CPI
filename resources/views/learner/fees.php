<?php
/** @var array $invoices */ /** @var array $payments */ /** @var array $ledger */
use App\Models\Invoice;

$owing = array_values(array_filter($invoices, fn ($i) => $i['status'] !== 'paid' && (float) $i['amount_total'] > 0 && Invoice::balance($i) > 0));
$totalOwing = array_sum(array_map(fn ($i) => Invoice::balance($i), $owing));
$awaiting = array_sum(array_map(fn ($i) => (float) $i['pending_amount'], $invoices));
$currency = $invoices[0]['currency'] ?? 'UGX';
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Student portal</p>
    <h1>Fees &amp; Payments</h1>
    <p>Pay by Mobile Money, then upload a screenshot of the confirmation here. The Finance office approves it and your class opens once
      the fee is paid.</p>
  </div>
</div>

<div class="stat-row cols-3">
  <div class="stat-box"><span class="stat-icon tone-orange"><i class="fa-solid fa-scale-balanced"></i></span><div><div class="num"><?= money($totalOwing, $currency) ?></div><div class="label">Balance to pay</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-hourglass-half"></i></span><div><div class="num"><?= money($awaiting, $currency) ?></div><div class="label">Awaiting approval</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><div><div class="num"><?= money(array_sum(array_map(fn ($i) => (float) $i['amount_paid'], $invoices)), $currency) ?></div><div class="label">Paid and approved</div></div></div>
</div>

<div class="panel fees-howto">
  <div class="panel-head"><h2><i class="fa-solid fa-circle-question"></i> How to pay</h2></div>
  <div class="panel-body howto-grid">
    <?php \App\Core\View::partial('partials.portal.mobile-money', ['amount' => null]); ?>
    <ol class="pay-howto">
      <li><strong>Send the money</strong> by Mobile Money to one of these numbers.</li>
      <li><strong>Take a screenshot</strong> of the confirmation message.</li>
      <li><strong>Press Pay</strong> next to the fee below and upload the screenshot.</li>
      <li><strong>The Finance office approves it</strong> and emails you. Your class opens once the fee is fully paid.</li>
    </ol>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-file-invoice"></i> Your fees</h2></div>
  <div class="table-card table-stack">
    <table>
      <thead><tr><th>For</th><th>Fee</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($invoices as $i): ?>
        <?php
        $balance = Invoice::balance($i);
        $unset = (float) $i['amount_total'] <= 0;
        [$label, $class] = match (true) {
            $i['status'] === 'paid' => ['Paid', 'paid'],
            $unset => ['Fee to be confirmed', 'draft'],
            (float) $i['pending_amount'] > 0 => ['Awaiting approval', 'pending_review'],
            $i['status'] === 'partially_paid' => ['Part paid', 'partially_paid'],
            default => ['Unpaid', 'unpaid'],
        };
        ?>
        <tr>
          <td><strong><?= e($i['course_title'] ? $i['course_title'] : Invoice::describe($i)) ?></strong><span class="cell-sub"><?= e(trim(($i['intake_code'] ? $i['intake_code'] . ' · ' : '') . $i['invoice_number'])) ?></span></td>
          <td class="nowrap" data-label="Fee"><?= $unset ? '—' : money($i['amount_total'], $i['currency']) ?></td>
          <td class="nowrap" data-label="Paid"><?= money($i['amount_paid'], $i['currency']) ?></td>
          <td class="nowrap" data-label="Balance"><strong><?= $unset ? '—' : money($balance, $i['currency']) ?></strong></td>
          <td data-label="Status"><span class="status status-<?= e($class) ?>"><?= e($label) ?></span></td>
          <td class="cell-actions">
            <?php if ($i['status'] !== 'paid' && !$unset && $balance - (float) $i['pending_amount'] > 0): ?>
              <a href="/learner/pay/<?= (int) $i['id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-mobile-screen-button"></i> Pay</a>
            <?php else: ?>
              <a href="/learner/pay/<?= (int) $i['id'] ?>" class="btn btn-sm btn-outline">View</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$invoices): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-receipt"></i>Nothing to pay right now. Fees appear here when you enrol in a
          course or when Admissions sets your programme fee.</div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($payments): ?>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-clock-rotate-left"></i> Your payments</h2></div>
    <?php \App\Core\View::partial('partials.portal.payments-table', ['payments' => $payments]); ?>
  </div>
<?php endif; ?>

<?php if ($ledger): ?>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-building-columns"></i> Academic fee statement</h2></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Description</th><th>Programme</th><th>Due</th><th>Paid</th><th>Balance</th></tr></thead>
        <tbody>
        <?php foreach ($ledger as $f): ?>
          <tr>
            <td><?= e($f['description']) ?></td>
            <td><?= e($f['course_title']) ?> (<?= e($f['intake_code']) ?>)</td>
            <td class="nowrap"><?= money($f['amount_due']) ?></td>
            <td class="nowrap"><?= money($f['amount_paid']) ?></td>
            <td class="nowrap"><?= money($f['amount_due'] - $f['amount_paid']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
