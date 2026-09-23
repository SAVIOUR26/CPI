<div class="auth-page">
  <?php \App\Core\View::partial('partials.auth-aside', ['heading' => 'Welcome back to CPI', 'text' => 'Log in to continue learning, manage your organization\'s training, or run the institute.']); ?>
  <div class="auth-main">
    <div class="auth-card">
      <p class="eyebrow">Portal login</p>
      <h1>Log in to your account</h1>
      <p class="sub">Learners, lecturers, corporate contacts and staff all sign in here.</p>
      <div class="form-card">
        <form method="post" action="/login">
          <?= csrf_field() ?>
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
          <button type="submit" class="btn btn-primary btn-block btn-lg">Log in <i class="fa-solid fa-arrow-right"></i></button>
        </form>
      </div>
      <p class="auth-switch">New to CPI? <a href="/register">Create an account</a></p>
    </div>
  </div>
</div>
