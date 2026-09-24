<?php
/** @var array $pending */ /** @var array $reviewed */
use App\Models\Payment;
use App\Support\Institute;

$numbers = implode(' or ', array_map(fn ($m) => $m[1] . ' (' . $m[0] . ')', Institute::MOBILE_MONEY));
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Clients</p>
    <h1>Payments</h1>
    <p>Students send Mobile Money to <?= e($numbers) ?> and upload a screenshot. Check that the money has arrived, then approve it.
      A student's class opens once their fee is fully paid, and they are emailed either way.</p>
  </div>
</div>

<h2 class="section-title"><i class="fa-solid fa-hourglass-half"></i> Waiting for approval <span class="count-pill"><?= count($pending) ?></span></h2>
<div class="review-queue">
  <?php foreach ($pending as $p): ?>
    <?php
    $proof = $p['proof_path'] ? '/admin/payments/' . (int) $p['id'] . '/proof' : null;
    $isImage = $proof && preg_match('/\.(jpe?g|png|webp)$/i', (string) $p['proof_path']);
    $balance = $p['invoice_total'] !== null ? max(0, (float) $p['invoice_total'] - (float) $p['invoice_paid']) : null;
    ?>
    <article class="review-card" id="payment-<?= (int) $p['id'] ?>">
      <?php if ($proof): ?>
        <a class="review-shot" href="<?= e($proof) ?>" target="_blank" rel="noopener" title="Open the screenshot full size">
          <?php if ($isImage): ?><img src="<?= e($proof) ?>" alt="Payment screenshot from <?= e($p['full_name'] ?? 'the student') ?>" loading="lazy">
          <?php else: ?><span><i class="fa-regular fa-file-pdf"></i>Open the PDF</span><?php endif; ?>
        </a>
      <?php else: ?>
        <div class="review-shot"><span><i class="fa-regular fa-image"></i>No screenshot</span></div>
      <?php endif; ?>

      <div>
        <h3><?= e($p['full_name'] ?? 'Unknown student') ?></h3>
        <span class="cell-sub"><?= e($p['email'] ?? '') ?><?= $p['user_phone'] ? ' · ' . e($p['user_phone']) : '' ?></span>
        <p class="review-amount"><?= money($p['amount'], $p['currency']) ?></p>
        <dl class="review-facts">
          <dt>For</dt><dd><?= e($p['course_title'] ? $p['course_title'] . ' (' . $p['intake_code'] . ')' : 'CPI fees') ?><?= $p['invoice_number'] ? ' · ' . e($p['invoice_number']) : '' ?></dd>
          <?php if ($balance !== null): ?><dt>Balance</dt><dd><?= money($balance, $p['currency']) ?> of <?= money($p['invoice_total'], $p['currency']) ?></dd><?php endif; ?>
          <dt>Sent from</dt><dd><?= e($p['payer_phone'] ?: '—') ?></dd>
          <dt>Transaction ID</dt><dd><?= e($p['provider_ref'] ?: '—') ?></dd>
          <dt>Uploaded</dt><dd><?= date_pretty($p['created_at'], 'j M Y, H:i') ?> · <?= e(Payment::methodLabel($p['method'])) ?></dd>
          <?php if ($proof): ?><dt>Screenshot</dt><dd><a href="<?= e($proof) ?>" target="_blank" rel="noopener">Open full size <i class="fa-solid fa-arrow-up-right-from-square"></i></a></dd><?php endif; ?>
        </dl>
      </div>

      <div class="review-actions">
        <form method="post" action="/admin/payments/<?= (int) $p['id'] ?>/confirm">
          <?= csrf_field() ?>
          <label for="amount-<?= (int) $p['id'] ?>">Amount received (<?= e($p['currency']) ?>)</label>
          <input id="amount-<?= (int) $p['id'] ?>" type="number" name="amount" min="1" step="any" inputmode="numeric" value="<?= e((string) (0 + (float) $p['amount'])) ?>" required>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
        </form>
        <form method="post" action="/admin/payments/<?= (int) $p['id'] ?>/reject" data-confirm-submit="Decline this payment and email <?= e($p['full_name'] ?? 'the student') ?>?">
          <?= csrf_field() ?>
          <label for="reason-<?= (int) $p['id'] ?>">Reason (sent to the student)</label>
          <input id="reason-<?= (int) $p['id'] ?>" type="text" name="reason" maxlength="255" placeholder="e.g. No payment arrived from this number">
          <button type="submit" class="btn btn-outline btn-sm"><i class="fa-solid fa-xmark"></i> Decline</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$pending): ?>
    <div class="card empty-state"><i class="fa-solid fa-circle-check"></i>Nothing waiting for approval.</div>
  <?php endif; ?>
</div>

<h2 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent decisions</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Student</th><th>For</th><th>Amount</th><th>Paid by</th><th>Status</th><th>Reviewed</th></tr></thead>
    <tbody>
    <?php foreach ($reviewed as $p): ?>
      <?php [$label, $class] = Payment::statusLabel($p['status']); ?>
      <tr>
        <td><strong><?= e($p['full_name'] ?? '—') ?></strong><span class="cell-sub"><?= e($p['email'] ?? '') ?></span></td>
        <td><?= e($p['course_title'] ?: 'CPI fees') ?><span class="cell-sub"><?= e(trim(($p['intake_code'] ? $p['intake_code'] . ' · ' : '') . ($p['invoice_number'] ?? ''))) ?></span></td>
        <td class="nowrap"><strong><?= money($p['amount'], $p['currency']) ?></strong></td>
        <td><?= e(Payment::methodLabel($p['method'])) ?><span class="cell-sub"><?= e(trim(($p['payer_phone'] ?: '') . ($p['provider_ref'] ? ' · ' . $p['provider_ref'] : ''), ' ·')) ?></span></td>
        <td><span class="status status-<?= e($class) ?>"><?= e($label) ?></span><?php if ($p['review_note']): ?><span class="cell-sub"><?= e($p['review_note']) ?></span><?php endif; ?></td>
        <td class="nowrap"><?= $p['confirmed_at'] ? date_pretty($p['confirmed_at'], 'j M Y') : '—' ?><span class="cell-sub"><?= e($p['reviewer_name'] ?? '') ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reviewed): ?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-receipt"></i>No payments decided yet.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
