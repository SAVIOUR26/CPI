<?php /** @var string $email */ /** @var string $token */ ?>
<section class="section">
  <div class="container">
    <div class="form-card">
      <h1 style="text-align:center">Reset Password</h1>
      <form method="post" action="/reset-password">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= e($email) ?>" required>
        </div>
        <div class="form-group">
          <label>New password</label>
          <input type="password" name="password" required>
          <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Confirm new password</label>
          <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
      </form>
    </div>
  </div>
</section>
