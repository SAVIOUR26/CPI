<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1>My Courses</h1></div>

<div class="table-card">
  <table>
    <thead><tr><th>Course</th><th>Intake</th><th>Status</th><th>Progress</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><?= e($e['course_title']) ?></td>
        <td><?= e($e['intake_code']) ?></td>
        <td><span class="status status-<?= e($e['status']) ?>"><?= e(str_replace('_',' ',$e['status'])) ?></span></td>
        <td><?= (int) $e['progress_pct'] ?>%</td>
        <td>
          <?php if (in_array($e['status'], ['active','completed'], true)): ?>
            <a href="/learner/courses/<?= (int) $e['intake_id'] ?>" class="btn btn-sm btn-outline">Open</a>
          <?php elseif ($e['status'] === 'pending_payment'): ?>
            <a href="/courses/<?= e($e['course_slug']) ?>" class="btn btn-sm btn-primary">Complete Payment</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$enrollments): ?><tr><td colspan="5">You haven't enrolled in anything yet. <a href="/courses">Browse courses</a>.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
