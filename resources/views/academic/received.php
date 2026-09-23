<?php /** @var array $receipt */ ?>
<section class="section receipt-section">
  <div class="container" style="max-width:820px">
    <div class="card receipt">
      <div class="receipt-head">
        <img src="<?= asset('img/logo.png') ?>" alt="CPI crest" width="64" height="64">
        <div>
          <strong>Crawford Professionals Institute (CPI)</strong>
          <small>Online Application for Admission</small>
        </div>
      </div>
      <div class="receipt-body">
        <div class="result-icon receipt-ok"><i class="fa-solid fa-check"></i></div>
        <h1>Application submitted successfully</h1>
        <p class="muted">Your application has been received by Crawford Professionals Institute. A confirmation has been sent to
          <strong><?= e($receipt['email']) ?></strong>.</p>
        <div class="application-no">
          <small>Your application number</small>
          <strong><?= e($receipt['application_no']) ?></strong>
        </div>
        <p class="help-text">Please keep your application number for future reference.</p>
        <ul class="detail-list">
          <li><span>Applicant</span><strong><?= e($receipt['name']) ?></strong></li>
          <li><span>Programme</span><strong><?= e($receipt['programme']) ?></strong></li>
          <?php if (!empty($receipt['awarding_body'])): ?><li><span>Awarding body</span><strong><?= e($receipt['awarding_body']) ?></strong></li><?php endif; ?>
          <?php if (!empty($receipt['intake'])): ?><li><span>Preferred intake</span><strong><?= e($receipt['intake']) ?></strong></li><?php endif; ?>
          <?php if (!empty($receipt['study_session'])): ?><li><span>Study session</span><strong><?= e($receipt['study_session']) ?></strong></li><?php endif; ?>
          <li><span>Documents attached</span><strong><?= (int) $receipt['file_count'] ?></strong></li>
          <li><span>Submitted</span><strong><?= date_pretty($receipt['submitted_at'], 'j F Y, H:i') ?></strong></li>
        </ul>
        <div class="receipt-next">
          <h3>What happens next</h3>
          <ol class="mini-steps">
            <li>Our Admissions team reviews your application against the programme's admission requirements.</li>
            <li>We contact you by email or phone with the outcome.</li>
            <li>Successful applicants receive admission and registration information, including access to the Student Portal.</li>
          </ol>
          <p class="help-text">Keep your original documents — you may be asked to present them for verification.</p>
        </div>
        <div class="hero-actions no-print" style="justify-content:center;margin-top:26px">
          <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / save application</button>
          <a href="/academic" class="btn btn-outline">Back to academic programmes</a>
        </div>
      </div>
    </div>
  </div>
</section>
