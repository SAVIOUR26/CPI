<?php $sidebar = 'partials.sidebar-learner'; ?>
<div class="dash-header"><h1>My Fees</h1></div>
<div class="table-card">
  <table>
    <thead><tr><th>Description</th><th>Programme</th><th>Due</th><th>Paid</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($ledger as $f): ?>
      <tr>
        <td><?= e($f['description']) ?></td>
        <td><?= e($f['course_title']) ?> (<?= e($f['intake_code']) ?>)</td>
        <td><?= money($f['amount_due']) ?></td>
        <td><?= money($f['amount_paid']) ?></td>
        <td><?= money($f['amount_due'] - $f['amount_paid']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$ledger): ?><tr><td colspan="5">No academic fee entries on your account.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
