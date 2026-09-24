<?php /** @var array $classes */ /** @var ?float $overall */ /** @var int $assessed */
use App\Support\Results;
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Student portal</p>
    <h1>My results</h1>
    <p>Marks for assignments, quizzes, exams and other assessments in all your classes.</p>
  </div>
  <div class="actions no-print"><button type="button" class="btn btn-outline btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button></div>
</div>

<div class="stat-row">
  <div class="stat-box"><span class="stat-icon tone-gold"><i class="fa-solid fa-chart-line"></i></span><div><div class="num"><?= $overall !== null ? $overall . '%' : '—' ?></div><div class="label">Overall average</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-book-open-reader"></i></span><div><div class="num"><?= count($classes) ?></div><div class="label">Classes</div></div></div>
  <div class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-square-check"></i></span><div><div class="num"><?= (int) $assessed ?></div><div class="label">Marks recorded</div></div></div>
</div>

<?php foreach ($classes as $c): $r = $c['results']; ?>
  <div class="panel">
    <div class="panel-head">
      <h2><i class="fa-solid fa-book-open-reader"></i> <?= e($c['course_title']) ?> <span class="cell-sub" style="display:inline;margin-left:6px"><?= e($c['intake_code']) ?></span></h2>
      <div class="result-meta">
        <?php if ($r['attendance'] !== null): ?><span class="muted"><i class="fa-solid fa-clipboard-user"></i> Attendance <?= $r['attendance'] ?>%</span><?php endif; ?>
        <?php if ($r['average'] !== null): ?><span class="score-pill <?= e(Results::band($r['average'])[1]) ?>">Average <?= $r['average'] ?>%</span><?php endif; ?>
      </div>
    </div>
    <?php \App\Core\View::partial('partials.portal.results-table', ['results' => $r]); ?>
  </div>
<?php endforeach; ?>

<?php if (!$classes): ?>
  <div class="card empty-state"><i class="fa-solid fa-square-poll-vertical"></i><h3>No results yet</h3><p>Your marks appear here once you are studying and your work has been graded.</p></div>
<?php endif; ?>
