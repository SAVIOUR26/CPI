<?php $sidebar = 'partials.sidebar-admin'; ?>
<div class="dash-header"><h1><?= e($cr['organization_name']) ?></h1></div>

<div class="grid-2">
  <div class="card">
    <h3>Request Details</h3>
    <p><strong>Contact:</strong> <?= e($cr['contact_name']) ?> (<?= e($cr['contact_email']) ?>, <?= e($cr['contact_phone'] ?? '—') ?>)</p>
    <p><strong>Topic:</strong> <?= e($cr['topic']) ?></p>
    <p><strong>Headcount:</strong> <?= e((string) ($cr['headcount'] ?? '—')) ?></p>
    <p><strong>Mode:</strong> <?= e(str_replace('_',' ',$cr['mode'])) ?> &middot; <strong>Location:</strong> <?= e($cr['location'] ?? '—') ?></p>
    <p><strong>Preferred dates:</strong> <?= e($cr['preferred_dates'] ?? '—') ?></p>
    <p><strong>Budget note:</strong> <?= e($cr['budget_note'] ?? '—') ?></p>
    <p><strong>Message:</strong><br><?= nl2br(e($cr['message'] ?? '—')) ?></p>
    <p><strong>Status:</strong> <span class="status status-<?= e($cr['status']) ?>"><?= e($cr['status']) ?></span></p>
    <?php if ($cr['quote_pdf_path']): ?><p><a href="/admin/corporate-requests/<?= (int)$cr['id'] ?>/quote-file">View uploaded quote</a></p><?php endif; ?>
  </div>

  <div>
    <div class="card" style="margin-bottom:16px">
      <h3>Update Status</h3>
      <form method="post" action="/admin/corporate-requests/<?= (int) $cr['id'] ?>/status">
        <?= csrf_field() ?>
        <div class="form-group">
          <select name="status">
            <?php foreach (['new','reviewing','quoted','accepted','declined'] as $s): ?>
              <option value="<?= $s ?>" <?= $cr['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Update</button>
      </form>
    </div>

    <div class="card" style="margin-bottom:16px">
      <h3>Upload Quote</h3>
      <form method="post" action="/admin/corporate-requests/<?= (int) $cr['id'] ?>/quote" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><input type="file" name="quote" accept=".pdf" required></div>
        <button type="submit" class="btn btn-outline btn-sm">Upload &amp; Notify Client</button>
      </form>
    </div>

    <?php if ($cr['status'] !== 'converted'): ?>
    <div class="card">
      <h3>Convert to Cohort</h3>
      <form method="post" action="/admin/corporate-requests/<?= (int) $cr['id'] ?>/convert">
        <?= csrf_field() ?>
        <div class="form-group"><label>Base course</label>
          <select name="course_id" required>
            <option value="">—</option>
            <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $cr['course_id']==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Existing organization (optional)</label>
          <select name="organization_id">
            <option value="">— Create new: <?= e($cr['organization_name']) ?> —</option>
            <?php foreach ($organizations as $o): ?><option value="<?= (int) $o['id'] ?>"><?= e($o['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Start date</label><input type="date" name="start_date"></div>
        <button type="submit" class="btn btn-primary btn-sm">Convert to Dedicated Cohort</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>
