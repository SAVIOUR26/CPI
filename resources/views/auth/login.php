<section class="section">
  <div class="container">
    <div class="form-card">
      <h1 style="text-align:center">Login</h1>
      <form method="post" action="/login">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
          <?php foreach (field_errors('email') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
          <?php foreach (field_errors('password') as $err): ?><div class="field-error"><?= e($err) ?></div><?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>
      <p style="text-align:center;margin-top:16px">
        <a href="/forgot-password">Forgot your password?</a>
      </p>
      <p style="text-align:center">New to CPI? <a href="/register">Create an account</a></p>
    </div>
  </div>
</section>
