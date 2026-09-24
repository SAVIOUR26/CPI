<?php
/**
 * How to pay: CPI's Mobile Money numbers and the registered name (App\Support\Institute).
 * @var float|null  $amount    amount to send, when known
 * @var string|null $currency
 * @var string|null $reference invoice number to quote as the reason
 */
use App\Support\Institute;

$amount = $amount ?? null;
$currency = $currency ?? 'UGX';
$reference = $reference ?? null;
?>
<div class="momo">
  <div class="momo-head">
    <span class="momo-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
    <div>
      <h3>Pay by Mobile Money</h3>
      <p><?= $amount ? 'Send <strong>' . e(money($amount, $currency)) . '</strong> to either number:' : 'Send your fees to either number:' ?></p>
    </div>
  </div>
  <ul class="momo-numbers">
    <?php foreach (Institute::MOBILE_MONEY as [$network, $number]): ?>
      <li>
        <span><small><?= e($network) ?></small><strong><?= e($number) ?></strong></span>
        <button type="button" class="btn btn-outline btn-sm" data-copy="<?= e(preg_replace('/\D/', '', $number)) ?>" aria-label="Copy the <?= e($network) ?> number"><i class="fa-regular fa-copy"></i> Copy</button>
      </li>
    <?php endforeach; ?>
  </ul>
  <p class="momo-name"><i class="fa-solid fa-user-check"></i>
    <span>Registered name: <strong><?= e(Institute::MOBILE_MONEY_NAME) ?></strong>, CPI's <?= e(Institute::MOBILE_MONEY_ROLE) ?>. Check that this name shows on your phone before you confirm.</span></p>
  <?php if ($reference): ?>
    <p class="momo-ref">If your phone asks for a reason or reference, enter <strong><?= e($reference) ?></strong>.</p>
  <?php endif; ?>
</div>
