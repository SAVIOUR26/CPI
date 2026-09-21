<?php /** @var array $invoice */ /** @var array|null $intake */ ?>
<section class="section">
  <div class="container">
    <div class="form-card wide">
      <p class="eyebrow">Invoice <?= e($invoice['invoice_number']) ?></p>
      <h1>Complete Your Payment</h1>
      <?php if ($intake): ?>
        <p><?= e($intake['course_title']) ?> — <?= e($intake['code']) ?></p>
      <?php endif; ?>

      <div class="stat-row" style="margin:20px 0">
        <div class="stat-box"><div class="num"><?= money($invoice['amount_total'], $invoice['currency']) ?></div><div class="label">Total due</div></div>
        <div class="stat-box"><div class="num"><?= money($invoice['amount_paid'], $invoice['currency']) ?></div><div class="label">Already paid</div></div>
        <div class="stat-box"><div class="num"><?= money($invoice['amount_total'] - $invoice['amount_paid'], $invoice['currency']) ?></div><div class="label">Balance</div></div>
      </div>

      <?php if ($invoice['status'] === 'paid'): ?>
        <div class="alert alert-success">This invoice is fully paid. Thank you!</div>
      <?php else: ?>

        <h3>Pay with Mobile Money or Card</h3>
        <p>Powered by Flutterwave — supports MTN Mobile Money, Airtel Money and major cards.</p>
        <form method="post" action="/learner/pay/<?= (int) $invoice['id'] ?>/flutterwave">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-block">Pay Now via Flutterwave</button>
        </form>

        <h3 style="margin-top:30px">Or Pay by Bank Transfer</h3>
        <p>Transfer the balance to our bank account, then upload your proof of payment below. Our Finance team will
          confirm it and activate your enrolment.</p>
        <form method="post" action="/learner/pay/<?= (int) $invoice['id'] ?>/bank-transfer" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Proof of payment (PDF or image)</label>
            <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
          <button type="submit" class="btn btn-outline btn-block">Submit Proof of Payment</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
