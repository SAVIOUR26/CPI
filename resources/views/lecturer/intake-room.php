<?php
/** @var array $intake */ /** @var array $roster */ /** @var array $performance */ /** @var array $announcements */ /** @var array $timetable */
/** @var array $materials */ /** @var array $assignments */ /** @var array $quizzes */ /** @var array $discussions */
/** @var string $today */ /** @var array $attendanceMap */
use App\Support\Results;
use App\Support\Video;

$id = (int) $intake['id'];
$needsSupport = 0;
foreach ($roster as $r) {
    $p = $performance[$r['id']] ?? null;
    if (($p['average'] ?? null) !== null && $p['average'] < 50 || ($p['attendance'] ?? null) !== null && $p['attendance'] < 75) {
        $needsSupport++;
    }
}
$sections = ['students' => 'Students', 'announcements' => 'Announcements', 'materials' => 'Materials & videos', 'attendance' => 'Attendance',
             'assignments' => 'Assignments', 'quizzes' => 'Quizzes & exams', 'grades' => 'Grades', 'discussion' => 'Discussion'];
?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><a href="/lecturer"><i class="fa-solid fa-arrow-left"></i> My classes</a> · <?= e($intake['code']) ?></p>
    <h1><?= e($intake['course_title']) ?></h1>
    <p><?= e(\App\Support\Institute::modeLabel($intake['mode'], true)) ?> · starts <?= date_pretty($intake['start_date']) ?><?= $intake['venue'] ? ' · ' . e($intake['venue']) : '' ?></p>
  </div>
</div>

<div class="stat-row">
  <div class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-users"></i></span><div><div class="num"><?= count($roster) ?></div><div class="label">Students</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-orange"><i class="fa-solid fa-triangle-exclamation"></i></span><div><div class="num"><?= $needsSupport ?></div><div class="label">May need support</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-folder-open"></i></span><div><div class="num"><?= count($materials) ?></div><div class="label">Materials &amp; videos</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-purple"><i class="fa-solid fa-list-check"></i></span><div><div class="num"><?= count($assignments) + count($quizzes) ?></div><div class="label">Assessments</div></div></div>
</div>

<nav class="section-nav" aria-label="Class sections">
  <?php foreach ($sections as $sid => $label): ?><a href="#<?= $sid ?>"><?= e($label) ?></a><?php endforeach; ?>
</nav>

<div class="panel" id="students">
  <div class="panel-head"><h2><i class="fa-solid fa-users"></i> Students &amp; performance</h2>
    <span class="muted" style="font-size:.82rem">Average across assessments · attendance of marked sessions</span></div>
  <div class="table-card">
    <table>
      <thead><tr><th>Student</th><th>Enrolment</th><th>Average</th><th>Attendance</th><th>Progress</th><th>Standing</th></tr></thead>
      <tbody>
      <?php foreach ($roster as $r): ?>
        <?php $p = $performance[$r['id']] ?? ['average' => null, 'assessed' => 0, 'attendance' => null, 'sessions' => 0];
              [$band, $bandClass] = Results::band($p['average']);
              $lowAttendance = $p['attendance'] !== null && $p['attendance'] < 75; ?>
        <tr>
          <td><div class="cell-person"><span class="avatar"><?= e(initials($r['full_name'])) ?></span><div><strong><?= e($r['full_name']) ?></strong><span class="cell-sub"><?= e($r['email']) ?></span></div></div></td>
          <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
          <td><?= $p['average'] !== null ? '<span class="score-pill ' . e($bandClass) . '">' . $p['average'] . '%</span><span class="cell-sub">' . (int) $p['assessed'] . ' mark' . ($p['assessed'] === 1 ? '' : 's') . '</span>' : '<span class="muted">—</span>' ?></td>
          <td><?= $p['attendance'] !== null ? '<span class="score-pill ' . ($lowAttendance ? 'failed' : 'active') . '">' . $p['attendance'] . '%</span><span class="cell-sub">' . (int) $p['sessions'] . ' session' . ($p['sessions'] === 1 ? '' : 's') . '</span>' : '<span class="muted">—</span>' ?></td>
          <td><?= (int) $r['progress_pct'] ?>%</td>
          <td><?= $lowAttendance && ($p['average'] === null || $p['average'] >= 50) ? '<span class="status status-late">Low attendance</span>' : '<span class="status status-' . e($bandClass) . '">' . e($band) . '</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$roster): ?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-users"></i>No students enrolled yet.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel" id="announcements">
  <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> Class announcements</h2></div>
  <div class="panel-body">
    <form method="post" action="/lecturer/classes/<?= $id ?>/announcements" class="announce-form">
      <?= csrf_field() ?>
      <div class="form-group"><label for="ann_title">Title</label><input id="ann_title" type="text" name="title" maxlength="190" required placeholder="e.g. Assignment 2 deadline moved to Friday"></div>
      <div class="form-group"><label for="ann_body">Message</label><textarea id="ann_body" name="body" rows="3" required></textarea></div>
      <div class="announce-form-foot">
        <label class="check"><input type="checkbox" name="pinned" value="1"> Pin to the top</label>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-bullhorn"></i> Post to the class</button>
      </div>
    </form>
    <?php if ($announcements): ?>
      <?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements,
        'deleteUrl' => fn ($a) => (int) $a['created_by'] === (int) \App\Core\Auth::id() ? '/lecturer/announcements/' . (int) $a['id'] . '/delete' : null]); ?>
    <?php endif; ?>
  </div>
