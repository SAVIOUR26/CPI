<div class="welcome-banner">
  <div>
    <p class="eyebrow">Lecturer portal</p>
    <h1>My Classes</h1>
    <p>Post materials, run quizzes, take attendance and grade your learners.</p>
  </div>
  <i class="fa-solid fa-chalkboard-user deco"></i>
</div>

<div class="stat-row">
  <div class="stat-box"><span class="stat-icon tone-crimson"><i class="fa-solid fa-chalkboard"></i></span><div><div class="num"><?= count($intakes) ?></div><div class="label">Assigned classes</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-users"></i></span><div><div class="num"><?= array_sum(array_map(fn ($i) => (int) $i['seats_taken'], $intakes)) ?></div><div class="label">Learners enrolled</div></div></div>
</div>

<?php if ($announcements || $dates): ?>
<div class="grid-2 dash-duo">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> From CPI</h2></div>
    <div class="panel-body">
      <?php if ($announcements): ?>
        <?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements]); ?>
      <?php else: ?><p class="muted" style="margin:0">No announcements for lecturers.</p><?php endif; ?>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-days"></i> Coming up</h2></div>
    <div class="panel-body">
      <?php if ($dates): ?>
        <?php \App\Core\View::partial('partials.portal.dates', ['events' => $dates]); ?>
      <?php else: ?><p class="muted" style="margin:0">No upcoming dates.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<h2>Your classes</h2>
<div class="grid-3">
  <?php foreach ($intakes as $i): ?>
    <a href="/lecturer/classes/<?= (int) $i['id'] ?>" class="card class-card">
      <span class="badge"><i class="fa-solid fa-layer-group"></i> <?= e($i['code']) ?></span>
      <h3><?= e($i['course_title']) ?></h3>
      <div class="class-meta">
        <span><i class="fa-regular fa-calendar"></i><?= date_pretty($i['start_date']) ?></span>
        <span><i class="fa-solid fa-location-dot"></i><?= e(\App\Support\Institute::modeLabel($i['mode'], true)) ?></span>
        <span><i class="fa-solid fa-users"></i><?= (int) $i['seats_taken'] ?><?= $i['capacity'] ? ' / ' . (int) $i['capacity'] : '' ?> seats</span>
      </div>
      <span class="link-arrow">Open class <i class="fa-solid fa-arrow-right"></i></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$intakes): ?>
    <div class="card empty-state"><i class="fa-solid fa-chalkboard"></i><h3>No classes assigned yet</h3><p>An administrator will assign you to an intake.</p></div>
  <?php endif; ?>
</div>
