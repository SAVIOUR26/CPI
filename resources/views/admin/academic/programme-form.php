<?php
$sidebar = 'partials.sidebar-admin';
/** @var array|null $programme */ /** @var array $levels */ /** @var array $categories */ /** @var int $applicationCount */
$isEdit = $programme !== null;
$current = $isEdit ? $programme + ['duration_note' => $programme['duration_note'] ?: $programme['course_duration']] : ['status' => 'published', 'sort_order' => 0];
$v = fn (string $key): string => old($key, (string) ($current[$key] ?? ''));
$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
$statuses = [
  'published' => 'Published — listed on the public Academic Programmes page and open for applications',
  'draft' => 'Draft — hidden from the public while you prepare it',
  'archived' => 'Archived — no longer offered; hidden from the public',
];
?>
<div class="dash-header">
  <div>
    <p class="eyebrow"><a href="/admin/academic/programmes"><i class="fa-solid fa-arrow-left"></i> Academic programmes</a></p>
    <h1><?= $isEdit ? e($programme['title']) : 'New academic programme' ?></h1>
    <p><?= $isEdit ? 'Update the details shown on the public programme page and application form.' : 'Programmes appear on the public Academic Programmes page once published.' ?></p>
  </div>
  <?php if ($isEdit && $programme['status'] === 'published'): ?>
    <div class="actions">
      <a href="/academic/programmes/<?= (int) $programme['id'] ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> View public page</a>
    </div>
  <?php endif; ?>
</div>

<div class="review-grid">
  <form method="post" action="<?= $isEdit ? '/admin/academic/programmes/' . (int) $programme['id'] : '/admin/academic/programmes' ?>" class="card">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="title">Programme title <span class="req">*</span></label>
      <input id="title" type="text" name="title" value="<?= e($v('title')) ?>" maxlength="190" required placeholder="e.g. Diploma in Public Health">
      <p class="help-text">Use the full award name, e.g. “Diploma in Public Health” or “Bachelor's Degree in Accounting &amp; Finance”.</p>
      <?= $err('title') ?>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="award_level">Award level <span class="req">*</span></label>
        <select id="award_level" name="award_level" required>
          <?php foreach ($levels as $key => $lvl): ?>
            <option value="<?= e($key) ?>"<?= $v('award_level') === $key ? ' selected' : '' ?>><?= e($lvl['label']) ?></option>
          <?php endforeach; ?>
        </select>
        <?= $err('award_level') ?>
      </div>
      <div class="form-group">
        <label for="category_id">Field / category</label>
        <select id="category_id" name="category_id">
          <option value="">—</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>"<?= $v('category_id') === (string) $cat['id'] ? ' selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="awarding_body">Examining / awarding body</label>
        <input id="awarding_body" type="text" name="awarding_body" value="<?= e($v('awarding_body')) ?>" maxlength="190" list="awarding-bodies" placeholder="e.g. TEAM University, Uganda">
        <datalist id="awarding-bodies"><option value="TEAM University, Uganda"><option value="Victoria University, Uganda"></datalist>
        <p class="help-text">Leave blank to show “confirmed by Admissions”.</p>
        <?= $err('awarding_body') ?>
      </div>
      <div class="form-group">
        <label for="duration_note">Duration</label>
        <input id="duration_note" type="text" name="duration_note" value="<?= e($v('duration_note')) ?>" maxlength="100" placeholder="e.g. 2 years (4 semesters)">
        <p class="help-text">Only shown when set.</p>
        <?= $err('duration_note') ?>
      </div>
    </div>
    <div class="form-group">
      <label for="summary">Summary</label>
      <textarea id="summary" name="summary" rows="2" maxlength="500" placeholder="One or two sentences shown on programme cards"><?= e($v('summary')) ?></textarea>
      <?= $err('summary') ?>
    </div>
    <div class="form-group">
      <label for="description">Programme overview</label>
      <textarea id="description" name="description" rows="6"><?= e($v('description')) ?></textarea>
    </div>
    <div class="form-group">
      <label for="entry_requirements">Entry requirements</label>
      <textarea id="entry_requirements" name="entry_requirements" rows="5" placeholder="e.g. UCE with at least 5 passes, or an equivalent qualification"><?= e($v('entry_requirements')) ?></textarea>
      <p class="help-text">Shown on the programme page. Leave blank to tell applicants that requirements are confirmed by Admissions.</p>
    </div>
    <div class="form-group">
      <label>Visibility <span class="req">*</span></label>
      <div class="choice-list">
        <?php foreach ($statuses as $key => $label): ?>
          <label class="choice"><input type="radio" name="status" value="<?= e($key) ?>"<?= $v('status') === $key ? ' checked' : '' ?> required> <span><?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
      <?= $err('status') ?>
    </div>
    <div class="form-group" style="max-width:220px">
      <label for="sort_order">Display order</label>
      <input id="sort_order" type="number" name="sort_order" value="<?= e($v('sort_order')) ?>" step="1">
      <p class="help-text">Lower numbers are listed first within each award level.</p>
      <?= $err('sort_order') ?>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Save changes' : 'Create programme' ?></button>
      <a href="/admin/academic/programmes" class="btn btn-outline">Cancel</a>
    </div>
  </form>

  <aside class="review-aside">
    <?php if ($isEdit): ?>
      <div class="panel">
        <div class="panel-head"><h3><i class="fa-solid fa-link"></i> Public links</h3></div>
        <div class="panel-body">
          <?php if ($programme['status'] === 'published'): ?>
            <ul class="doc-links">
              <li><a href="/academic/programmes/<?= (int) $programme['id'] ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i><span>Programme page</span><small>/academic/programmes/<?= (int) $programme['id'] ?></small></a></li>
              <li><a href="/academic/apply/<?= (int) $programme['id'] ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file-pen"></i><span>Online application form</span><small>/academic/apply/<?= (int) $programme['id'] ?></small></a></li>
            </ul>
          <?php else: ?>
            <p class="muted" style="margin:0">This programme is <?= e($programme['status']) ?>, so its public page and application form are hidden.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><h3><i class="fa-solid fa-file-signature"></i> Applications</h3></div>
        <div class="panel-body">
          <p style="margin:0 0 12px"><strong style="font-size:1.6rem"><?= (int) $applicationCount ?></strong> <span class="muted">received for this programme</span></p>
          <a href="/admin/academic/applications" class="link-arrow" style="font-size:.88rem">Go to admissions <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    <?php endif; ?>
    <div class="panel">
      <div class="panel-head"><h3><i class="fa-solid fa-circle-info"></i> Good to know</h3></div>
      <div class="panel-body">
        <ul class="check-list">
          <li><i class="fa-solid fa-circle-check"></i> Fees are agreed at admission — no price is shown for academic programmes.</li>
          <li><i class="fa-solid fa-circle-check"></i> Programmes with the same field name at different levels are linked as a progression pathway.</li>
          <li><i class="fa-solid fa-circle-check"></i> Admitted students join an intake (cohort) of this programme —
            <?php if ($isEdit): ?><a href="/admin/courses/<?= (int) $programme['course_id'] ?>">manage its intakes</a><?php else: ?>create intakes after saving<?php endif; ?>.</li>
        </ul>
      </div>
    </div>
  </aside>
</div>