</div>

<?php if ($timetable): ?>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-week"></i> Class times</h2></div>
    <?php \App\Core\View::partial('partials.portal.timetable', ['timetable' => $timetable]); ?>
  </div>
<?php endif; ?>

<div class="grid-2 dash-duo" id="materials">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-upload"></i> Add material or video</h2></div>
    <div class="panel-body">
      <form method="post" action="/lecturer/classes/<?= $id ?>/materials" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><label for="mat_title">Title</label><input id="mat_title" type="text" name="title" required></div>
        <div class="form-group"><label for="mat_type">Type</label>
          <select id="mat_type" name="type"><option value="note">Document (PDF, Word, PowerPoint…)</option><option value="video">Lecture video</option><option value="link">Link or note</option></select>
        </div>
        <div class="form-group"><label for="mat_file">File (for documents)</label><input id="mat_file" type="file" name="file"></div>
        <div class="form-group"><label for="mat_url">Video or web link</label><input id="mat_url" type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=…">
          <p class="help-text">Upload lecture videos to YouTube (as <strong>Unlisted</strong>), Vimeo or Google Drive and paste the link — they play
            inside the class page. Video files are too large for the website's hosting.</p></div>
        <div class="form-group"><label for="mat_body">Description (optional)</label><textarea id="mat_body" name="body" rows="2"></textarea></div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add to class</button>
      </form>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-folder-open"></i> Posted (<?= count($materials) ?>)</h2></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Title</th><th>Type</th></tr></thead>
        <tbody>
        <?php foreach ($materials as $m): ?>
          <tr>
            <td><strong><?= e($m['title']) ?></strong><span class="cell-sub"><?= date_pretty($m['created_at']) ?></span></td>
            <td><?php if ($m['type'] === 'video'): ?><?= Video::embedUrl($m['video_url']) ? '<span class="status status-active">Video · plays in page</span>' : '<span class="status status-pending">Video · opens link</span>' ?>
              <?php else: ?><?= $m['type'] === 'link' ? 'Link / note' : 'Document' ?><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$materials): ?><tr><td colspan="2"><div class="empty-state"><i class="fa-solid fa-folder-open"></i>Nothing posted yet.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="panel" id="attendance">
  <div class="panel-head"><h2><i class="fa-solid fa-clipboard-user"></i> Attendance — <?= date_pretty($today, 'l j M Y') ?></h2></div>
  <div class="panel-body">
    <?php if ($roster): ?>
      <form method="post" action="/lecturer/classes/<?= $id ?>/attendance">
        <?= csrf_field() ?>
        <input type="hidden" name="session_date" value="<?= e($today) ?>">
        <div class="attendance-grid">
          <?php foreach ($roster as $r): ?>
            <?php $current = $attendanceMap[$r['id']] ?? 'present'; ?>
            <label class="attendance-row"><span><?= e($r['full_name']) ?></span>
              <select name="status[<?= (int) $r['id'] ?>]" aria-label="Attendance for <?= e($r['full_name']) ?>">
                <?php foreach (['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'excused' => 'Excused'] as $val => $label): ?>
                  <option value="<?= $val ?>"<?= $current === $val ? ' selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select></label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Save attendance</button>
      </form>
    <?php else: ?><p class="muted" style="margin:0">No students to mark yet.</p><?php endif; ?>
  </div>
</div>

<div class="grid-2 dash-duo" id="assignments">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-file-lines"></i> Assignments</h2></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Title</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($assignments as $a): ?>
          <tr>
            <td><strong><?= e($a['title']) ?></strong><span class="cell-sub">Out of <?= rtrim(rtrim((string) $a['max_score'], '0'), '.') ?></span></td>
            <td><?= $a['due_at'] ? date_pretty($a['due_at'], 'j M Y') : '—' ?></td>
            <td class="cell-actions"><a href="/lecturer/assignments/<?= (int) $a['id'] ?>/submissions" class="btn btn-sm btn-outline">Submissions</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$assignments): ?><tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-file-lines"></i>No assignments yet.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-plus"></i> New assignment</h2></div>
    <div class="panel-body">
      <form method="post" action="/lecturer/classes/<?= $id ?>/assignments">
        <?= csrf_field() ?>
        <div class="form-group"><label for="as_title">Title</label><input id="as_title" type="text" name="title" required></div>
        <div class="form-group"><label for="as_instr">Instructions</label><textarea id="as_instr" name="instructions" rows="3"></textarea></div>
        <div class="form-row">
          <div class="form-group"><label for="as_max">Marked out of</label><input id="as_max" type="number" name="max_score" value="100" min="1"></div>
          <div class="form-group"><label for="as_due">Due date</label><input id="as_due" type="date" name="due_at"></div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Create assignment</button>
      </form>
    </div>
  </div>
</div>

<div class="grid-2 dash-duo" id="quizzes">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-list-check"></i> Quizzes &amp; exams</h2></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($quizzes as $q): ?>
          <tr>
            <td><strong><?= e($q['title']) ?></strong><span class="cell-sub"><?= (int) $q['max_attempts'] ?> attempt<?= (int) $q['max_attempts'] === 1 ? '' : 's' ?><?= $q['time_limit_minutes'] ? ' · ' . (int) $q['time_limit_minutes'] . ' min' : '' ?></span></td>
            <td><span class="status status-<?= $q['is_exam'] ? 'reviewing' : 'open' ?>"><?= $q['is_exam'] ? 'Exam' : 'Quiz' ?></span></td>
            <td class="cell-actions"><a href="/lecturer/quizzes/<?= (int) $q['id'] ?>" class="btn btn-sm btn-outline">Questions &amp; results</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$quizzes): ?><tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-list-check"></i>No quizzes or exams yet.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-plus"></i> New quiz or exam</h2></div>
    <div class="panel-body">
      <form method="post" action="/lecturer/classes/<?= $id ?>/quizzes">
        <?= csrf_field() ?>
        <div class="form-group"><label for="qz_title">Title</label><input id="qz_title" type="text" name="title" required></div>
        <div class="form-row">
          <div class="form-group"><label for="qz_time">Time limit (minutes)</label><input id="qz_time" type="number" name="time_limit_minutes" min="1"></div>
          <div class="form-group"><label for="qz_att">Attempts allowed</label><input id="qz_att" type="number" name="max_attempts" value="1" min="1"></div>
        </div>
        <label class="check" style="display:flex;margin-bottom:16px"><input type="checkbox" name="is_exam" value="1"> This is a formal exam</label>
        <button type="submit" class="btn btn-primary btn-sm">Create</button>
      </form>
    </div>
  </div>
