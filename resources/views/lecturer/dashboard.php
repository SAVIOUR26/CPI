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

<div class="grid-3">
  <?php foreach ($intakes as $i): ?>
    <a href="/lecturer/classes/<?= (int) $i['id'] ?>" class="card class-card">
      <span class="badge"><i class="fa-solid fa-layer-group"></i> <?= e($i['code']) ?></span>
      <h3><?= e($i['course_title']) ?></h3>
      <div class="class-meta">
        <span><i class="fa-regular fa-calendar"></i><?= date_pretty($i['start_date']) ?></span>
        <span><i class="fa-solid fa-location-dot"></i><?= e(ucfirst(str_replace('_', ' ', $i['mode']))) ?></span>
        <span><i class="fa-solid fa-users"></i><?= (int) $i['seats_taken'] ?><?= $i['capacity'] ? ' / ' . (int) $i['capacity'] : '' ?> seats</span>
      </div>
      <span class="link-arrow">Open class <i class="fa-solid fa-arrow-right"></i></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$intakes): ?>
    <div class="card empty-state"><i class="fa-solid fa-chalkboard"></i><h3>No classes assigned yet</h3><p>An administrator will assign you to an intake.</p></div>
  <?php endif; ?>
</div>
