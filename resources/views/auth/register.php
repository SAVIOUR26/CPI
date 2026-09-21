<section class="section">
  <div class="container">
    <div class="form-card">
      <h1 style="text-align:center">Create your CPI account</h1>
      <form method="post" action="/register">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Full name</label>
          <input type="text" name="full_name" value="<?= e(old('full_name')) ?>" required autofocus>
          <?php foreach (field_errors('full_name') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= e(old('email')) ?>" required>
          <?php foreach (field_errors('email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="phone" value="<?= e(old('phone')) ?>">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
          <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Confirm password</label>
          <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
      </form>
      <p style="text-align:center;margin-top:16px">Already have an account? <a href="/login">Login</a></p>
    </div>
  </div>
</section>
