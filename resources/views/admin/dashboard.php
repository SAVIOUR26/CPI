<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Admin Dashboard</h1></div>

<div class="stat-row">
  <div class="stat-box"><div class="num"><?= $stats['active_enrollments'] ?></div><div class="label">Active enrolments</div></div>
  <div class="stat-box"><div class="num"><?= $stats['pending_payments'] ?></div><div class="label">Payments to review</div></div>
  <div class="stat-box"><div class="num"><?= $stats['new_corporate_requests'] ?></div><div class="label">New corporate requests</div></div>
  <div class="stat-box"><div class="num"><?= $stats['pending_admissions'] ?></div><div class="label">Pending admissions</div></div>
  <div class="stat-box"><div class="num"><?= money($stats['revenue_total']) ?></div><div class="label">Total revenue</div></div>
  <div class="stat-box"><div class="num"><?= $stats['published_courses'] ?></div><div class="label">Published courses</div></div>
</div>

<div class="grid-2">
  <div>
    <h2>Recent Payments</h2>
    <div class="table-card">
      <table>
        <thead><tr><th>Learner</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentPayments as $p): ?>
          <tr>
            <td><?= e($p['full_name'] ?? '—') ?></td>
            <td><?= money($p['amount'], $p['currency']) ?></td>
            <td><?= e(str_replace('_',' ',$p['method'])) ?></td>
            <td><span class="status status-<?= e($p['status']) ?>"><?= e(str_replace('_',' ',$p['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentPayments): ?><tr><td colspan="4">No payments yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <h2>Recent Corporate Requests</h2>
    <div class="table-card">
      <table>
        <thead><tr><th>Organization</th><th>Topic</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentRequests as $r): ?>
          <tr>
            <td><a href="/admin/corporate-requests/<?= (int) $r['id'] ?>"><?= e($r['organization_name']) ?></a></td>
            <td><?= e($r['topic']) ?></td>
            <td><span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentRequests): ?><tr><td colspan="3">No requests yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
