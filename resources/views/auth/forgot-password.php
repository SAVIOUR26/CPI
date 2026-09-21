<section class="section">
  <div class="container">
    <div class="form-card">
      <h1 style="text-align:center">Forgot Password</h1>
      <p>Enter your email and we'll send you a link to reset your password.</p>
      <form method="post" action="/forgot-password">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
      </form>
    </div>
  </div>
</section>
