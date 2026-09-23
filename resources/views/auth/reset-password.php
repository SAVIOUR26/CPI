<?php /** @var string $email */ /** @var string $token */ ?>
<div class="auth-page">
  <?php \App\Core\View::partial('partials.auth-aside', ['heading' => 'Set a new password', 'text' => 'Choose a strong password you haven\'t used before.']); ?>
  <div class="auth-main">
    <div class="auth-card">
      <p class="eyebrow">Password reset</p>
      <h1>Create a new password</h1>
      <p class="sub">You'll be able to log in straight away.</p>
      <div class="form-card">
        <form method="post" action="/reset-password">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-icon"><i class="fa-regular fa-envelope"></i><input id="email" type="email" name="email" value="<?= e($email) ?>" required></div>
          </div>
          <div class="form-group">
            <label for="password">New password</label>
            <div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password" type="password" name="password" autocomplete="new-password" required></div>
            <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
          </div>
          <div class="form-group">
            <label for="password_confirmation">Confirm new password</label>
            <div class="input-icon"><i class="fa-solid fa-lock"></i><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg">Reset password</button>
        </form>
      </div>
    </div>
  </div>
</div>
