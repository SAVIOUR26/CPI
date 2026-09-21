<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header">
  <h1>Courses &amp; Intakes</h1>
  <a href="/admin/courses/create" class="btn btn-primary">New Course</a>
</div>
<div class="table-card">
  <table>
    <thead><tr><th>Title</th><th>Category</th><th>Type</th><th>Status</th><th>Public</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= e($c['title']) ?></td>
        <td><?= e($c['category_name'] ?? '—') ?></td>
        <td><?= e(str_replace('_',' ',$c['programme_type'])) ?></td>
        <td><span class="status status-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
        <td><?= $c['is_public'] ? 'Yes' : 'No' ?></td>
        <td><a href="/admin/courses/<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline">Manage</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$courses): ?><tr><td colspan="6">No courses yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
