<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Issue Certificates — <?= e($intake['course_title']) ?> (<?= e($intake['code']) ?>)</h1></div>

<form method="post" action="/admin/certificates/roster/<?= (int) $intake['id'] ?>">
  <?= csrf_field() ?>
  <div class="form-group" style="max-width:500px">
    <label>Certificate title (optional — defaults to "Certificate in &lt;course&gt;")</label>
    <input type="text" name="title" placeholder="Certificate in <?= e($intake['course_title']) ?>">
  </div>

  <div class="table-card" style="margin-bottom:20px">
    <table>
      <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.rosterCk').forEach(c=>c.checked=this.checked)"></th><th>Name</th><th>Email</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($roster as $r): ?>
        <tr>
          <td><input class="rosterCk" type="checkbox" name="user_ids[]" value="<?= (int) $r['id'] ?>" <?= $r['status']==='active' ? '' : 'disabled' ?>></td>
          <td><?= e($r['full_name']) ?></td>
          <td><?= e($r['email']) ?></td>
          <td><span class="status status-<?= e($r['status']) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$roster): ?><tr><td colspan="4">No enrolled learners.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <button type="submit" class="btn btn-primary">Issue Certificates to Selected</button>
</form>
