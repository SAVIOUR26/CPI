<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Users &amp; Roles</h1></div>

<form method="get" action="/admin/users" style="max-width:360px;margin-bottom:20px">
  <input type="text" name="q" placeholder="Search name or email…" value="<?= e($search ?? '') ?>">
</form>

<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['full_name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td>
          <form method="post" action="/admin/users/<?= (int) $u['id'] ?>/roles" style="display:flex;gap:4px;flex-wrap:wrap;align-items:center">
            <?= csrf_field() ?>
            <?php $current = array_filter(explode(', ', (string) $u['role_slugs'])); ?>
            <?php foreach ($roles as $r): ?>
              <label style="font-weight:400;font-size:.78rem"><input type="checkbox" name="roles[]" value="<?= e($r['slug']) ?>" style="width:auto" <?= in_array($r['slug'], $current, true) ? 'checked' : '' ?>> <?= e($r['slug']) ?></label>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-sm btn-outline">Save</button>
          </form>
        </td>
        <td><span class="status status-<?= $u['status']==='active' ? 'active' : 'cancelled' ?>"><?= e(str_replace('_', ' ', $u['status'])) ?></span></td>
        <td>
          <form method="post" action="/admin/users/<?= (int) $u['id'] ?>/status">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline"><?= $u['status']==='active' ? 'Suspend' : 'Reactivate' ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="5">No users found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card" style="max-width:500px">
  <h3>Create Staff User</h3>
  <form method="post" action="/admin/users">
    <?= csrf_field() ?>
    <div class="form-group"><label>Full name</label><input type="text" name="full_name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
    <div class="form-group"><label>Role</label>
      <select name="role">
        <?php foreach ($roles as $r): ?><option value="<?= e($r['slug']) ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Create &amp; Notify</button>
  </form>
</div>
