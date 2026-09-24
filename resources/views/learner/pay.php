<?php
/** @var array $invoice */ /** @var array $payments */ /** @var string $phone */ /** @var int $maxBytes */
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Institute;

$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
$currency = $invoice['currency'];
$balance = Invoice::balance($invoice);
$pending = array_sum(array_map(fn ($p) => $p['status'] === 'pending_review' ? (float) $p['amount'] : 0.0, $payments));
$toSend = max(0, $balance - $pending);
$settled = in_array($invoice['status'], ['paid', 'void'], true) || ((float) $invoice['amount_total'] > 0 && $balance <= 0);
?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><a href="/learner/fees"><i class="fa-solid fa-arrow-left"></i> Fees &amp; Payments</a> · <?= e($invoice['invoice_number']) ?></p>
    <h1>Pay your fees</h1>
    <p><?= e(Invoice::describe($invoice)) ?></p>
  </div>
</div>

<div class="stat-row cols-3">
  <div class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-file-invoice"></i></span><div><div class="num"><?= (float) $invoice['amount_total'] > 0 ? money($invoice['amount_total'], $currency) : '—' ?></div><div class="label">Total fee</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><div><div class="num"><?= money($invoice['amount_paid'], $currency) ?></div><div class="label">Paid and approved</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-orange"><i class="fa-solid fa-scale-balanced"></i></span><div><div class="num"><?= money($balance, $currency) ?></div><div class="label">Balance<?= $pending > 0 ? ' · ' . money($pending, $currency) . ' awaiting approval' : '' ?></div></div></div>
</div>

<?php if ($invoice['status'] === 'void'): ?>
  <div class="notice"><i class="fa-solid fa-circle-info"></i><p>This invoice was cancelled, so there is nothing to pay on it.</p></div>
<?php elseif ($settled): ?>
  <div class="notice admission-notice"><i class="fa-solid fa-circle-check"></i>
    <p><strong>This fee is fully paid — thank you.</strong> Your class is open under <a href="/learner/courses">My Courses</a>.</p></div>
<?php elseif ((float) $invoice['amount_total'] <= 0): ?>
  <div class="notice"><i class="fa-solid fa-circle-info"></i>
    <p><strong>CPI will confirm the fee for this course.</strong> Once it is set it appears here with how to pay. Questions? Call or WhatsApp
      <a href="<?= e(Institute::whatsappUrl('Hello CPI, I would like to know the fee for ' . Invoice::describe($invoice) . '.')) ?>" target="_blank" rel="noopener"><?= e(Institute::WHATSAPP) ?></a>.</p></div>
<?php elseif ($toSend <= 0): ?>
  <div class="notice"><i class="fa-solid fa-hourglass-half"></i>
    <p><strong>Your payment is waiting for approval.</strong> The Finance office checks that the money has arrived, then approves it. We will
      email you, and your class opens once the fee is fully paid.</p></div>
<?php else: ?>
  <?php if ($pending > 0): ?>
    <div class="notice" style="margin-bottom:22px"><i class="fa-solid fa-hourglass-half"></i>
      <p><strong><?= e(money($pending, $currency)) ?> is waiting for approval.</strong> Only upload another screenshot if you have sent more money.</p></div>
  <?php endif; ?>
  <div class="pay-steps">
    <section class="panel pay-step">
      <div class="panel-head"><h2><span class="step-no">1</span> Send the money</h2></div>
      <div class="panel-body">
        <?php \App\Core\View::partial('partials.portal.mobile-money', ['amount' => $toSend, 'currency' => $currency, 'reference' => $invoice['invoice_number']]); ?>
        <p class="help-text">You can pay the full balance or part of it. Keep the confirmation message — you need a screenshot of it next.</p>
      </div>
    </section>

    <section class="panel pay-step" id="upload">
      <div class="panel-head"><h2><span class="step-no">2</span> Upload your screenshot</h2></div>
      <div class="panel-body">
        <form method="post" action="/learner/pay/<?= (int) $invoice['id'] ?>/mobile-money" enctype="multipart/form-data" data-upload-single>
          <?= csrf_field() ?>
          <label class="upload-box upload-box-lg">
            <i class="fa-solid fa-image"></i>
            <strong>Screenshot of the Mobile Money confirmation</strong>
            <small data-file-label>Tap to choose the image (JPG, PNG or PDF, up to <?= e(\App\Core\Upload::humanSize($maxBytes)) ?>)</small>
            <input type="file" name="screenshot" accept="image/*,.pdf" required data-max-bytes="<?= (int) $maxBytes ?>">
          </label>
          <?= $err('screenshot') ?>
          <div class="form-row" style="margin-top:18px">
            <div class="form-group">
              <label for="pay_amount">Amount you sent (<?= e($currency) ?>)</label>
              <input id="pay_amount" type="number" name="amount" inputmode="numeric" min="1" max="<?= e((string) $toSend) ?>" step="any" value="<?= e(old('amount', (string) (int) round($toSend))) ?>" required>
              <?= $err('amount') ?>
            </div>
            <div class="form-group">
              <label for="pay_phone">Number you paid from</label>
              <input id="pay_phone" type="tel" name="payer_phone" maxlength="40" value="<?= e(old('payer_phone', $phone)) ?>" placeholder="e.g. 0772 123456" required>
              <?= $err('payer_phone') ?>
            </div>
          </div>
          <div class="form-group">
            <label for="pay_ref">Transaction ID <span class="muted">(optional)</span></label>
            <input id="pay_ref" type="text" name="transaction_id" maxlength="100" value="<?= e(old('transaction_id')) ?>" placeholder="From the confirmation message, e.g. 108765432101">
            <?= $err('transaction_id') ?>
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-paper-plane"></i> Send for approval</button>
          <p class="help-text" style="text-align:center">The Finance office checks the payment and approves it. Your class opens once the fee is fully paid.</p>
        </form>
      </div>
    </section>
  </div>
<?php endif; ?>

<?php if ($payments): ?>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-clock-rotate-left"></i> Payments on this fee</h2></div>
    <?php \App\Core\View::partial('partials.portal.payments-table', ['payments' => $payments, 'showFor' => false]); ?>
  </div>
<?php endif; ?>
