<?php
/** @var array $intake */ /** @var array $announcements */ /** @var array $videos */ /** @var array $resources */
/** @var array $assignments */ /** @var array $submissions */ /** @var array $quizzes */ /** @var array $discussions */
/** @var array $results */ /** @var array $timetable */
use App\Support\Results;
use App\Support\Video;

[$bandLabel] = Results::band($results['average']);
$sections = array_filter([
  'announcements' => $announcements ? 'Announcements' : null,
  'videos' => $videos ? 'Videos' : null,
  'materials' => 'Materials',
  'assignments' => 'Assignments',
  'quizzes' => 'Quizzes & exams',
  'results' => 'Results',
  'discussion' => 'Discussion',
]);
?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><a href="/learner/courses"><i class="fa-solid fa-arrow-left"></i> My courses</a> · <?= e($intake['code']) ?></p>
    <h1><?= e($intake['course_title']) ?></h1>
    <p><?= e(\App\Support\Institute::modeLabel($intake['mode'], true)) ?> · starts <?= date_pretty($intake['start_date']) ?><?= $intake['venue'] ? ' · ' . e($intake['venue']) : '' ?></p>
  </div>
  <div class="actions"><a href="/learner/results" class="btn btn-outline btn-sm"><i class="fa-solid fa-square-poll-vertical"></i> All my results</a></div>
</div>

<div class="stat-row">
  <div class="stat-box"><span class="stat-icon tone-gold"><i class="fa-solid fa-chart-line"></i></span><div><div class="num"><?= $results['average'] !== null ? $results['average'] . '%' : '—' ?></div><div class="label">Average · <?= e($bandLabel) ?></div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-clipboard-user"></i></span><div><div class="num"><?= $results['attendance'] !== null ? $results['attendance'] . '%' : '—' ?></div><div class="label">Attendance<?= $results['sessions'] ? ' · ' . $results['sessions'] . ' session' . ($results['sessions'] === 1 ? '' : 's') : '' ?></div></div></div>
  <div class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-file-lines"></i></span><div><div class="num"><?= count($assignments) ?></div><div class="label">Assignments</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-purple"><i class="fa-solid fa-list-check"></i></span><div><div class="num"><?= count($quizzes) ?></div><div class="label">Quizzes &amp; exams</div></div></div>
</div>

<nav class="section-nav" aria-label="Class sections">
  <?php foreach ($sections as $id => $label): ?><a href="#<?= $id ?>"><?= e($label) ?></a><?php endforeach; ?>
</nav>

<?php if ($announcements): ?>
  <div class="panel" id="announcements">
    <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> Announcements</h2></div>
    <div class="panel-body"><?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements]); ?></div>
  </div>
<?php endif; ?>

<?php if ($timetable): ?>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-week"></i> Class times</h2><a href="/learner/calendar">Calendar</a></div>
    <?php \App\Core\View::partial('partials.portal.timetable', ['timetable' => $timetable]); ?>
  </div>
<?php endif; ?>

<?php if ($videos): ?>
  <div class="panel" id="videos">
    <div class="panel-head"><h2><i class="fa-solid fa-circle-play"></i> Lecture videos</h2><span class="muted" style="font-size:.84rem"><?= count($videos) ?> video<?= count($videos) === 1 ? '' : 's' ?></span></div>
    <div class="panel-body">
      <div class="video-grid">
        <?php foreach ($videos as $v): ?>
          <?php $embed = Video::embedUrl($v['video_url']); ?>
          <div class="video-card">
            <?php if ($embed): ?>
              <div class="video-frame"><iframe src="<?= e($embed) ?>" title="<?= e($v['title']) ?>" loading="lazy"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
            <?php else: ?>
              <div class="video-frame video-link"><i class="fa-solid fa-circle-play"></i></div>
            <?php endif; ?>
            <div class="vc-body">
              <strong><?= e($v['title']) ?></strong>
              <?php if ($v['body']): ?><p><?= nl2br(e($v['body'])) ?></p><?php endif; ?>
              <?php if (!$embed && Video::isWebUrl($v['video_url'])): ?>
                <a href="<?= e($v['video_url']) ?>" target="_blank" rel="noopener" class="link-arrow">Watch video <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="panel" id="materials">
  <div class="panel-head"><h2><i class="fa-solid fa-folder-open"></i> Lecture materials</h2></div>
  <div class="table-card">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($resources as $m): ?>
        <tr>
          <td><strong><?= e($m['title']) ?></strong><?php if ($m['body'] && $m['file_path']): ?><span class="cell-sub"><?= e(\App\Support\Str::limit($m['body'], 90)) ?></span><?php endif; ?></td>
          <td><?= $m['type'] === 'link' ? 'Link / note' : 'Document' ?></td>
          <td class="cell-actions">
            <?php if ($m['file_path']): ?>
              <a href="/learner/materials/<?= (int) $m['id'] ?>/download" class="btn btn-sm btn-outline"><i class="fa-solid fa-download"></i> Download</a>
            <?php elseif (\App\Support\Video::isWebUrl($m['video_url'] ?? '')): ?>
              <a href="<?= e($m['video_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Open</a>
            <?php elseif ($m['body']): ?>
              <span class="cell-sub" style="text-align:left;white-space:normal"><?= nl2br(e($m['body'])) ?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$resources): ?><tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-folder-open"></i>No materials posted yet.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<h2 id="assignments" class="section-title"><i class="fa-solid fa-file-lines"></i> Assignments</h2>
