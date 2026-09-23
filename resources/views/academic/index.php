<?php \App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Academic programmes',
  'title' => 'Certificate, diploma & degree programmes',
  'lead' => 'Accredited academic programmes offered by Crawford Professionals Institute.',
  'crumbs' => [['label' => 'Academic Programmes']],
]); ?>
<section class="section">
  <div class="container">

    <div class="grid-3">
      <?php foreach ($programmes as $p): ?>
        <div class="card class-card">
          <span class="badge"><i class="fa-solid fa-graduation-cap"></i> <?= e(ucfirst($p['award_level'])) ?></span>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e($p['summary']) ?></p>
          <a href="/academic/apply/<?= (int) $p['id'] ?>" class="btn btn-primary btn-sm" style="align-self:flex-start;margin-top:auto">Apply now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      <?php endforeach; ?>
      <?php if (!$programmes): ?><p>No academic programmes are open for applications right now.</p><?php endif; ?>
    </div>
  </div>
</section>
