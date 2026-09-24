<?php
/** @var array $applications */ /** @var array $levels */
$labels = ['submitted' => 'Received', 'under_review' => 'Under review', 'admitted' => 'Admitted', 'rejected' => 'Not admitted'];
$steps = ['submitted' => 1, 'under_review' => 2, 'admitted' => 3, 'rejected' => 3];
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Student portal</p>
    <h1>My admission</h1>
    <p>Your academic programme application, its status, the documents you submitted and your admission letter.</p>
  </div>
</div>

<?php foreach ($applications as $a): ?>
  <?php $level = $levels[$a['award_level']] ?? null; $step = $steps[$a['status']] ?? 1; ?>
  <div class="panel admission-panel">
    <div class="panel-head">
      <h2><i class="fa-solid <?= e($level['icon'] ?? 'fa-building-columns') ?>"></i> <?= e($a['programme_title']) ?></h2>
      <span class="status status-lg status-<?= e($a['status']) ?>"><?= e($labels[$a['status']] ?? $a['status']) ?></span>
    </div>
    <div class="panel-body">
      <ol class="admission-track">
        <li class="is-done"><span><i class="fa-solid fa-paper-plane"></i></span>Application received</li>
        <li class="<?= $step >= 2 ? 'is-done' : '' ?>"><span><i class="fa-solid fa-magnifying-glass"></i></span>Under review</li>
        <li class="<?= $step >= 3 ? ($a['status'] === 'admitted' ? 'is-done' : 'is-declined') : '' ?>"><span><i class="fa-solid <?= $a['status'] === 'rejected' ? 'fa-circle-xmark' : 'fa-graduation-cap' ?>"></i></span><?= $a['status'] === 'rejected' ? 'Not admitted' : 'Admitted' ?></li>
      </ol>
      <div class="kv kv-plain">
        <div><small>Application number</small><strong><?= e($a['application_no'] ?: '—') ?></strong></div>
        <div><small>Submitted</small><strong><?= date_pretty($a['created_at'], 'j F Y') ?></strong></div>
        <div><small>Award level</small><strong><?= e($level['label'] ?? ucfirst((string) $a['award_level'])) ?></strong></div>
        <div><small>Examining / awarding body</small><strong><?= e($a['awarding_body'] ?: 'Confirmed by Admissions') ?></strong></div>
        <?php if ($a['status'] === 'admitted'): ?>
          <div><small>Admitted on</small><strong><?= $a['decided_at'] ? date_pretty($a['decided_at'], 'j F Y') : '—' ?></strong></div>
          <div><small>Intake</small><strong><?= $a['intake_code'] ? e($a['intake_code']) . ($a['intake_start'] ? ' · starts ' . date_pretty($a['intake_start']) : '') : 'To be confirmed' ?></strong></div>
        <?php endif; ?>
      </div>

      <?php if ($a['status'] === 'admitted'): ?>
        <div class="notice admission-notice"><i class="fa-solid fa-circle-check"></i>
          <p><strong>Congratulations — you have been admitted.</strong> Download your admission letter, then complete registration and fee payment under
            <a href="/learner/fees">Fees &amp; Payments</a>.</p></div>
        <div class="hero-actions" style="margin:16px 0 0">
          <a href="/learner/admission/<?= (int) $a['id'] ?>/letter" class="btn btn-primary btn-sm"><i class="fa-solid fa-file-signature"></i> Admission letter</a>
          <a href="/learner/fees" class="btn btn-outline btn-sm"><i class="fa-solid fa-receipt"></i> Fees &amp; Payments</a>
        </div>
      <?php elseif ($a['status'] === 'rejected'): ?>
        <div class="notice"><i class="fa-solid fa-circle-info"></i><p>You were not admitted to this programme. <a href="/contact">Contact Admissions</a> to discuss other programmes or future intakes.</p></div>
      <?php else: ?>
        <div class="notice"><i class="fa-solid fa-circle-info"></i><p>Admissions is reviewing your application. We will contact you by email or phone with the outcome.</p></div>
      <?php endif; ?>

      <h3 class="subhead">Documents you submitted</h3>
      <?php if ($a['files']): ?>
        <ul class="doc-links doc-links-grid">
          <?php foreach ($a['files'] as $doc): ?>
            <li><a href="<?= e($doc['url']) ?>" target="_blank" rel="noopener"><i class="fa-regular <?= $doc['ext'] === 'PDF' ? 'fa-file-pdf' : 'fa-file-image' ?>"></i><span><?= e($doc['label']) ?></span><small><?= e($doc['ext']) ?></small></a></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="muted">No documents were attached to this application.</p>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!$applications): ?>
  <div class="card empty-state"><i class="fa-solid fa-building-columns"></i><h3>No academic application on this account</h3>
    <p>Applications for Certificate, Diploma and Degree programmes appear here once you apply while signed in, or once you are admitted.</p>
    <a href="/academic" class="btn btn-primary btn-sm"><i class="fa-solid fa-building-columns"></i> Explore academic programmes</a></div>
<?php endif; ?>
