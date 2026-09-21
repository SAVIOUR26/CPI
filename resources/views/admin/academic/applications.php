<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Academic Admissions</h1></div>
<div class="table-card">
  <table>
    <thead><tr><th>Applicant</th><th>Programme</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($applications as $a): ?>
      <tr>
        <td><?= e($a['applicant_name']) ?> (<?= e($a['email']) ?>)</td>
        <td><?= e($a['programme_title']) ?></td>
        <td><span class="status status-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
        <td><a href="/admin/academic/applications/<?= (int) $a['id'] ?>" class="btn btn-sm btn-outline">Review</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$applications): ?><tr><td colspan="4">No applications yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
