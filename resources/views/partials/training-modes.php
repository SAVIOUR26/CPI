<?php
/** @var string|null $title */ /** @var string|null $intro */
use App\Support\Institute;
?>
<div class="section-head center" data-reveal>
  <p class="eyebrow">Training modes</p>
  <h2><?= e($title ?? 'How we deliver training') ?></h2>
  <p><?= e($intro ?? 'Choose the format that suits you or your organization.') ?></p>
</div>
<div class="grid-4 modes-grid">
  <?php foreach (Institute::TRAINING_MODES as $n => [$icon, $name, $text]): ?>
    <div class="card mode-card" data-reveal style="--i:<?= $n ?>">
      <span class="icon-badge"><i class="fa-solid <?= e($icon) ?>"></i></span>
      <h3><?= e($name) ?></h3>
      <p><?= e($text) ?></p>
    </div>
  <?php endforeach; ?>
</div>
<p class="coverage-line" data-reveal><i class="fa-solid fa-earth-africa"></i> Based in <strong><?= e(Institute::LOCATION) ?></strong> ·
  training coverage: <strong><?= e(Institute::COVERAGE) ?></strong></p>
