<?php
/** @var array $timetable timetable_entries rows (course_title optional) */
$showCourse = $showCourse ?? false;
?>
<div class="table-card">
  <table>
    <thead><tr><?php if ($showCourse): ?><th>Class</th><?php endif; ?><th>Day</th><th>Time</th><th>Venue</th></tr></thead>
    <tbody>
    <?php foreach ($timetable as $t): ?>
      <tr>
        <?php if ($showCourse): ?><td><strong><?= e($t['course_title'] ?? '') ?></strong><span class="cell-sub"><?= e($t['intake_code'] ?? '') ?></span></td><?php endif; ?>
        <td><?= e(\App\Models\Timetable::dayName((int) $t['day_of_week'])) ?></td>
        <td><?= e(substr($t['start_time'], 0, 5)) ?>–<?= e(substr($t['end_time'], 0, 5)) ?></td>
        <td><?= e($t['venue'] ?: '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
