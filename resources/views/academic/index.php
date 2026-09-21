<section class="section">
  <div class="container">
    <p class="eyebrow">Private Academic System</p>
    <h1>Academic Programmes</h1>
    <p class="lead">Certificate, diploma and degree programmes offered by Crawford Professionals Institute.</p>

    <div class="grid-3">
      <?php foreach ($programmes as $p): ?>
        <div class="card">
          <span class="badge"><?= e(ucfirst($p['award_level'])) ?></span>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e($p['summary']) ?></p>
          <a href="/academic/apply/<?= (int) $p['id'] ?>" class="btn btn-primary btn-sm">Apply Now</a>
        </div>
      <?php endforeach; ?>
      <?php if (!$programmes): ?><p>No academic programmes are open for applications right now.</p><?php endif; ?>
    </div>
  </div>
</section>
