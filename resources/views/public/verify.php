<?php /** @var array|null $certificate */ /** @var bool|null $valid */
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Certificate verification',
  'title' => 'Verify a CPI certificate',
  'lead' => 'Confirm that a certificate was genuinely issued by Crawford Professionals Institute using the code printed on it.',
  'crumbs' => [['label' => 'Verify Certificate']],
  'stats' => [['icon' => 'fa-qrcode', 'text' => 'Or scan the QR code on the certificate']],
]);
?>
<section class="section">
  <div class="container" style="max-width:720px">
    <form method="get" action="/verify/lookup" class="form-card" style="max-width:none;margin-bottom:28px">
      <div class="form-group">
        <label for="code">Certificate code</label>
        <div class="input-icon"><i class="fa-solid fa-hashtag"></i><input id="code" type="text" name="code" value="<?= e($code ?? '') ?>" placeholder="e.g. CPI-2026-000123" required></div>
        <p class="help-text">You'll find the code at the bottom of the certificate, next to the QR code.</p>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-shield-halved"></i> Verify certificate</button>
    </form>

    <?php if (isset($valid)): ?>
      <?php if ($valid): ?>
        <div class="card verify-result valid">
          <div class="result-icon"><i class="fa-solid fa-check"></i></div>
          <h2 style="color:var(--success)">Certificate is valid</h2>
          <p>This certificate was issued by Crawford Professionals Institute.</p>
          <ul class="detail-list">
            <li><span>Awarded to</span><strong><?= e($certificate['full_name']) ?></strong></li>
            <li><span>Award</span><strong><?= e($certificate['title']) ?></strong></li>
            <li><span>Programme</span><strong><?= e($certificate['course_title']) ?> (<?= e($certificate['intake_code']) ?>)</strong></li>
            <li><span>Issued</span><strong><?= date_pretty($certificate['issued_at']) ?></strong></li>
            <li><span>Certificate code</span><strong><?= e($certificate['code']) ?></strong></li>
          </ul>
        </div>
      <?php else: ?>
        <div class="card verify-result invalid">
          <div class="result-icon"><i class="fa-solid fa-xmark"></i></div>
          <h2 style="color:var(--danger)">Certificate not found</h2>
          <p style="margin:0">We could not verify a certificate with code “<?= e($code ?? '') ?>”. Please check the code and try again,
            or <a href="/contact">contact CPI</a> if you believe this is an error.</p>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
