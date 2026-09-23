<div class="welcome-banner">
  <div>
    <p class="eyebrow">Corporate portal</p>
    <h1><?= e($orgs[0]['name'] ?? 'Your organization') ?></h1>
    <p>Track your staff cohorts, training schedules and invoices in one place.</p>
  </div>
  <div class="actions">
    <a href="/corporate/request" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus"></i> Request training</a>
  </div>
  <i class="fa-solid fa-building deco"></i>
</div>

<?php if (count($orgs) > 1): ?>
  <div style="margin-bottom:18px"><?php foreach ($orgs as $org): ?><span class="org-chip"><i class="fa-solid fa-building"></i><?= e($org['name']) ?></span><?php endforeach; ?></div>
<?php endif; ?>

<div class="stat-row">
  <a href="/corporate/portal/cohorts" class="stat-box"><span class="stat-icon tone-crimson"><i class="fa-solid fa-people-group"></i></span><div><div class="num"><?= count($cohorts) ?></div><div class="label">Cohorts</div></div></a>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-user-check"></i></span><div><div class="num"><?= array_sum(array_map(fn ($c) => (int) $c['seats_taken'], $cohorts)) ?></div><div class="label">Staff enrolled</div></div></div>
  <a href="/corporate/portal/invoices" class="stat-box"><span class="stat-icon tone-gold"><i class="fa-solid fa-file-invoice-dollar"></i></span><div><div class="num"><i class="fa-solid fa-arrow-right" style="font-size:1.1rem"></i></div><div class="label">View invoices</div></div></a>
</div>

<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-people-group"></i> Our cohorts</h2><a href="/corporate/portal/cohorts">View all</a></div>
  <div class="table-card">
    <table>
      <thead><tr><th>Cohort</th><th>Course</th><th>Starts</th><th>Seats</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($cohorts as $c): ?>
        <tr>
          <td><strong><?= e($c['code']) ?></strong></td>
          <td><?= e($c['course_title']) ?></td>
          <td><?= date_pretty($c['start_date']) ?></td>
          <td><?= (int) $c['seats_taken'] ?><?= $c['capacity'] ? ' / ' . (int) $c['capacity'] : '' ?></td>
          <td><span class="status status-<?= e($c['status']) ?>"><?= e(str_replace('_', ' ', $c['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$cohorts): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-people-group"></i>No cohorts yet. <a href="/corporate/request">Request training</a> to get started.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
