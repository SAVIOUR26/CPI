<?php $sidebar = 'partials.sidebar-corporate'; ?>
<div class="dash-header"><h1>Corporate Portal</h1></div>
<?php foreach ($orgs as $org): ?><p class="badge"><?= e($org['name']) ?></p><?php endforeach; ?>

<h2>Our Cohorts</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Cohort</th><th>Course</th><th>Starts</th><th>Seats</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($cohorts as $c): ?>
      <tr>
        <td><?= e($c['code']) ?></td>
        <td><?= e($c['course_title']) ?></td>
        <td><?= date_pretty($c['start_date']) ?></td>
        <td><?= (int) $c['seats_taken'] ?><?= $c['capacity'] ? ' / ' . (int) $c['capacity'] : '' ?></td>
        <td><span class="status status-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$cohorts): ?><tr><td colspan="5">No cohorts yet. <a href="/corporate/request">Request training</a> to get started.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
