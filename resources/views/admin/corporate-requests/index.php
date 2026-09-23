<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Corporate Requests</h1></div>
<div class="table-card">
  <table>
    <thead><tr><th>Organization</th><th>Topic</th><th>Headcount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($requests as $r): ?>
      <tr>
        <td><?= e($r['organization_name']) ?></td>
        <td><?= e($r['topic']) ?></td>
        <td><?= e((string) ($r['headcount'] ?? '—')) ?></td>
        <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
        <td><a href="/admin/corporate-requests/<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$requests): ?><tr><td colspan="5">No requests yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
