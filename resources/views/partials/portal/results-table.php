<?php
/** @var array $results from App\Support\Results::forStudent */
$kinds = ['assignment' => 'Assignment', 'quiz' => 'Quiz', 'exam' => 'Exam', 'grade' => 'Assessment'];
?>
<div class="table-card">
  <table>
    <thead><tr><th>Assessment</th><th>Type</th><th>Score</th><th>%</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($results['rows'] as $r): ?>
      <tr>
        <td><strong><?= e($r['title']) ?></strong></td>
        <td><?= e($kinds[$r['kind']] ?? 'Assessment') ?></td>
        <td><?= rtrim(rtrim(number_format($r['score'], 2), '0'), '.') ?> / <?= rtrim(rtrim(number_format($r['max'], 2), '0'), '.') ?></td>
        <td><?= $r['pct'] !== null ? '<span class="score-pill ' . e(\App\Support\Results::band($r['pct'])[1]) . '">' . $r['pct'] . '%</span>' : '—' ?></td>
        <td><?= $r['date'] ? date_pretty($r['date']) : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$results['rows']): ?>
      <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-square-poll-vertical"></i>No marks yet. They appear here once your work is graded.</div></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
