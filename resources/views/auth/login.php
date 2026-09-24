<?php
/** @var string|null $portal */
$portal = $portal ?? null;
$portals = [
  'student' => ['label' => 'Student', 'icon' => 'fa-user-graduate', 'title' => 'Student Portal', 'button' => 'Log in to the Student Portal',
                'sub' => 'Your courses, class materials, assignments, results, fees and certificates.'],
  'lecturer' => ['label' => 'Lecturer', 'icon' => 'fa-chalkboard-user', 'title' => 'Lecturer Portal', 'button' => 'Log in to the Lecturer Portal',
                 'sub' => 'Your classes: materials, assignments, quizzes, attendance and grades.'],
  'staff' => ['label' => 'Staff', 'icon' => 'fa-shield-halved', 'title' => 'Staff login', 'button' => 'Log in',
              'sub' => 'Admissions, finance, registry and administration.'],
];
$asides = [
  'student' => ['Welcome to the Student Portal', 'Everything for your studies at CPI in one place.', null],
  'lecturer' => ['Welcome to the Lecturer Portal', 'Teach, assess and track your classes online.', [
    ['fa-chalkboard', 'Your classes in one place', 'Materials, lecture videos and announcements for each class.'],
    ['fa-list-check', 'Assess online', 'Assignments and quizzes, graded and returned to students.'],
    ['fa-clipboard-user', 'Attendance and progress', 'Record attendance and follow each student\'s performance.'],
  ]],
  'staff' => ['CPI staff sign-in', 'Run admissions, payments, programmes and certificates.', [
    ['fa-file-signature', 'Admissions', 'Review applications and admit students.'],
    ['fa-money-bill-wave', 'Payments', 'Confirm bank transfers and track fees.'],
    ['fa-award', 'Certificates', 'Issue verifiable, QR-coded certificates.'],
  ]],
];
$current = $portal ? $portals[$portal] : null;
[$heading, $text, $points] = $portal ? $asides[$portal]
  : ['Welcome back to CPI', 'Log in to continue learning, manage your organization\'s training, or run the institute.', null];
?>
<div class="auth-page">
  <?php \App\Core\View::partial('partials.auth-aside', ['heading' => $heading, 'text' => $text, 'points' => $points]); ?>
  <div class="auth-main">
    <div class="auth-card">
      <nav class="portal-tabs" aria-label="Choose your portal">
        <?php foreach ($portals as $key => $p): ?>
          <a href="/login?portal=<?= $key ?>" class="portal-tab<?= $portal === $key ? ' is-active' : '' ?>"<?= $portal === $key ? ' aria-current="page"' : '' ?>>
            <i class="fa-solid <?= $p['icon'] ?>"></i> <?= e($p['label']) ?></a>
        <?php endforeach; ?>
      </nav>
      <p class="eyebrow">Portal login</p>
      <h1><?= e($current['title'] ?? 'Log in to your account') ?></h1>
      <p class="sub"><?= e($current['sub'] ?? 'Students, lecturers, corporate contacts and staff all sign in here — you are taken to your own portal.') ?></p>
      <div class="form-card">
        <form method="post" action="/login">
          <?= csrf_field() ?>
          <?php if ($portal): ?><input type="hidden" name="portal" value="<?= e($portal) ?>"><?php endif; ?>
          <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-icon"><i class="fa-regular fa-envelope"></i><input id="email" type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com" autocomplete="email" required autofocus></div>
            <?php foreach (field_errors('email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required></div>
            <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="auth-links"><span></span><a href="/forgot-password">Forgot your password?</a></div>
          <button type="submit" class="btn btn-primary btn-block btn-lg"><?= e($current['button'] ?? 'Log in') ?> <i class="fa-solid fa-arrow-right"></i></button>
        </form>
      </div>
      <?php if ($portal === 'lecturer'): ?>
        <div class="notice auth-notice"><i class="fa-solid fa-circle-info"></i>
          <p>Lecturer accounts are set up by CPI administration. Don't have one yet? <a href="/contact">Contact us</a> and we'll get you started.</p></div>
      <?php elseif ($portal === 'staff'): ?>
        <p class="auth-switch">Staff accounts are managed by the CPI administrator.</p>
      <?php else: ?>
        <p class="auth-switch">New student? <a href="/register">Create an account</a> · <a href="/academic">Apply for an academic programme</a></p>
      <?php endif; ?>
    </div>
  </div>
</div>
