<?php $sidebar = 'partials.sidebar-admin'; $isEdit = $course !== null; ?>
<div class="dash-header"><h1><?= $isEdit ? 'Edit: ' . e($course['title']) : 'New Course' ?></h1></div>

<div class="card" style="max-width:760px;margin-bottom:28px">
  <form method="post" action="<?= $isEdit ? '/admin/courses/' . (int) $course['id'] : '/admin/courses' ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Title</label><input type="text" name="title" value="<?= e($course['title'] ?? old('title')) ?>" required></div>
    <div class="form-group"><label>Summary</label><input type="text" name="summary" value="<?= e($course['summary'] ?? '') ?>"></div>
    <div class="form-group"><label>Description</label><textarea name="description"><?= e($course['description'] ?? '') ?></textarea></div>

    <div class="grid-2">
      <div class="form-group"><label>Category</label>
        <select name="category_id">
          <option value="">—</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (($course['category_id'] ?? null) == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Programme type</label>
        <select name="programme_type">
          <?php foreach (['short_course'=>'Short course','corporate'=>'Corporate (unlisted/custom)','academic'=>'Academic'] as $val=>$label): ?>
            <option value="<?= $val ?>" <?= (($course['programme_type'] ?? 'short_course') === $val) ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Level</label>
        <select name="level">
          <?php foreach (['foundation','intermediate','advanced'] as $val): ?>
            <option value="<?= $val ?>" <?= (($course['level'] ?? 'foundation') === $val) ? 'selected' : '' ?>><?= ucfirst($val) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Duration note</label><input type="text" name="duration_note" value="<?= e($course['duration_note'] ?? '') ?>"></div>
      <div class="form-group"><label>Price amount</label><input type="number" step="0.01" name="price_amount" value="<?= e((string) ($course['price_amount'] ?? 0)) ?>"></div>
      <div class="form-group"><label>Currency</label><input type="text" name="price_currency" value="<?= e($course['price_currency'] ?? 'UGX') ?>"></div>
      <div class="form-group"><label>Status</label>
        <select name="status">
          <?php foreach (['draft','published','archived'] as $val): ?>
            <option value="<?= $val ?>" <?= (($course['status'] ?? 'draft') === $val) ? 'selected' : '' ?>><?= ucfirst($val) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label><input type="checkbox" name="is_public" value="1" style="width:auto;display:inline-block" <?= !$isEdit || !empty($course['is_public']) ? 'checked' : '' ?>> Show on public catalogue</label></div>
    </div>

    <div class="form-group">
      <label>Pillars</label>
      <?php foreach ($pillars as $p): ?>
        <label style="display:inline-block;margin-right:14px;font-weight:400">
          <input type="checkbox" name="pillars[]" value="<?= (int) $p['id'] ?>" style="width:auto;display:inline-block" <?= in_array($p['id'], $selectedPillars) ? 'checked' : '' ?>> <?= e($p['name']) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Course' ?></button>
  </form>
</div>

<?php if ($isEdit): ?>
  <h2>Intakes</h2>
  <div class="table-card" style="margin-bottom:24px">
    <table>
      <thead><tr><th>Code</th><th>Starts</th><th>Mode</th><th>Seats</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach (($intakes ?? []) as $i): ?>
        <tr>
          <td><?= e($i['code']) ?></td>
          <td><?= date_pretty($i['start_date']) ?></td>
          <td><?= e(\App\Support\Institute::modeLabel($i['mode'], true)) ?></td>
          <td><?= (int) $i['seats_taken'] ?><?= $i['capacity'] ? ' / ' . (int) $i['capacity'] : '' ?></td>
          <td>
            <form method="post" action="/admin/intakes/<?= (int) $i['id'] ?>/status" style="display:flex;gap:6px;flex-wrap:wrap">
              <?= csrf_field() ?>
              <select name="status">
                <?php foreach (['scheduled','open','closed','in_progress','completed','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $i['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
              <select name="primary_lecturer_id">
                <option value="">No lecturer</option>
                <?php foreach (($lecturers ?? []) as $l): ?>
                  <option value="<?= (int) $l['id'] ?>" <?= (($i['primary_lecturer_id'] ?? null) == $l['id']) ? 'selected' : '' ?>><?= e($l['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-sm btn-outline">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!($intakes ?? [])): ?><tr><td colspan="5">No intakes yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card" style="max-width:600px">
    <h3>Add Intake</h3>
    <form method="post" action="/admin/courses/<?= (int) $course['id'] ?>/intakes">
      <?= csrf_field() ?>
      <div class="grid-2">
        <div class="form-group"><label>Code</label><input type="text" name="code" placeholder="NOV-2026-WEEKEND" required></div>
        <div class="form-group"><label>Mode</label>
          <select name="mode"><option value="online">Online</option><option value="in_person">Physical (training venue)</option><option value="hybrid">Hybrid</option></select>
        </div>
        <div class="form-group"><label>Venue</label><input type="text" name="venue"></div>
        <div class="form-group"><label>Capacity</label><input type="number" name="capacity"></div>
        <div class="form-group"><label>Start date</label><input type="date" name="start_date" required></div>
        <div class="form-group"><label>End date</label><input type="date" name="end_date"></div>
        <div class="form-group"><label>Primary lecturer</label>
          <select name="primary_lecturer_id">
            <option value="">—</option>
            <?php foreach (($lecturers ?? []) as $l): ?><option value="<?= (int) $l['id'] ?>"><?= e($l['full_name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Status</label>
          <select name="status"><option value="scheduled">Scheduled</option><option value="open">Open</option></select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Add Intake</button>
    </form>
  </div>
<?php endif; ?>
