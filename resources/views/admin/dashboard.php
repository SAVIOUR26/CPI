<div class="welcome-banner">
  <div>
    <p class="eyebrow">Admin overview</p>
    <h1>Good <?= (int) date('G') < 12 ? 'morning' : ((int) date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e($auth_user['full_name'] ?? 'Admin') ?></h1>
    <p>Here's what needs your attention across CPI today.</p>
  </div>
  <div class="actions">
    <a href="/admin/courses/create" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus"></i> New course</a>
    <a href="/admin/payments" class="btn btn-ghost-light btn-sm"><i class="fa-solid fa-money-bill-wave"></i> Review payments</a>
  </div>
  <i class="fa-solid fa-shield-halved deco"></i>
</div>

<div class="stat-row cols-3">
  <a href="/admin/courses" class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-user-graduate"></i></span><div><div class="num"><?= (int) $stats['active_enrollments'] ?></div><div class="label">Active enrolments</div></div></a>
  <a href="/admin/payments" class="stat-box"><span class="stat-icon tone-orange"><i class="fa-solid fa-hourglass-half"></i></span><div><div class="num"><?= (int) $stats['pending_payments'] ?></div><div class="label">Payments to review</div></div></a>
  <a href="/admin/corporate-requests" class="stat-box"><span class="stat-icon tone-blue"><i class="fa-solid fa-handshake"></i></span><div><div class="num"><?= (int) $stats['new_corporate_requests'] ?></div><div class="label">New corporate requests</div></div></a>
  <a href="/admin/academic/applications" class="stat-box"><span class="stat-icon tone-purple"><i class="fa-solid fa-file-signature"></i></span><div><div class="num"><?= (int) $stats['pending_admissions'] ?></div><div class="label">Pending admissions</div></div></a>
  <a href="/admin/reports" class="stat-box"><span class="stat-icon tone-gold"><i class="fa-solid fa-sack-dollar"></i></span><div><div class="num"><?= money($stats['revenue_total']) ?></div><div class="label">Total revenue</div></div></a>
  <a href="/admin/courses" class="stat-box"><span class="stat-icon tone-crimson"><i class="fa-solid fa-book-open"></i></span><div><div class="num"><?= (int) $stats['published_courses'] ?></div><div class="label">Published courses</div></div></a>
</div>

<div class="grid-2">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-money-bill-wave"></i> Recent payments</h2><a href="/admin/payments">View all</a></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Learner</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentPayments as $p): ?>
          <tr>
            <td><?= e($p['full_name'] ?? '—') ?></td>
            <td><strong><?= money($p['amount'], $p['currency']) ?></strong></td>
            <td><?= e(\App\Models\Payment::methodLabel($p['method'])) ?></td>
            <?php [$statusLabel, $statusClass] = \App\Models\Payment::statusLabel($p['status']); ?>
            <td><span class="status status-<?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentPayments): ?><tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-receipt"></i>No payments yet.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-handshake"></i> Recent corporate requests</h2><a href="/admin/corporate-requests">View all</a></div>
    <div class="table-card">
      <table>
        <thead><tr><th>Organization</th><th>Topic</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentRequests as $r): ?>
          <tr>
            <td><a href="/admin/corporate-requests/<?= (int) $r['id'] ?>"><?= e($r['organization_name']) ?></a></td>
            <td><?= e($r['topic']) ?></td>
            <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentRequests): ?><tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-inbox"></i>No requests yet.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
