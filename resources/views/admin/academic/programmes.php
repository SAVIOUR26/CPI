<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1>Academic Programmes</h1></div>

<div class="table-card" style="margin-bottom:24px">
  <table>
    <thead><tr><th>Title</th><th>Award Level</th><th>Status</th><th>Apply Link</th></tr></thead>
    <tbody>
    <?php foreach ($programmes as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><?= e(ucfirst($p['award_level'])) ?></td>
        <td><span class="status status-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
        <td><a href="/academic/apply/<?= (int) $p['id'] ?>" target="_blank">/academic/apply/<?= (int) $p['id'] ?></a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$programmes): ?><tr><td colspan="4">No academic programmes yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card" style="max-width:640px">
  <h3>New Programme</h3>
  <form method="post" action="/admin/academic/programmes">
    <?= csrf_field() ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
    <div class="form-group"><label>Award level</label>
      <select name="award_level"><option value="certificate">Certificate</option><option value="diploma">Diploma</option><option value="degree">Degree</option></select>
    </div>
    <div class="form-group"><label>Summary</label><input type="text" name="summary"></div>
    <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
    <div class="form-group"><label>Duration note</label><input type="text" name="duration_note" placeholder="e.g. 2 years, part-time"></div>
    <div class="form-group"><label>Entry requirements</label><textarea name="entry_requirements"></textarea></div>
    <button type="submit" class="btn btn-primary btn-sm">Create Programme</button>
  </form>
</div>