<div class="grid-2" style="margin-bottom:28px">
  <?php foreach ($assignments as $a): $sub = $submissions[$a['id']] ?? null; ?>
    <div class="card assignment-card">
      <h3><?= e($a['title']) ?></h3>
      <?php if ($a['instructions']): ?><p><?= nl2br(e(\App\Support\Str::limit($a['instructions'], 300))) ?></p><?php endif; ?>
      <p class="help-text"><?= $a['due_at'] ? '<i class="fa-regular fa-clock"></i> Due ' . date_pretty($a['due_at'], 'j M Y, H:i') . ' · ' : '' ?>Marked out of <?= rtrim(rtrim((string) $a['max_score'], '0'), '.') ?></p>
      <?php if ($sub): ?>
        <p><span class="status status-<?= $sub['score'] !== null ? 'graded' : 'submitted' ?>">
          <?= $sub['score'] !== null ? 'Graded: ' . rtrim(rtrim((string) $sub['score'], '0'), '.') . ' / ' . rtrim(rtrim((string) $a['max_score'], '0'), '.') : 'Submitted — awaiting grade' ?></span></p>
        <?php if (!empty($sub['feedback'])): ?><p class="feedback"><i class="fa-solid fa-comment-dots"></i> <?= nl2br(e($sub['feedback'])) ?></p><?php endif; ?>
      <?php endif; ?>
      <form method="post" action="/learner/assignments/<?= (int) $a['id'] ?>/submit" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><input type="file" name="file" aria-label="Your work"></div>
        <div class="form-group"><textarea name="notes" rows="2" placeholder="Notes for your lecturer (optional)"><?= e($sub['notes'] ?? '') ?></textarea></div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload"></i> <?= $sub ? 'Resubmit' : 'Submit' ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$assignments): ?><div class="card empty-state"><i class="fa-solid fa-file-lines"></i>No assignments yet.</div><?php endif; ?>
</div>

<div class="panel" id="quizzes">
  <div class="panel-head"><h2><i class="fa-solid fa-list-check"></i> Quizzes &amp; exams</h2></div>
  <div class="table-card">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th>Time limit</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($quizzes as $q): ?>
        <tr>
          <td><strong><?= e($q['title']) ?></strong></td>
          <td><span class="status status-<?= $q['is_exam'] ? 'reviewing' : 'open' ?>"><?= $q['is_exam'] ? 'Exam' : 'Quiz' ?></span></td>
          <td><?= $q['time_limit_minutes'] ? (int) $q['time_limit_minutes'] . ' min' : 'None' ?></td>
          <td class="cell-actions"><a href="/learner/quizzes/<?= (int) $q['id'] ?>" class="btn btn-sm btn-outline">Open</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$quizzes): ?><tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-list-check"></i>No quizzes or exams yet.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel" id="results">
  <div class="panel-head"><h2><i class="fa-solid fa-square-poll-vertical"></i> My results</h2>
    <?php if ($results['average'] !== null): ?><span class="score-pill <?= e(Results::band($results['average'])[1]) ?>">Average <?= $results['average'] ?>%</span><?php endif; ?></div>
  <?php \App\Core\View::partial('partials.portal.results-table', ['results' => $results]); ?>
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
    <?php if (!$discussions): ?><p class="muted">No messages yet — start the conversation.</p><?php endif; ?>
    <form method="post" action="/learner/courses/<?= (int) $intake['id'] ?>/discussion">
      <?= csrf_field() ?>
      <div class="form-group"><textarea name="body" rows="3" placeholder="Ask a question or share something with the class…" required></textarea></div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i> Post</button>
    </form>
  </div>
</div>
