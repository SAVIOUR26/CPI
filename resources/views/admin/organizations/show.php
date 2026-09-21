<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1><?= e($org['name']) ?></h1></div>

<div class="grid-2" style="margin-bottom:24px">
  <div class="card">
    <h3>Portal Contact</h3>
    <form method="post" action="/admin/organizations/<?= (int) $org['id'] ?>/contact">
      <?= csrf_field() ?>
      <div class="form-group"><label>Name</label><input type="text" name="contact_name" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="contact_email" required></div>
      <button type="submit" class="btn btn-primary btn-sm">Set / Invite Contact</button>
    </form>
    <h4 style="margin-top:16px">Current members</h4>
    <?php foreach ($members as $m): ?><p><?= e($m['full_name']) ?> — <?= e($m['email']) ?> <?= $m['title'] ? '(' . e($m['title']) . ')' : '' ?></p><?php endforeach; ?>
  </div>

  <div class="card">
    <h3>Bulk-Enrol Staff</h3>
    <form method="post" action="/admin/organizations/<?= (int) $org['id'] ?>/bulk-enrol" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group"><label>Cohort</label>
        <select name="intake_id" required>
          <?php foreach ($cohorts as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['code']) ?> — <?= e($c['course_title']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Upload CSV (Name, Email)</label><input type="file" name="csv" accept=".csv"></div>
      <div class="form-group"><label>Or paste "Name, Email" — one per line</label><textarea name="roster_text" placeholder="Jane Doe, jane@org.com"></textarea></div>
      <button type="submit" class="btn btn-primary btn-sm">Enrol Staff</button>
    </form>
  </div>
</div>

<h2>Cohorts</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Code</th><th>Course</th><th>Starts</th><th>Seats</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($cohorts as $c): ?>
      <tr>
        <td><?= e($c['code']) ?></td><td><?= e($c['course_title']) ?></td><td><?= date_pretty($c['start_date']) ?></td>
        <td><?= (int) $c['seats_taken'] ?><?= $c['capacity'] ? ' / ' . (int) $c['capacity'] : '' ?></td>
        <td><span class="status status-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$cohorts): ?><tr><td colspan="5">No cohorts yet — convert a corporate request to create one.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
