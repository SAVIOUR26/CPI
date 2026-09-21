<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1>My Profile</h1></div>

<div class="grid-2">
  <div class="card">
    <h3>Profile details</h3>
    <form method="post" action="/learner/profile">
      <?= csrf_field() ?>
      <div class="form-group"><label>Full name</label><input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required></div>
      <div class="form-group"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div>
      <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
  <div class="card">
    <h3>Change password</h3>
    <form method="post" action="/learner/profile/password">
      <?= csrf_field() ?>
      <div class="form-group"><label>Current password</label><input type="password" name="current_password" required></div>
      <div class="form-group"><label>New password</label><input type="password" name="password" required></div>
      <div class="form-group"><label>Confirm new password</label><input type="password" name="password_confirmation" required></div>
      <button type="submit" class="btn btn-outline">Update Password</button>
    </form>
  </div>
</div>
