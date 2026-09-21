<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1><?= e($quiz['title']) ?></h1></div>

<form method="post" action="/learner/quizzes/<?= (int) $quiz['id'] ?>/attempt/<?= (int) $attempt['id'] ?>">
  <?= csrf_field() ?>
  <?php foreach ($questions as $i => $q): ?>
    <div class="card" style="margin-bottom:16px">
      <p><strong>Q<?= $i + 1 ?>.</strong> <?= e($q['question']) ?> <span class="help-text">(<?= $q['points'] ?> pts)</span></p>
      <?php if ($q['type'] === 'single'): ?>
        <?php foreach ($q['options'] as $o): ?>
          <label style="display:block;font-weight:400;margin-bottom:6px">
            <input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= (int) $o['id'] ?>"> <?= e($o['option_text']) ?>
          </label>
        <?php endforeach; ?>
      <?php elseif ($q['type'] === 'multiple'): ?>
        <?php foreach ($q['options'] as $o): ?>
          <label style="display:block;font-weight:400;margin-bottom:6px">
            <input type="checkbox" name="answers[<?= (int) $q['id'] ?>][]" value="<?= (int) $o['id'] ?>"> <?= e($o['option_text']) ?>
          </label>
        <?php endforeach; ?>
      <?php else: ?>
        <textarea name="answers[<?= (int) $q['id'] ?>]" placeholder="Your answer"></textarea>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">Submit Answers</button>
</form>
