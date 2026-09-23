<?php
/** @var string $content */
/** @var array|null $auth_user */
/** @var string[] $auth_roles */
use App\Core\View;

$success = flash_get('success');
$error = flash_get('error');
$info = flash_get('info');
$newAccounts = flash_get('new_accounts');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
// Page views can't pass variables to the layout, so the portal is derived from the URL.
$portal = match (true) {
    str_starts_with($path, '/admin') => 'admin',
    str_starts_with($path, '/lecturer') => 'lecturer',
    str_starts_with($path, '/corporate') => 'corporate',
    default => 'learner',
};
$portalMeta = [
    'admin' => ['Admin Portal', 'fa-shield-halved', '/admin/account'],
    'lecturer' => ['Lecturer Portal', 'fa-chalkboard-user', '/lecturer/account'],
    'corporate' => ['Corporate Portal', 'fa-building', '/corporate/portal/account'],
    'learner' => ['Student Portal', 'fa-user-graduate', '/learner/profile'],
][$portal];
$sidebar = $sidebar ?? 'partials.sidebar-' . $portal;
$pageTitle = $pageTitle ?? $portalMeta[0] . ' — CPI';
$userName = $auth_user['full_name'] ?? 'Member';
$roleLabel = role_label($auth_roles ?? []);
$pageLabel = trim(explode('—', $pageTitle)[0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#1d0707">
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script>document.documentElement.classList.add('js');</script>
</head>
<body class="dash-body">
<div class="dash-shell">
  <aside class="dash-sidebar" id="dash-sidebar" aria-label="Portal navigation">
    <a href="/" class="dash-brand">
      <img src="<?= asset('img/logo.png') ?>" alt="CPI crest" width="46" height="46">
      <span><strong>CPI</strong><small><?= e($portalMeta[0]) ?></small></span>
    </a>
    <nav class="dash-nav">
      <?php View::partial($sidebar, get_defined_vars()); ?>
    </nav>
    <div class="dash-user">
      <span class="avatar"><?= e(initials($userName)) ?></span>
      <div class="dash-user-info">
        <strong><?= e($userName) ?></strong>
        <small><?= e($roleLabel) ?></small>
      </div>
      <form action="/logout" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="dash-logout" title="Log out" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></button>
      </form>
    </div>
  </aside>
  <div class="dash-overlay" data-dash-close></div>

  <div class="dash-main-wrap">
    <header class="dash-topbar">
      <button type="button" class="dash-menu-btn" id="dash-menu-btn" aria-label="Open navigation" aria-expanded="false" aria-controls="dash-sidebar">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="dash-crumb">
        <i class="fa-solid <?= e($portalMeta[1]) ?>"></i>
        <span><?= e($portalMeta[0]) ?></span>
        <?php if ($pageLabel && $pageLabel !== $portalMeta[0]): ?><span>/</span><strong><?= e($pageLabel) ?></strong><?php endif; ?>
      </div>
      <div class="dash-topbar-actions">
        <a href="/" class="topbar-btn" title="View website"><i class="fa-solid fa-globe"></i><span>View website</span></a>
        <a href="<?= e($portalMeta[2]) ?>" class="user-chip" title="My account"><span class="avatar"><?= e(initials($userName)) ?></span><span><?= e(explode(' ', $userName)[0] === 'Dr.' ? $userName : explode(' ', $userName)[0]) ?></span></a>
      </div>
    </header>

    <main class="dash-main" id="main">
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($info): ?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>
      <?php if ($newAccounts): ?><?php View::partial('partials.new-accounts', ['accounts' => $newAccounts]); ?><?php endif; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
