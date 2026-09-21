<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1><?= e($quiz['title']) ?></h1></div>
<div class="card" style="max-width:520px">
  <p><?= $quiz['is_exam'] ? 'This is a formal exam.' : 'This is a practice quiz.' ?></p>
  <?php if ($quiz['time_limit_minutes']): ?><p>Time limit: <?= (int) $quiz['time_limit_minutes'] ?> minutes.</p><?php endif; ?>
  <p>Attempts used: <?= $attemptsUsed ?> / <?= (int) $quiz['max_attempts'] ?></p>

  <?php if ($ongoingAttempt): ?>
    <a href="/learner/quizzes/<?= (int) $quiz['id'] ?>/attempt/<?= (int) $ongoingAttempt['id'] ?>" class="btn btn-primary">Continue Attempt</a>
  <?php elseif ($attemptsUsed < (int) $quiz['max_attempts']): ?>
    <form method="post" action="/learner/quizzes/<?= (int) $quiz['id'] ?>/start">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-primary">Start Attempt</button>
    </form>
  <?php else: ?>
    <p class="help-text">You have used all your attempts.</p>
  <?php endif; ?>
  <a href="/learner/courses/<?= (int) $quiz['intake_id'] ?>" style="display:inline-block;margin-top:10px">&larr; Back to course</a>
</div>
