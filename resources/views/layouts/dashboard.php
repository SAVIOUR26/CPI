<?php
/** @var string $content */
/** @var string $sidebar partial view name, e.g. 'partials.sidebar-learner' */
use App\Core\View;

$pageTitle = $pageTitle ?? 'Dashboard — CPI';
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
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="dash">
  <aside class="dash-sidebar">
    <a href="/" class="brand-mini">CPI</a>
    <?php View::partial($sidebar ?? 'partials.sidebar-learner', get_defined_vars()); ?>
    <form action="/logout" method="post" style="margin-top:20px">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-outline btn-sm btn-block" style="border-color:#fff;color:#fff">Logout</button>
    </form>
  </aside>
  <div class="dash-main">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>
    <?= $content ?>
  </div>
</div>
</body>
</html>