</div>

<div class="panel" id="grades">
  <div class="panel-head"><h2><i class="fa-solid fa-marker"></i> Record a grade</h2><span class="muted" style="font-size:.82rem">For tests and coursework marked outside the portal</span></div>
  <div class="panel-body">
    <?php if ($roster): ?>
      <form method="post" action="/lecturer/classes/<?= $id ?>/grades" class="grade-form">
        <?= csrf_field() ?>
        <div class="form-group"><label for="gr_user">Student</label>
          <select id="gr_user" name="user_id" required><?php foreach ($roster as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e($r['full_name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label for="gr_comp">Assessment</label><input id="gr_comp" type="text" name="component" maxlength="100" placeholder="e.g. Mid-term test" required></div>
        <div class="form-group"><label for="gr_score">Score</label><input id="gr_score" type="number" step="0.01" min="0" name="score" required></div>
        <div class="form-group"><label for="gr_max">Out of</label><input id="gr_max" type="number" step="0.01" min="1" name="max_score" value="100" required></div>
        <button type="submit" class="btn btn-primary btn-sm">Save grade</button>
      </form>
    <?php else: ?><p class="muted" style="margin:0">No students to grade yet.</p><?php endif; ?>
  </div>
</div>

<div class="panel" id="discussion">
  <div class="panel-head"><h2><i class="fa-solid fa-comments"></i> Class discussion</h2></div>
  <div class="panel-body">
    <ul class="discussion-list">
      <?php foreach ($discussions as $d): ?>
        <li><span class="avatar"><?= e(initials($d['full_name'])) ?></span>
          <div><strong><?= e($d['full_name']) ?></strong> <small><?= date_pretty($d['created_at'], 'j M, H:i') ?></small><p><?= nl2br(e($d['body'])) ?></p></div></li>
      <?php endforeach; ?>
    </ul>
    <?php if (!$discussions): ?><p class="muted">No messages yet.</p><?php endif; ?>
    <form method="post" action="/lecturer/classes/<?= $id ?>/discussion">
      <?= csrf_field() ?>
      <div class="form-group"><textarea name="body" rows="3" placeholder="Message the class…" required></textarea></div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i> Post</button>
    </form>
  </div>
</div>
