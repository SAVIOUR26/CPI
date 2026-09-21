<?php $sidebar = 'partials.sidebar-lecturer'; ?>
<div class="dash-header"><h1><?= e($quiz['title']) ?></h1></div>

<h2>Questions</h2>
<?php foreach ($questions as $i => $q): ?>
  <div class="card" style="margin-bottom:12px">
    <p><strong>Q<?= $i + 1 ?>.</strong> <?= e($q['question']) ?> <span class="help-text">(<?= e($q['type']) ?>, <?= $q['points'] ?> pts)</span></p>
    <?php foreach ($q['options'] as $o): ?>
      <p style="margin:2px 0 2px 16px"><?= $o['is_correct'] ? '&#10003;' : '&#9675;' ?> <?= e($o['option_text']) ?></p>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>
<?php if (!$questions): ?><p>No questions yet — add one below.</p><?php endif; ?>

<div class="card" style="max-width:600px;margin-bottom:28px">
  <h3>Add Question</h3>
  <form method="post" action="/lecturer/quizzes/<?= (int) $quiz['id'] ?>/questions">
    <?= csrf_field() ?>
    <div class="form-group"><label>Question</label><textarea name="question" required></textarea></div>
    <div class="grid-2">
      <div class="form-group"><label>Type</label>
        <select name="type">
          <option value="single">Single choice</option>
          <option value="multiple">Multiple choice</option>
          <option value="short_text">Short answer (manually graded)</option>
        </select>
      </div>
      <div class="form-group"><label>Points</label><input type="number" step="0.5" name="points" value="1"></div>
    </div>
    <div class="form-group">
      <label>Options (one per line — for choice questions)</label>
      <textarea name="options" placeholder="Option A&#10;*Option B (correct)&#10;Option C"></textarea>
      <p class="help-text">Put a <strong>*</strong> at the start of each correct option's line, e.g. "*Option B".</p>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Add Question</button>
  </form>
</div>

<h2>Attempts (<?= count($attempts) ?>)</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Learner</th><th>Attempt</th><th>Score</th><th>Submitted</th></tr></thead>
    <tbody>
    <?php foreach ($attempts as $a): ?>
      <tr>
        <td><?= e($a['full_name']) ?></td>
        <td><?= (int) $a['attempt_no'] ?></td>
        <td><?= $a['score'] !== null ? $a['score'] . '/' . $a['max_score'] : 'In progress' ?></td>
        <td><?= $a['submitted_at'] ? date_pretty($a['submitted_at'], 'j M Y, g:i A') : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$attempts): ?><tr><td colspan="4">No attempts yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
