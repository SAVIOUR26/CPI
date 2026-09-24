<?php
/** @var string $heading */ /** @var string $text */ /** @var array|null $points [icon, title, text] rows */
$points = ($points ?? null) ?: [
  ['fa-book-open-reader', 'Your courses in one place', 'Materials, quizzes, assignments and grades.'],
  ['fa-mobile-screen-button', 'Pay the easy way', 'MTN Mobile Money or Airtel Money, then upload your receipt.'],
  ['fa-shield-halved', 'Verifiable certificates', 'QR-coded certificates employers can check online.'],
];
?>
<aside class="auth-aside">
  <div class="hero-bg" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <div class="bg-dots"></div>
    <div class="floating-icons">
      <i class="fa-solid fa-graduation-cap" style="--x:12%;--d:26s;--delay:-5s;--s:1.5rem"></i>
      <i class="fa-solid fa-book-open gold" style="--x:38%;--d:30s;--delay:-16s;--s:1.2rem"></i>
      <i class="fa-solid fa-award" style="--x:64%;--d:24s;--delay:-10s;--s:1.3rem"></i>
      <i class="fa-solid fa-lightbulb gold" style="--x:86%;--d:28s;--delay:-2s;--s:1.2rem"></i>
    </div>
  </div>
  <img src="<?= asset('img/logo.png') ?>" alt="CPI crest" class="auth-crest" width="76" height="76">
  <h2><?= e($heading) ?></h2>
  <p><?= e($text) ?></p>
  <ul class="auth-points">
    <?php foreach ($points as [$icon, $title, $body]): ?>
      <li><i class="fa-solid <?= e($icon) ?>"></i><div><strong><?= e($title) ?></strong><span><?= e($body) ?></span></div></li>
    <?php endforeach; ?>
  </ul>
</aside>
