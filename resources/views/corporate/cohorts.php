<?php $sidebar = 'partials.sidebar-corporate'; ?>
<div class="dash-header"><h1>Our Cohorts</h1></div>
<?php foreach ($cohorts as $c): ?>
  <h2><?= e($c['course_title']) ?> — <?= e($c['code']) ?></h2>
  <div class="table-card" style="margin-bottom:24px">
    <table>
      <thead><tr><th>Staff Member</th><th>Email</th><th>Status</th><th>Progress</th></tr></thead>
      <tbody>
      <?php foreach ($c['roster'] as $r): ?>
        <tr>
          <td><?= e($r['full_name']) ?></td><td><?= e($r['email']) ?></td>
          <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_',' ',$r['status'])) ?></span></td>
          <td><?= (int) $r['progress_pct'] ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$c['roster']): ?><tr><td colspan="4">No staff enrolled yet — contact CPI Admissions to add your team.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
<?php if (!$cohorts): ?><p>No cohorts yet.</p><?php endif; ?>
