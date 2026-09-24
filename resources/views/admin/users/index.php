<?php
/** @var array $users */ /** @var array $roles */ /** @var string|null $search */
// Most-used roles first; "learner" is what the site calls a student.
$order = ['lecturer', 'learner', 'admissions', 'finance', 'registrar', 'content_manager', 'corporate_contact', 'super_admin'];
usort($roles, fn ($a, $b) => (array_search($a['slug'], $order, true) ?? 99) <=> (array_search($b['slug'], $order, true) ?? 99));
$roleName = fn (array $r): string => $r['slug'] === 'learner' ? 'Student' : $r['name'];
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
    <p class="eyebrow">System</p>
    <h1>Users &amp; Roles</h1>
    <p>Add lecturers, staff and students, and choose what each account can open.</p>
  </div>
</div>

<div class="panel" id="add-user">
  <div class="panel-head"><h2><i class="fa-solid fa-user-plus"></i> Add a user</h2></div>
  <div class="panel-body">
    <form method="post" action="/admin/users">
      <?= csrf_field() ?>
      <div class="form-row add-user-row">
        <div class="form-group">
          <label for="new_name">Full name</label>
          <input id="new_name" type="text" name="full_name" value="<?= e(old('full_name')) ?>" required>
          <?= $err('full_name') ?>
        </div>
        <div class="form-group">
          <label for="new_email">Email</label>
          <input id="new_email" type="email" name="email" value="<?= e(old('email')) ?>" required>
          <?= $err('email') ?>
        </div>
        <div class="form-group">
          <label for="new_phone">Phone</label>
          <input id="new_phone" type="tel" name="phone" value="<?= e(old('phone')) ?>">
        </div>
        <div class="form-group">
          <label for="new_role">Role</label>
          <select id="new_role" name="role" required>
            <option value="">Choose a role…</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= e($r['slug']) ?>"<?= old('role') === $r['slug'] ? ' selected' : '' ?>><?= e($roleName($r)) ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('role') ?>
        </div>
      </div>
      <div class="add-user-foot">
        <p class="help-text"><i class="fa-solid fa-key"></i> They get a temporary password by email, and it is also shown to you once here so you can share it
          directly. Lecturers then need a class: <a href="/admin/courses">Courses &amp; Intakes</a> → open the course → set the intake's lecturer.</p>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Create account</button>
      </div>
    </form>
  </div>
</div>

<form method="get" action="/admin/users" class="users-search">
  <div class="input-icon"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" placeholder="Search name or email…" value="<?= e($search ?? '') ?>" aria-label="Search users"></div>
</form>

<div class="table-card" data-no-filter>
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><div class="cell-person"><span class="avatar"><?= e(initials($u['full_name'])) ?></span><strong><?= e($u['full_name']) ?></strong></div></td>
        <td><?= e($u['email']) ?></td>
        <td>
          <form method="post" action="/admin/users/<?= (int) $u['id'] ?>/roles" class="role-checks">
            <?= csrf_field() ?>
            <?php $current = array_filter(explode(', ', (string) $u['role_slugs'])); ?>
            <?php foreach ($roles as $r): ?>
              <label class="role-check"><input type="checkbox" name="roles[]" value="<?= e($r['slug']) ?>"<?= in_array($r['slug'], $current, true) ? ' checked' : '' ?>> <?= e($roleName($r)) ?></label>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-sm btn-outline">Save roles</button>
          </form>
        </td>
        <td><span class="status status-<?= $u['status'] === 'active' ? 'active' : 'cancelled' ?>"><?= e(str_replace('_', ' ', $u['status'])) ?></span></td>
        <td class="cell-actions">
          <form method="post" action="/admin/users/<?= (int) $u['id'] ?>/status">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline"><?= $u['status'] === 'active' ? 'Suspend' : 'Reactivate' ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-users"></i>No users found.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
