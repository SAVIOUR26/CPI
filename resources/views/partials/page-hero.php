<?php
/**
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $lead
 * @var array $crumbs   [['label' => 'Courses', 'href' => '/courses'], ['label' => 'Current page']]
 * @var array $actions  [['label' => '...', 'href' => '...', 'class' => 'btn-gold', 'icon' => 'fa-arrow-right']]
 * @var array $stats    [['icon' => 'fa-book-open', 'text' => '184 courses']]
 */
$crumbs = $crumbs ?? [];
$actions = $actions ?? [];
$stats = $stats ?? [];
?>
<section class="page-hero">
  <div class="hero-bg" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <div class="bg-dots"></div>
    <div class="floating-icons">
      <i class="fa-solid fa-graduation-cap" style="--x:8%;--d:26s;--delay:-6s;--s:1.4rem"></i>
      <i class="fa-solid fa-book-open gold" style="--x:24%;--d:30s;--delay:-18s;--s:1.1rem"></i>
      <i class="fa-solid fa-lightbulb" style="--x:52%;--d:24s;--delay:-12s;--s:1.2rem"></i>
      <i class="fa-solid fa-award gold" style="--x:72%;--d:28s;--delay:-3s;--s:1.3rem"></i>
      <i class="fa-solid fa-chart-line" style="--x:88%;--d:32s;--delay:-20s;--s:1.1rem"></i>
    </div>
  </div>
  <div class="container page-hero-inner">
    <?php if ($crumbs): ?>
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="/"><i class="fa-solid fa-house"></i> Home</a>
        <?php foreach ($crumbs as $crumb): ?>
          <i class="fa-solid fa-chevron-right sep"></i>
          <?php if (!empty($crumb['href'])): ?>
            <a href="<?= e($crumb['href']) ?>"><?= e($crumb['label']) ?></a>
          <?php else: ?>
            <span><?= e($crumb['label']) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <?php if (!empty($eyebrow)): ?><p class="eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
    <h1><?= e($title) ?></h1>
    <?php if (!empty($lead)): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
    <?php if ($stats): ?>
      <div class="page-hero-stats">
        <?php foreach ($stats as $s): ?><span><i class="fa-solid <?= e($s['icon']) ?>"></i> <?= e($s['text']) ?></span><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($actions): ?>
      <div class="page-hero-actions">
        <?php foreach ($actions as $a): ?>
          <a href="<?= e($a['href']) ?>" class="btn <?= e($a['class'] ?? 'btn-gold') ?>"><?php if (!empty($a['icon'])): ?><i class="fa-solid <?= e($a['icon']) ?>"></i> <?php endif; ?><?= e($a['label']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
