<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header">
  <div><p class="eyebrow">Welcome back</p><h1><?= e($auth_user['full_name']) ?></h1></div>
</div>

<div class="stat-row">
  <div class="stat-box"><div class="num"><?= count($active) ?></div><div class="label">Active courses</div></div>
  <div class="stat-box"><div class="num"><?= count($pending) ?></div><div class="label">Awaiting payment</div></div>
  <div class="stat-box"><div class="num"><?= count($certificates) ?></div><div class="label">Certificates</div></div>
</div>

<?php if ($pending): ?>
<div class="table-card" style="margin-bottom:24px">
  <table>
    <thead><tr><th>Course</th><th>Intake</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pending as $e): ?>
      <tr>
        <td><?= e($e['course_title']) ?></td>
        <td><?= e($e['intake_code']) ?></td>
        <td><span class="status status-pending_payment">Payment pending</span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<h2>My Active Courses</h2>
<div class="grid-3">
  <?php foreach ($active as $e): ?>
    <a href="/learner/courses/<?= (int) $e['intake_id'] ?>" class="card course-card">
      <span class="badge"><?= e($e['intake_code']) ?></span>
      <h3><?= e($e['course_title']) ?></h3>
      <p>Progress: <?= (int) $e['progress_pct'] ?>%</p>
    </a>
  <?php endforeach; ?>
  <?php if (!$active): ?><p>You have no active courses yet. <a href="/courses">Browse the catalogue</a> to get started.</p><?php endif; ?>
</div>

<?php if ($timetable): ?>
<h2 style="margin-top:30px">Upcoming Classes</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Course</th><th>Day</th><th>Time</th><th>Venue</th></tr></thead>
    <tbody>
    <?php foreach ($timetable as $t): ?>
      <tr>
        <td><?= e($t['course_title']) ?></td>
        <td><?= e(\App\Models\Timetable::dayName((int) $t['day_of_week'])) ?></td>
        <td><?= e(substr($t['start_time'],0,5)) ?>–<?= e(substr($t['end_time'],0,5)) ?></td>
        <td><?= e($t['venue'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
