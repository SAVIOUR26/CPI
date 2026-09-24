<?php /** @var array $enrollments */ /** @var array $invoices enrolment id => invoice */ ?>
<div class="dash-header"><h1>My Courses</h1></div>

<div class="table-card table-stack">
  <table>
    <thead><tr><th>Course</th><th>Intake</th><th>Status</th><th>Progress</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><strong><?= e($e['course_title']) ?></strong></td>
        <td data-label="Intake"><?= e($e['intake_code']) ?></td>
        <td data-label="Status"><span class="status status-<?= e($e['status']) ?>"><?= $e['status'] === 'pending_payment' ? 'Payment pending' : e(str_replace('_',' ',$e['status'])) ?></span></td>
        <td data-label="Progress"><?= (int) $e['progress_pct'] ?>%</td>
        <td class="cell-actions">
          <?php if (in_array($e['status'], ['active','completed'], true)): ?>
            <a href="/learner/courses/<?= (int) $e['intake_id'] ?>" class="btn btn-sm btn-outline">Open</a>
          <?php elseif ($e['status'] === 'pending_payment'): ?>
            <a href="<?= isset($invoices[(int) $e['id']]) ? '/learner/pay/' . (int) $invoices[(int) $e['id']]['id'] : '/learner/fees' ?>" class="btn btn-sm btn-primary">Pay fees</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$enrollments): ?><tr><td colspan="5">You haven't enrolled in anything yet. <a href="/courses">Browse courses</a>.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
