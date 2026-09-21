<?php $sidebar = 'partials.sidebar-lecturer'; ?>
<div class="dash-header"><h1><?= e($assignment['title']) ?> — Submissions</h1></div>
<div class="table-card">
  <table>
    <thead><tr><th>Learner</th><th>Submitted</th><th>Notes</th><th>File</th><th>Score</th><th>Grade</th></tr></thead>
    <tbody>
    <?php foreach ($submissions as $s): ?>
      <tr>
        <td><?= e($s['full_name']) ?></td>
        <td><?= date_pretty($s['submitted_at'], 'j M Y, g:i A') ?></td>
        <td><?= e(\App\Support\Str::limit($s['notes'], 60)) ?></td>
        <td><?php if ($s['file_path']): ?><a href="/lecturer/submissions/<?= (int) $s['id'] ?>/file" target="_blank">View</a><?php endif; ?></td>
        <td><?= $s['score'] !== null ? $s['score'] . '/' . $assignment['max_score'] : '—' ?></td>
        <td>
          <form method="post" action="/lecturer/submissions/<?= (int) $s['id'] ?>/grade" style="display:flex;gap:6px">
            <?= csrf_field() ?>
            <input type="number" step="0.01" name="score" value="<?= e((string) $s['score']) ?>" style="width:80px" required>
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$submissions): ?><tr><td colspan="6">No submissions yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
