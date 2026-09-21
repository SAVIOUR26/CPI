<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1><?= e($quiz['title']) ?> — Result</h1></div>
<div class="card" style="max-width:420px">
  <?php if ($attempt['max_score'] > 0): ?>
    <div class="stat-box"><div class="num"><?= $attempt['score'] ?>/<?= $attempt['max_score'] ?></div><div class="label">Your score</div></div>
  <?php else: ?>
    <p>Submitted — awaiting grading.</p>
  <?php endif; ?>
  <a href="/learner/courses/<?= (int) $quiz['intake_id'] ?>" class="btn btn-outline" style="margin-top:14px">&larr; Back to course</a>
</div>
