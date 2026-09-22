<?php
/** @var string $content */
/** @var array|null $auth_user */
/** @var string[] $auth_roles */
use App\Core\Auth;

$pageTitle = $pageTitle ?? 'Crawford Professionals Institute (CPI)';
$success = flash_get('success');
$error = flash_get('error');
$info = flash_get('info');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="container bar">
    <a href="/" class="brand">
      <img src="<?= asset('img/logo.png') ?>" alt="CPI crest">
      <span class="brand-full">Crawford Professionals Institute<small>Empowering Skills, Transforming Lives.</small></span>
      <span class="brand-short">CPI</span>
    </a>
    <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-collapse" aria-label="Open menu">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="nav-collapse" id="nav-collapse">
      <nav class="main-nav">
        <a href="/professional-training">Professional Training</a>
        <a href="/capacity-building">Capacity Building</a>
        <a href="/corporate-training">Corporate Training</a>
        <a href="/courses">Courses</a>
        <a href="/verify">Verify Certificate</a>
        <a href="/about">About</a>
      </nav>
      <div class="nav-actions">
        <?php if (Auth::check()): ?>
          <a href="<?= Auth::homeFor($auth_roles ?? []) ?>" class="btn btn-outline btn-sm">My Dashboard</a>
          <form action="/logout" method="post" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm">Logout</button>
          </form>
        <?php else: ?>
          <a href="/login" class="btn btn-outline btn-sm">Login</a>
          <a href="/register" class="btn btn-primary btn-sm">Register <i class="fa-solid fa-arrow-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<main>
<?php if ($success): ?><div class="container" style="padding-top:20px"><div class="alert alert-success"><?= e($success) ?></div></div><?php endif; ?>
<?php if ($error): ?><div class="container" style="padding-top:20px"><div class="alert alert-error"><?= e($error) ?></div></div><?php endif; ?>
<?php if ($info): ?><div class="container" style="padding-top:20px"><div class="alert alert-info"><?= e($info) ?></div></div><?php endif; ?>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4><i class="fa-solid fa-building-columns"></i> Crawford Professionals Institute</h4>
        <p style="color:#a39c8f">CRAWFORD PROFESSIONALS INSTITUTE (CPI) LIMITED. Empowering Skills, Transforming Lives.</p>
      </div>
      <div>
        <h4><i class="fa-solid fa-graduation-cap"></i> Programmes</h4>
        <p><a href="/professional-training">Professional Training</a></p>
        <p><a href="/capacity-building">Capacity Building</a></p>
        <p><a href="/corporate-training">Corporate Training</a></p>
      </div>
      <div>
        <h4><i class="fa-solid fa-book-open"></i> Resources</h4>
        <p><a href="/courses">Course Catalogue</a></p>
        <p><a href="/verify">Verify a Certificate</a></p>
        <p><a href="/corporate/request">Request Corporate Training</a></p>
      </div>
      <div>
        <h4><i class="fa-solid fa-address-card"></i> Contact</h4>
        <p class="contact-line"><i class="fa-solid fa-envelope"></i> info@crawfordinstitute.online</p>
        <p><a href="/contact">Contact form</a></p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Crawford Professionals Institute (CPI) Limited. All rights reserved.</span>
      <span>crawfordinstitute.online</span>
    </div>
  </div>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
