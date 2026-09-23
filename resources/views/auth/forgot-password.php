<div class="auth-page">
  <?php \App\Core\View::partial('partials.auth-aside', ['heading' => 'Locked out? It happens.', 'text' => 'We\'ll email you a secure link to set a new password.']); ?>
  <div class="auth-main">
    <div class="auth-card">
      <p class="eyebrow">Password help</p>
      <h1>Forgot your password?</h1>
      <p class="sub">Enter the email you registered with and we'll send you a reset link.</p>
      <div class="form-card">
        <form method="post" action="/forgot-password">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-icon"><i class="fa-regular fa-envelope"></i><input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required autofocus></div>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-paper-plane"></i> Send reset link</button>
        </form>
      </div>
      <p class="auth-switch">Remembered it? <a href="/login">Back to login</a></p>
    </div>
  </div>
</div>
