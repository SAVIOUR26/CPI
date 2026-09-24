<div class="welcome-banner">
  <div>
    <p class="eyebrow">Welcome back</p>
    <h1><?= e($auth_user['full_name']) ?></h1>
    <p>Pick up where you left off, or find your next course.</p>
  </div>
  <div class="actions">
    <a href="/courses" class="btn btn-gold btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Browse courses</a>
  </div>
  <i class="fa-solid fa-graduation-cap deco"></i>
</div>

<div class="stat-row">
  <a href="/learner/courses" class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-book-open-reader"></i></span><div><div class="num"><?= count($active) ?></div><div class="label">Active courses</div></div></a>
  <a href="/learner/fees" class="stat-box"><span class="stat-icon tone-orange"><i class="fa-solid fa-hourglass-half"></i></span><div><div class="num"><?= count($pending) ?></div><div class="label">Awaiting payment</div></div></a>
  <a href="/learner/certificates" class="stat-box"><span class="stat-icon tone-gold"><i class="fa-solid fa-award"></i></span><div><div class="num"><?= count($certificates) ?></div><div class="label">Certificates earned</div></div></a>
</div>

<?php if ($pending): ?>
<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-hourglass-half"></i> Complete your enrolment</h2><a href="/learner/fees">Fees &amp; Payments</a></div>
  <div class="table-card table-stack">
    <table>
      <thead><tr><th>Course</th><th>Intake</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($pending as $e): $inv = $invoices[(int) $e['id']] ?? null; ?>
        <tr>
          <td><strong><?= e($e['course_title']) ?></strong></td>
          <td data-label="Intake"><?= e($e['intake_code']) ?></td>
          <td data-label="Status"><span class="status status-pending_payment">Payment pending</span></td>
          <td class="cell-actions"><a href="<?= $inv ? '/learner/pay/' . (int) $inv['id'] : '/learner/fees' ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-mobile-screen-button"></i> Pay fees</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$appLabels = ['submitted' => 'Received', 'under_review' => 'Under review', 'admitted' => 'Admitted', 'rejected' => 'Not admitted'];
?>
<?php if ($applications): $app = $applications[0]; ?>
  <a href="/learner/admission" class="admission-strip">
    <span class="stat-icon tone-purple"><i class="fa-solid fa-building-columns"></i></span>
    <span><small>My admission · <?= e($app['application_no'] ?: '') ?></small><strong><?= e($app['programme_title']) ?></strong></span>
    <span class="status status-<?= e($app['status']) ?>"><?= e($appLabels[$app['status']] ?? $app['status']) ?></span>
    <i class="fa-solid fa-arrow-right"></i>
  </a>
<?php endif; ?>

<?php if ($announcements || $dates): ?>
<div class="grid-2 dash-duo">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> Announcements</h2><a href="/learner/announcements">View all</a></div>
    <div class="panel-body">
      <?php if ($announcements): ?>
        <?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements, 'showAudience' => true]); ?>
      <?php else: ?><p class="muted" style="margin:0">No announcements yet.</p><?php endif; ?>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-days"></i> Coming up</h2><a href="/learner/calendar">Full calendar</a></div>
    <div class="panel-body">
      <?php if ($dates): ?>
        <?php \App\Core\View::partial('partials.portal.dates', ['events' => $dates]); ?>
      <?php else: ?><p class="muted" style="margin:0">No upcoming dates.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<h2>My active courses</h2>
<div class="grid-3" style="margin-bottom:28px">
  <?php foreach ($active as $e): ?>
    <a href="/learner/courses/<?= (int) $e['intake_id'] ?>" class="card class-card">
      <span class="badge"><i class="fa-solid fa-layer-group"></i> <?= e($e['intake_code']) ?></span>
      <h3><?= e($e['course_title']) ?></h3>
      <div>
        <div class="progress-row"><span>Progress</span><span><?= (int) $e['progress_pct'] ?>%</span></div>
        <div class="progress"><span style="width:<?= max(2, min(100, (int) $e['progress_pct'])) ?>%"></span></div>
      </div>
      <span class="link-arrow">Open course room <i class="fa-solid fa-arrow-right"></i></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$active): ?>
    <div class="card empty-state"><i class="fa-solid fa-book-open"></i><h3>No active courses yet</h3><p><a href="/courses">Browse the catalogue</a> to get started.</p></div>
  <?php endif; ?>
</div>

<?php if ($timetable): ?>
<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-calendar-week"></i> Weekly timetable</h2><a href="/learner/calendar">Calendar</a></div>
  <?php \App\Core\View::partial('partials.portal.timetable', ['timetable' => $timetable, 'showCourse' => true]); ?>
</div>
<?php endif; ?>
