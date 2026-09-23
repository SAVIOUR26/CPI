<div class="auth-page">
  <?php \App\Core\View::partial('partials.auth-aside', ['heading' => 'Start learning with CPI', 'text' => 'Create a free account to enrol in courses, pay securely online and earn verifiable certificates.']); ?>
  <div class="auth-main">
    <div class="auth-card">
      <p class="eyebrow">Create account</p>
      <h1>Join Crawford Professionals Institute</h1>
      <p class="sub">It takes less than a minute.</p>
      <div class="form-card">
        <form method="post" action="/register">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="full_name">Full name</label>
            <div class="input-icon"><i class="fa-regular fa-user"></i><input id="full_name" type="text" name="full_name" value="<?= e(old('full_name')) ?>" placeholder="As it should appear on your certificate" autocomplete="name" required autofocus></div>
            <?php foreach (field_errors('full_name') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-icon"><i class="fa-regular fa-envelope"></i><input id="email" type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com" autocomplete="email" required></div>
              <?php foreach (field_errors('email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
            </div>
            <div class="form-group">
              <label for="phone">Phone <span class="muted">(optional)</span></label>
              <div class="input-icon"><i class="fa-solid fa-phone"></i><input id="phone" type="tel" name="phone" value="<?= e(old('phone')) ?>" placeholder="+256 7XX XXX XXX" autocomplete="tel"></div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password" type="password" name="password" autocomplete="new-password" required></div>
              <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
            </div>
            <div class="form-group">
              <label for="password_confirmation">Confirm password</label>
              <div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg">Create account <i class="fa-solid fa-arrow-right"></i></button>
        </form>
      </div>
      <p class="auth-switch">Already have an account? <a href="/login">Log in</a></p>
    </div>
  </div>
</div>
