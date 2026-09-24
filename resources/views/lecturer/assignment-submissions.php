<?php
/** @var array $assignment */ /** @var array $submissions */
$max = rtrim(rtrim((string) $assignment['max_score'], '0'), '.');
?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><a href="/lecturer/classes/<?= (int) $assignment['intake_id'] ?>#assignments"><i class="fa-solid fa-arrow-left"></i> Back to the class</a></p>
    <h1><?= e($assignment['title']) ?></h1>
    <p>Submissions · marked out of <?= e($max) ?><?= $assignment['due_at'] ? ' · due ' . date_pretty($assignment['due_at'], 'j M Y') : '' ?>. Your score and feedback appear in the student's class page and results.</p>
  </div>
</div>
<div class="table-card">
  <table>
    <thead><tr><th>Student</th><th>Submitted</th><th>Work</th><th>Score</th><th>Grade and feedback</th></tr></thead>
    <tbody>
    <?php foreach ($submissions as $s): ?>
      <tr>
        <td><strong><?= e($s['full_name']) ?></strong></td>
        <td class="nowrap"><?= date_pretty($s['submitted_at'], 'j M Y, H:i') ?></td>
        <td><?php if ($s['file_path']): ?><a href="/lecturer/submissions/<?= (int) $s['id'] ?>/file" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip"></i> Open file</a><?php endif; ?>
          <?php if ($s['notes']): ?><span class="cell-sub"><?= e(\App\Support\Str::limit($s['notes'], 90)) ?></span><?php endif; ?></td>
        <td><?= $s['score'] !== null ? '<strong>' . e(rtrim(rtrim((string) $s['score'], '0'), '.')) . '</strong> / ' . e($max) : '<span class="muted">—</span>' ?></td>
        <td>
          <form method="post" action="/lecturer/submissions/<?= (int) $s['id'] ?>/grade" class="mark-form">
            <?= csrf_field() ?>
            <input type="number" step="0.01" min="0" max="<?= e((string) $assignment['max_score']) ?>" name="score" value="<?= e((string) $s['score']) ?>" required aria-label="Score for <?= e($s['full_name']) ?>">
            <input type="text" name="feedback" maxlength="1000" value="<?= e((string) ($s['feedback'] ?? '')) ?>" placeholder="Feedback (optional)" aria-label="Feedback for <?= e($s['full_name']) ?>">
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$submissions): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-inbox"></i>No submissions yet.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
