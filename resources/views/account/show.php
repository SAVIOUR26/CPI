<?php
/** @var array $user */ /** @var string $page */ /** @var string[] $auth_roles */
$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">My account</p>
    <h1>Account settings</h1>
    <p>Keep your details up to date and your account secure.</p>
  </div>
</div>

<div class="account-grid">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-id-card"></i> Your details</h2></div>
    <div class="panel-body">
      <div class="account-id">
        <span class="avatar avatar-lg"><?= e(initials($user['full_name'])) ?></span>
        <div>
          <strong><?= e($user['full_name']) ?></strong>
          <small><?= e(role_label($auth_roles ?? [])) ?><?= !empty($user['created_at']) ? ' · member since ' . date_pretty($user['created_at'], 'F Y') : '' ?></small>
        </div>
      </div>
      <form method="post" action="<?= e($page) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="full_name">Full name</label>
          <input id="full_name" type="text" name="full_name" value="<?= e(old('full_name', $user['full_name'])) ?>" maxlength="150" autocomplete="name" required>
          <?= $err('full_name') ?>
        </div>
        <div class="form-group">
          <label for="email">Email address</label>
          <input id="email" type="email" name="email" value="<?= e(old('email', $user['email'])) ?>" maxlength="190" autocomplete="email" required>
          <p class="help-text">You sign in with this email address.</p>
          <?= $err('email') ?>
        </div>
        <div class="form-group">
          <label for="phone">Phone</label>
          <input id="phone" type="tel" name="phone" value="<?= e(old('phone', (string) ($user['phone'] ?? ''))) ?>" maxlength="30" autocomplete="tel" placeholder="+256 7XX XXX XXX">
          <?= $err('phone') ?>
        </div>
        <div class="form-group">
          <label for="password_check">Current password</label>
          <input id="password_check" type="password" name="password_check" autocomplete="current-password">
          <p class="help-text">Only needed if you change your email address.</p>
          <?= $err('password_check') ?>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save details</button>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-lock"></i> Change password</h2></div>
    <div class="panel-body">
      <p class="muted" style="margin-top:0">Were you given a temporary password? Replace it here with one only you know.</p>
      <form method="post" action="<?= e($page) ?>/password">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="current_password">Current password</label>
          <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
          <?= $err('current_password') ?>
        </div>
        <div class="form-group">
          <label for="new_password">New password</label>
          <input id="new_password" type="password" name="password" autocomplete="new-password" minlength="8" required>
          <p class="help-text">At least 8 characters.</p>
          <?= $err('password') ?>
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirm new password</label>
          <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Change password</button>
      </form>
    </div>
  </div>
</div>
