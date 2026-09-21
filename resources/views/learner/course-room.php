<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><?= e($intake['code']) ?></p>
    <h1><?= e($intake['course_title']) ?></h1>
  </div>
  <div class="stat-box" style="min-width:160px">
    <div class="num"><?= $attendanceRate ?>%</div>
    <div class="label">Attendance</div>
  </div>
</div>

<h2>Materials</h2>
<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($materials as $m): ?>
      <tr>
        <td><?= e($m['title']) ?></td>
        <td><?= e(ucfirst($m['type'])) ?></td>
        <td>
          <?php if ($m['type'] === 'video' && $m['video_url']): ?>
            <a href="<?= e($m['video_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Watch</a>
          <?php elseif ($m['file_path']): ?>
            <a href="/learner/materials/<?= (int) $m['id'] ?>/download" class="btn btn-sm btn-outline">Download</a>
          <?php elseif ($m['body']): ?>
            <span><?= e(\App\Support\Str::limit($m['body'], 80)) ?></span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$materials): ?><tr><td colspan="3">No materials posted yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h2>Assignments</h2>
<div class="grid-2" style="margin-bottom:28px">
  <?php foreach ($assignments as $a): $sub = $submissions[$a['id']] ?? null; ?>
    <div class="card">
      <h3><?= e($a['title']) ?></h3>
      <p><?= nl2br(e(\App\Support\Str::limit($a['instructions'], 200))) ?></p>
      <?php if ($a['due_at']): ?><p class="help-text">Due: <?= date_pretty($a['due_at'], 'j M Y, g:i A') ?></p><?php endif; ?>
      <?php if ($sub): ?>
        <p class="status status-<?= $sub['score'] !== null ? 'active' : 'pending' ?>">
          <?= $sub['score'] !== null ? 'Graded: ' . $sub['score'] . '/' . $a['max_score'] : 'Submitted — awaiting grade' ?>
        </p>
      <?php endif; ?>
      <form method="post" action="/learner/assignments/<?= (int) $a['id'] ?>/submit" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><input type="file" name="file"></div>
        <div class="form-group"><textarea name="notes" placeholder="Notes (optional)"><?= e($sub['notes'] ?? '') ?></textarea></div>
        <button type="submit" class="btn btn-primary btn-sm"><?= $sub ? 'Resubmit' : 'Submit' ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$assignments): ?><p>No assignments yet.</p><?php endif; ?>
</div>

<h2>Quizzes &amp; Exams</h2>
<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($quizzes as $q): ?>
      <tr>
        <td><?= e($q['title']) ?></td>
        <td><?= $q['is_exam'] ? 'Exam' : 'Quiz' ?></td>
        <td><a href="/learner/quizzes/<?= (int) $q['id'] ?>" class="btn btn-sm btn-outline">Open</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$quizzes): ?><tr><td colspan="3">No quizzes yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h2>Grades <?php if ($average !== null): ?><span class="badge">Average: <?= $average ?>%</span><?php endif; ?></h2>
<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Component</th><th>Score</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($grades as $g): ?>
      <tr><td><?= e($g['component']) ?></td><td><?= $g['score'] ?>/<?= $g['max_score'] ?></td><td><?= date_pretty($g['recorded_at']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$grades): ?><tr><td colspan="3">No grades recorded yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h2 id="discussion">Class Discussion</h2>
<div class="card">
  <?php foreach ($discussions as $d): ?>
    <p><strong><?= e($d['full_name']) ?>:</strong> <?= e($d['body']) ?> <span class="help-text"><?= date_pretty($d['created_at'], 'j M, g:i A') ?></span></p>
  <?php endforeach; ?>
  <?php if (!$discussions): ?><p class="help-text">No messages yet — start the conversation.</p><?php endif; ?>
  <form method="post" action="/learner/courses/<?= (int) $intake['id'] ?>/discussion">
    <?= csrf_field() ?>
    <div class="form-group"><textarea name="body" placeholder="Ask a question or share something with the class…" required></textarea></div>
    <button type="submit" class="btn btn-primary btn-sm">Post</button>
  </form>
</div>
