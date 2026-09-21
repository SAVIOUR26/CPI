<?php $pageTitle = 'Error ' . ($code ?? '') . ' — CPI'; ?>
<div class="container section" style="text-align:center">
  <p class="eyebrow">Error <?= e((string) ($code ?? '')) ?></p>
  <h1><?= e($message ?: 'Something went wrong') ?></h1>
  <a href="/" class="btn btn-primary">Back to Home</a>
</div>
