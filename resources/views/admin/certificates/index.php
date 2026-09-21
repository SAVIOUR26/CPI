<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Certificates</h1></div>

<div class="card" style="max-width:600px;margin-bottom:24px">
  <h3>Issue Certificates for an Intake</h3>
  <form method="get" action="/admin/certificates/roster">
    <div class="form-group">
      <label>Intake</label>
      <select id="intakeSelect" onchange="window.location='/admin/certificates/roster/'+this.value">
        <option value="">— Select an intake —</option>
        <?php foreach ($intakes as $i): ?>
          <option value="<?= (int) $i['id'] ?>"><?= e($i['course_title']) ?> — <?= e($i['code']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<h2>Issued Certificates</h2>
<div class="table-card">
  <table>
    <thead><tr><th>Code</th><th>Learner</th><th>Course</th><th>Issued</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($certificates as $c): ?>
      <tr>
        <td><?= e($c['code']) ?></td>
        <td><?= e($c['full_name']) ?></td>
        <td><?= e($c['course_title']) ?></td>
        <td><?= date_pretty($c['issued_at']) ?></td>
        <td>
          <a href="/verify/<?= e($c['code']) ?>" target="_blank" class="btn btn-sm btn-outline">Verify</a>
          <?php if (!$c['revoked']): ?>
            <form method="post" action="/admin/certificates/<?= (int) $c['id'] ?>/revoke" style="display:inline">
              <?= csrf_field() ?><button type="submit" class="btn btn-sm btn-outline">Revoke</button>
            </form>
          <?php else: ?><span class="status status-cancelled">Revoked</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$certificates): ?><tr><td colspan="5">No certificates issued yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
