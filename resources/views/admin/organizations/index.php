<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Organizations</h1></div>

<div class="table-card" style="margin-bottom:24px">
  <table>
    <thead><tr><th>Name</th><th>Sector</th><th>Cohorts</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orgs as $o): ?>
      <tr>
        <td><?= e($o['name']) ?></td>
        <td><?= e($o['sector'] ?? '—') ?></td>
        <td><?= (int) $o['cohort_count'] ?></td>
        <td><a href="/admin/organizations/<?= (int) $o['id'] ?>" class="btn btn-sm btn-outline">Manage</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$orgs): ?><tr><td colspan="4">No organizations yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card" style="max-width:500px">
  <h3>Add Organization</h3>
  <form method="post" action="/admin/organizations">
    <?= csrf_field() ?>
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Sector</label><input type="text" name="sector"></div>
    <div class="form-group"><label>Address</label><input type="text" name="address"></div>
    <button type="submit" class="btn btn-primary btn-sm">Add</button>
  </form>
</div>
