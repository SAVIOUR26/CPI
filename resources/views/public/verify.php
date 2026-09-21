<?php /** @var array|null $certificate */ /** @var bool|null $valid */ ?>
<section class="section">
  <div class="container" style="max-width:600px">
    <p class="eyebrow">Certificate Verification</p>
    <h1>Verify a CPI Certificate</h1>
    <p>Enter the certificate code printed on the certificate (e.g. CPI-2026-000123) to confirm it was issued by
      Crawford Professionals Institute.</p>

    <form method="get" action="/verify/lookup" class="form-card" style="margin-bottom:24px">
      <div class="form-group">
        <label>Certificate code</label>
        <input type="text" name="code" value="<?= e($code ?? '') ?>" placeholder="CPI-2026-000123" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Verify</button>
    </form>

    <?php if (isset($valid)): ?>
      <?php if ($valid): ?>
        <div class="card verify-result valid">
          <h2 style="color:#1e5c2a">&#10003; Certificate Valid</h2>
          <p><strong><?= e($certificate['full_name']) ?></strong></p>
          <p><?= e($certificate['title']) ?></p>
          <p>Programme: <?= e($certificate['course_title']) ?> (<?= e($certificate['intake_code']) ?>)</p>
          <p>Issued: <?= date_pretty($certificate['issued_at']) ?></p>
          <p>Certificate code: <strong><?= e($certificate['code']) ?></strong></p>
        </div>
      <?php else: ?>
        <div class="card verify-result invalid">
          <h2 style="color:#8a1c1c">&#10007; Not Found</h2>
          <p>We could not verify a certificate with code "<?= e($code ?? '') ?>". Please check the code and try again,
            or contact CPI if you believe this is an error.</p>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
