<?php $pageTitle = 'Error ' . ($code ?? '') . ' — CPI'; ?>
<section class="error-page">
  <div class="container">
    <div class="error-code"><?= e((string) ($code ?? '!')) ?></div>
    <h1><?= e($message ?: 'Something went wrong') ?></h1>
    <p class="lead">Please try again, or head back to the homepage.</p>
    <a href="/" class="btn btn-primary" style="margin-top:16px"><i class="fa-solid fa-house"></i> Back to home</a>
  </div>
</section>
