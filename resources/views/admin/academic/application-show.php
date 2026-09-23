<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1><?= e($app['applicant_name']) ?></h1></div>

<div class="grid-2">
  <div class="card">
    <h3>Application</h3>
    <p><strong>Programme:</strong> <?= e($programme['title']) ?></p>
    <p><strong>Email:</strong> <?= e($app['email']) ?> &middot; <strong>Phone:</strong> <?= e($app['phone'] ?? '—') ?></p>
    <p><strong>Status:</strong> <span class="status status-<?= e($app['status']) ?>"><?= e(str_replace('_', ' ', $app['status'])) ?></span></p>
    <?php if ($app['documents_path']): ?><p><a href="/admin/academic/applications/<?= (int) $app['id'] ?>/documents" target="_blank">View submitted documents</a></p><?php endif; ?>
    <?php if ($app['decision_note']): ?><p><strong>Decision note:</strong> <?= nl2br(e($app['decision_note'])) ?></p><?php endif; ?>
  </div>

  <?php if (in_array($app['status'], ['submitted','under_review'], true)): ?>
  <div class="card">
    <h3>Make a Decision</h3>
    <form method="post" action="/admin/academic/applications/<?= (int) $app['id'] ?>/decide">
      <?= csrf_field() ?>
      <div class="form-group"><label>Decision</label>
        <select name="decision"><option value="admitted">Admit</option><option value="rejected">Reject</option></select>
      </div>
      <div class="form-group"><label>Assign to intake (if admitting)</label>
        <select name="intake_id">
          <option value="">—</option>
          <?php foreach ($intakes as $i): ?><option value="<?= (int) $i['id'] ?>"><?= e($i['code']) ?> (<?= date_pretty($i['start_date']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Note (sent internally)</label><textarea name="decision_note"></textarea></div>
      <button type="submit" class="btn btn-primary">Submit Decision</button>
    </form>
  </div>
  <?php endif; ?>
</div>
