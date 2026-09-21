<?php /** @var array $meta */ /** @var array $courses */ /** @var string $slug */ ?>
<section class="section">
  <div class="container">
    <p class="eyebrow"><?= e($meta['title']) ?></p>
    <h1><?= e($meta['title']) ?></h1>
    <p class="lead" style="max-width:720px"><?= e($meta['lead']) ?></p>

    <?php if ($slug === 'corporate-training'): ?>
      <div class="hero-actions" style="margin-bottom:30px">
        <a href="/corporate/request" class="btn btn-primary">Request Customized Training</a>
        <a href="/courses" class="btn btn-outline">Browse Full Catalogue</a>
      </div>
    <?php endif; ?>

    <div class="grid-3">
      <?php foreach ($courses as $c): ?>
        <a href="/courses/<?= e($c['slug']) ?>" class="card course-card">
          <span class="badge"><?= e(ucfirst($c['level'])) ?></span>
          <h3><?= e($c['title']) ?></h3>
          <p><?= e($c['summary']) ?></p>
          <div class="price"><?= money($c['price_amount'], $c['price_currency']) ?></div>
        </a>
      <?php endforeach; ?>
      <?php if (!$courses): ?><p>Courses for this pathway are being finalized — check back shortly, or <a href="/corporate/request">request customized training</a>.</p><?php endif; ?>
    </div>
  </div>
</section>
