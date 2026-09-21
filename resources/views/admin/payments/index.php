<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Payments</h1></div>

<h2>Awaiting Review (Bank Transfers)</h2>
<div class="table-card" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Learner</th><th>Amount</th><th>Proof</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pending as $p): ?>
      <tr>
        <td><?= e($p['full_name'] ?? '—') ?> (<?= e($p['email'] ?? '') ?>)</td>
        <td><?= money($p['amount'], $p['currency']) ?></td>
        <td><a href="/admin/payments/<?= (int) $p['id'] ?>/proof" target="_blank">View proof</a></td>
        <td>
          <form method="post" action="/admin/payments/<?= (int) $p['id'] ?>/confirm" style="display:inline">
            <?= csrf_field() ?><button type="submit" class="btn btn-sm btn-primary">Confirm</button>
          </form>
          <form method="post" action="/admin/payments/<?= (int) $p['id'] ?>/reject" style="display:inline">
            <?= csrf_field() ?><button type="submit" class="btn btn-sm btn-outline">Reject</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$pending): ?><tr><td colspan="4">Nothing awaiting review.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h2>Recent Payments</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Learner</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $p): ?>
      <tr>
        <td><?= e($p['full_name'] ?? '—') ?></td>
        <td><?= money($p['amount'], $p['currency']) ?></td>
        <td><?= e(str_replace('_',' ',$p['method'])) ?></td>
        <td><span class="status status-<?= e($p['status']) ?>"><?= e(str_replace('_',' ',$p['status'])) ?></span></td>
        <td><?= date_pretty($p['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
