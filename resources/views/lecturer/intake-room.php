<?php $sidebar = 'partials.sidebar-lecturer'; ?>
<div class="dash-header">
  <div><p class="eyebrow"><?= e($intake['code']) ?></p><h1><?= e($intake['course_title']) ?></h1></div>
</div>

<h2>Roster (<?= count($roster) ?>)</h2>
<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Progress</th></tr></thead>
    <tbody>
    <?php foreach ($roster as $r): ?>
      <tr><td><?= e($r['full_name']) ?></td><td><?= e($r['email']) ?></td>
        <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_',' ',$r['status'])) ?></span></td>
        <td><?= (int) $r['progress_pct'] ?>%</td></tr>
    <?php endforeach; ?>
    <?php if (!$roster): ?><tr><td colspan="4">No learners enrolled yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="grid-2" style="margin-bottom:28px">
  <div class="card">
    <h3>Add Material</h3>
    <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/materials" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
      <div class="form-group"><label>Type</label>
        <select name="type"><option value="note">Note / PDF</option><option value="video">Video link</option><option value="link">Link / text</option></select>
      </div>
      <div class="form-group"><label>Video URL (if video)</label><input type="text" name="video_url"></div>
      <div class="form-group"><label>File (if note)</label><input type="file" name="file"></div>
      <div class="form-group"><label>Notes / description</label><textarea name="body"></textarea></div>
      <button type="submit" class="btn btn-primary btn-sm">Add Material</button>
    </form>
  </div>

  <div class="card">
    <h3>Mark Attendance — <?= e($today) ?></h3>
    <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/attendance">
      <?= csrf_field() ?>
      <input type="hidden" name="session_date" value="<?= e($today) ?>">
      <?php foreach ($roster as $r): ?>
        <div class="form-group" style="display:flex;justify-content:space-between;align-items:center">
          <span><?= e($r['full_name']) ?></span>
          <select name="status[<?= (int) $r['id'] ?>]">
            <?php $current = $attendanceMap[$r['id']] ?? 'present'; ?>
            <option value="present" <?= $current==='present'?'selected':'' ?>>Present</option>
            <option value="absent" <?= $current==='absent'?'selected':'' ?>>Absent</option>
            <option value="late" <?= $current==='late'?'selected':'' ?>>Late</option>
            <option value="excused" <?= $current==='excused'?'selected':'' ?>>Excused</option>
          </select>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primary btn-sm">Save Attendance</button>
    </form>
  </div>
</div>

<h2>Assignments</h2>
<div class="grid-2" style="margin-bottom:16px">
  <?php foreach ($assignments as $a): ?>
    <div class="card">
      <h3><?= e($a['title']) ?></h3>
      <p class="help-text">Max score: <?= $a['max_score'] ?><?= $a['due_at'] ? ' &middot; Due ' . date_pretty($a['due_at'],'j M Y') : '' ?></p>
      <a href="/lecturer/assignments/<?= (int) $a['id'] ?>/submissions" class="btn btn-sm btn-outline">View Submissions</a>
    </div>
  <?php endforeach; ?>
</div>
<div class="card" style="margin-bottom:28px;max-width:520px">
  <h3>New Assignment</h3>
  <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/assignments">
    <?= csrf_field() ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
    <div class="form-group"><label>Instructions</label><textarea name="instructions"></textarea></div>
    <div class="grid-2">
      <div class="form-group"><label>Max score</label><input type="number" name="max_score" value="100"></div>
      <div class="form-group"><label>Due date</label><input type="date" name="due_at"></div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Create Assignment</button>
  </form>
</div>

<h2>Quizzes &amp; Exams</h2>
<div class="grid-2" style="margin-bottom:16px">
  <?php foreach ($quizzes as $q): ?>
    <div class="card">
      <h3><?= e($q['title']) ?></h3>
      <p class="help-text"><?= $q['is_exam'] ? 'Exam' : 'Quiz' ?> &middot; Max attempts: <?= (int) $q['max_attempts'] ?></p>
      <a href="/lecturer/quizzes/<?= (int) $q['id'] ?>" class="btn btn-sm btn-outline">Manage Questions &amp; Results</a>
    </div>
  <?php endforeach; ?>
</div>
<div class="card" style="margin-bottom:28px;max-width:520px">
  <h3>New Quiz</h3>
  <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/quizzes">
    <?= csrf_field() ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
    <div class="grid-2">
      <div class="form-group"><label>Time limit (minutes)</label><input type="number" name="time_limit_minutes"></div>
      <div class="form-group"><label>Max attempts</label><input type="number" name="max_attempts" value="1"></div>
    </div>
    <div class="form-group"><label><input type="checkbox" name="is_exam" value="1" style="width:auto;display:inline-block"> This is a formal exam</label></div>
    <button type="submit" class="btn btn-primary btn-sm">Create Quiz</button>
  </form>
</div>

<h2>Record a Grade</h2>
<div class="card" style="margin-bottom:28px;max-width:520px">
  <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/grades">
    <?= csrf_field() ?>
    <div class="form-group"><label>Learner</label>
      <select name="user_id" required>
        <?php foreach ($roster as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e($r['full_name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Component</label><input type="text" name="component" placeholder="e.g. final" required></div>
    <div class="grid-2">
      <div class="form-group"><label>Score</label><input type="number" step="0.01" name="score" required></div>
      <div class="form-group"><label>Max score</label><input type="number" step="0.01" name="max_score" value="100" required></div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Save Grade</button>
  </form>
</div>

<h2 id="discussion">Class Discussion</h2>
<div class="card">
  <?php foreach ($discussions as $d): ?>
    <p><strong><?= e($d['full_name']) ?>:</strong> <?= e($d['body']) ?> <span class="help-text"><?= date_pretty($d['created_at'], 'j M, g:i A') ?></span></p>
  <?php endforeach; ?>
  <?php if (!$discussions): ?><p class="help-text">No messages yet.</p><?php endif; ?>
  <form method="post" action="/lecturer/classes/<?= (int) $intake['id'] ?>/discussion">
    <?= csrf_field() ?>
    <div class="form-group"><textarea name="body" placeholder="Message the class…" required></textarea></div>
    <button type="submit" class="btn btn-primary btn-sm">Post</button>
  </form>
</div>
