<?php /** @var string $heading */ /** @var string $text */ ?>
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
    <li><i class="fa-solid fa-book-open-reader"></i><div><strong>Your courses in one place</strong><span>Materials, quizzes, assignments and grades.</span></div></li>
    <li><i class="fa-solid fa-mobile-screen-button"></i><div><strong>Pay the easy way</strong><span>MTN Mobile Money, Airtel Money, card or bank transfer.</span></div></li>
    <li><i class="fa-solid fa-shield-halved"></i><div><strong>Verifiable certificates</strong><span>QR-coded certificates employers can check online.</span></div></li>
  </ul>
</aside>
