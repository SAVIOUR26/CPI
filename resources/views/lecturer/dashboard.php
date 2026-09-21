<?php $sidebar = 'partials.sidebar-lecturer'; ?>
<div class="dash-header"><h1>My Classes</h1></div>
<div class="grid-3">
  <?php foreach ($intakes as $i): ?>
    <a href="/lecturer/classes/<?= (int) $i['id'] ?>" class="card course-card">
      <span class="badge"><?= e($i['code']) ?></span>
      <h3><?= e($i['course_title']) ?></h3>
      <p>Starts <?= date_pretty($i['start_date']) ?> &middot; <?= e(ucfirst(str_replace('_',' ',$i['mode']))) ?></p>
      <p class="help-text">Seats: <?= (int) $i['seats_taken'] ?><?= $i['capacity'] ? ' / ' . (int) $i['capacity'] : '' ?></p>
    </a>
  <?php endforeach; ?>
  <?php if (!$intakes): ?><p>You have no assigned classes yet.</p><?php endif; ?>
</div>
