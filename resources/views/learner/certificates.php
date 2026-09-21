<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1>My Certificates</h1></div>

<div class="grid-3">
  <?php foreach ($certificates as $c): ?>
    <div class="card">
      <h3><?= e($c['title']) ?></h3>
      <p><?= e($c['course_title']) ?></p>
      <p class="help-text">Issued <?= date_pretty($c['issued_at']) ?> &middot; <?= e($c['code']) ?></p>
      <?php if ($c['pdf_path']): ?>
        <a href="/learner/certificates/<?= e($c['code']) ?>/download" class="btn btn-primary btn-sm">Download PDF</a>
      <?php endif; ?>
      <a href="/verify/<?= e($c['code']) ?>" class="btn btn-outline btn-sm">Verify</a>
    </div>
  <?php endforeach; ?>
  <?php if (!$certificates): ?><p>No certificates yet — complete a course to earn one.</p><?php endif; ?>
</div>
