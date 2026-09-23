<?php
/** @var string $content */
/** @var array|null $auth_user */
/** @var string[] $auth_roles */
use App\Core\Auth;

$pageTitle = $pageTitle ?? 'Crawford Professionals Institute (CPI)';
$metaDescription = $metaDescription ?? 'Crawford Professionals Institute (CPI), Kampala — practical professional training, capacity building and customized corporate training for professionals across Africa.';
$success = flash_get('success');
$error = flash_get('error');
$info = flash_get('info');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isActive = fn (string $prefix): bool => $prefix === '/' ? $path === '/' : ($path === $prefix || str_starts_with($path, $prefix . '/'));
$programmeActive = $isActive('/professional-training') || $isActive('/capacity-building') || $isActive('/corporate-training') || $isActive('/academic');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<meta name="theme-color" content="#560b0b">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:image" content="<?= asset('img/logo.png') ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/favicon-64.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script>document.documentElement.classList.add('js');</script>
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>

<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-group info">
      <span><i class="fa-solid fa-location-dot"></i> Kampala, Uganda</span>
      <a href="mailto:info@crawfordinstitute.online"><i class="fa-solid fa-envelope"></i> info@crawfordinstitute.online</a>
    </div>
    <div class="topbar-group">
      <a href="/verify"><i class="fa-solid fa-shield-halved"></i> Verify a Certificate</a>
      <a href="/corporate/request"><i class="fa-solid fa-handshake"></i> Corporate Enquiries</a>
    </div>
  </div>
</div>

<header class="site-header" id="site-header">
  <div class="container bar">
    <a href="/" class="brand" aria-label="Crawford Professionals Institute — home">
      <img src="<?= asset('img/logo.png') ?>" alt="CPI crest" width="64" height="64">
      <span class="brand-text">
        <span class="brand-name">Crawford Professionals Institute</span>
        <span class="brand-short">CPI</span>
        <span class="brand-tagline">Empowering Skills &middot; Transforming Lives</span>
        <span class="brand-tagline-short">Crawford Professionals Institute</span>
      </span>
    </a>

    <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="nav-collapse" aria-label="Open menu">
      <i class="fa-solid fa-bars"></i>
    </button>

    <div class="nav-collapse" id="nav-collapse">
      <nav class="main-nav" aria-label="Main">
        <div class="nav-item">
          <button type="button" class="nav-link<?= $programmeActive ? ' is-active' : '' ?>" data-dropdown aria-haspopup="true" aria-expanded="false">
            Programmes <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="dropdown">
            <a href="/professional-training" class="dropdown-item">
              <span class="dd-icon"><i class="fa-solid fa-user-graduate"></i></span>
              <span><strong>Professional Training</strong><small>Short courses and certifications for individual professionals.</small></span>
            </a>
            <a href="/capacity-building" class="dropdown-item">
              <span class="dd-icon"><i class="fa-solid fa-people-group"></i></span>
              <span><strong>Capacity Building</strong><small>Structured programmes that strengthen institutions.</small></span>
            </a>
            <a href="/corporate-training" class="dropdown-item">
              <span class="dd-icon"><i class="fa-solid fa-building"></i></span>
              <span><strong>Corporate Training</strong><small>Customized training delivered to your organization.</small></span>
            </a>
            <a href="/academic" class="dropdown-item">
              <span class="dd-icon"><i class="fa-solid fa-building-columns"></i></span>
              <span><strong>Academic Programmes</strong><small>Certificate, Diploma &amp; Degree programmes with partner universities.</small></span>
            </a>
            <div class="dropdown-foot">
              <span>Looking for something specific?</span>
              <a href="/courses" class="link-arrow">All courses <i class="fa-solid fa-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <a href="/courses" class="nav-link<?= $isActive('/courses') ? ' is-active' : '' ?>">Courses</a>
        <a href="/verify" class="nav-link<?= $isActive('/verify') ? ' is-active' : '' ?>">Verify Certificate</a>
        <a href="/about" class="nav-link<?= $isActive('/about') ? ' is-active' : '' ?>">About</a>
        <a href="/contact" class="nav-link<?= $isActive('/contact') ? ' is-active' : '' ?>">Contact</a>
      </nav>
      <div class="nav-actions">
        <?php if (Auth::check()): ?>
          <a href="<?= Auth::homeFor($auth_roles ?? []) ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-gauge-high"></i> My Dashboard</a>
          <form action="/logout" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
          </form>
        <?php else: ?>
          <a href="/login" class="btn btn-outline btn-sm"><i class="fa-regular fa-user"></i> Login</a>
          <a href="/register" class="btn btn-primary btn-sm">Register <i class="fa-solid fa-arrow-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<main id="main">
<?php if ($success || $error || $info): ?>
  <div class="container flash-wrap">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>
  </div>
<?php endif; ?>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="/" class="footer-brand">
          <img src="<?= asset('img/logo.png') ?>" alt="" width="60" height="60">
          <span><strong>Crawford Professionals Institute</strong><small>Empowering Skills &middot; Transforming Lives</small></span>
        </a>
        <p class="footer-about">A Kampala-based professional training and capacity-building institution equipping individuals and
          organizations with practical, industry-relevant skills for today's dynamic workplace.</p>
      </div>
      <div>
        <h4>Programmes</h4>
        <ul class="footer-links">
          <li><a href="/professional-training"><i class="fa-solid fa-chevron-right"></i> Professional Training</a></li>
          <li><a href="/capacity-building"><i class="fa-solid fa-chevron-right"></i> Capacity Building</a></li>
          <li><a href="/corporate-training"><i class="fa-solid fa-chevron-right"></i> Corporate Training</a></li>
          <li><a href="/academic"><i class="fa-solid fa-chevron-right"></i> Academic Programmes</a></li>
          <li><a href="/courses"><i class="fa-solid fa-chevron-right"></i> Course Catalogue</a></li>
        </ul>
      </div>
      <div>
        <h4>Institute</h4>
        <ul class="footer-links">
          <li><a href="/about"><i class="fa-solid fa-chevron-right"></i> About CPI</a></li>
          <li><a href="/verify"><i class="fa-solid fa-chevron-right"></i> Verify a Certificate</a></li>
          <li><a href="/corporate/request"><i class="fa-solid fa-chevron-right"></i> Request Corporate Training</a></li>
          <li><a href="/login"><i class="fa-solid fa-chevron-right"></i> Learner Portal</a></li>
        </ul>
      </div>
      <div>
        <h4>Get in touch</h4>
        <p class="contact-line"><i class="fa-solid fa-location-dot"></i> <span>Kampala, Uganda</span></p>
        <p class="contact-line"><i class="fa-solid fa-envelope"></i> <a href="mailto:info@crawfordinstitute.online">info@crawfordinstitute.online</a></p>
        <p class="contact-line"><i class="fa-solid fa-comments"></i> <a href="/contact">Send us a message</a></p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Crawford Professionals Institute (CPI) Limited. All rights reserved.</span>
      <span><a href="/about">About</a> &nbsp;&middot;&nbsp; <a href="/contact">Contact</a> &nbsp;&middot;&nbsp; crawfordinstitute.online</span>
    </div>
  </div>
</footer>

<button type="button" class="back-to-top" id="back-to-top" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></button>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
