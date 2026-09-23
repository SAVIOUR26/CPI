<?php
$sidebar = 'partials.sidebar-admin';
/** @var array $app */ /** @var array $programme */ /** @var array|null $level */ /** @var array $intakes */
/** @var array $form */ /** @var array $documents */ /** @var array|null $reviewer */
$labels = ['submitted' => 'New', 'under_review' => 'Under review', 'admitted' => 'Admitted', 'rejected' => 'Not admitted'];
$p = $form['personal'] ?? [];
$choice = $form['programme'] ?? [];
$show = fn ($value): string => ($value === null || $value === '' || $value === []) ? '<span class="muted">—</span>' : e(is_array($value) ? implode(', ', $value) : (string) $value);
$hasPhoto = !empty(array_filter($documents, fn ($d) => $d['key'] === 'passport_photo'));
$age = null;
if (!empty($p['dob']) && ($dob = date_create($p['dob']))) {
    $age = $dob->diff(date_create('today'))->y;
}
$open = in_array($app['status'], ['submitted', 'under_review'], true);
$base = '/admin/academic/applications/' . (int) $app['id'];
?>
<div class="dash-header">
  <div class="applicant-head">
    <?php if ($hasPhoto): ?>
      <img class="applicant-photo" src="<?= $base ?>/files/passport_photo" alt="Passport photograph of <?= e($app['applicant_name']) ?>">
    <?php else: ?>
      <span class="applicant-photo placeholder"><?= e(initials($app['applicant_name'])) ?></span>
    <?php endif; ?>
    <div>
      <p class="eyebrow"><a href="/admin/academic/applications"><i class="fa-solid fa-arrow-left"></i> Admissions</a> · <?= e($app['application_no'] ?: 'Application #' . $app['id']) ?></p>
      <h1><?= e(trim(($p['title'] ?? '') . ' ' . $app['applicant_name'])) ?></h1>
      <p><?= e($programme['title']) ?> · Submitted <?= date_pretty($app['created_at'], 'j M Y, H:i') ?></p>
    </div>
  </div>
  <div class="actions">
    <span class="status status-lg status-<?= e($app['status']) ?>"><?= e($labels[$app['status']] ?? $app['status']) ?></span>
    <button type="button" class="btn btn-outline btn-sm no-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
  </div>
</div>

<div class="review-grid">
  <div>
    <div class="panel">
      <div class="panel-head"><h2><i class="fa-solid fa-building-columns"></i> Programme choice</h2></div>
      <div class="kv">
        <div><small>Programme</small><strong><?= e($programme['title']) ?></strong></div>
        <div><small>Award level</small><strong><?= e($level['label'] ?? ucfirst($programme['award_level'])) ?></strong></div>
        <div><small>Examining / awarding body</small><strong><?= $show($choice['awarding_body'] ?? $programme['awarding_body']) ?></strong></div>
        <div><small>Preferred intake</small><strong><?= e(($choice['intake'] ?? '') ?: 'Next available') ?></strong></div>
        <div class="full"><small>Study session</small><strong><?= $show($choice['study_session'] ?? '') ?></strong></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2><i class="fa-solid fa-user"></i> Personal information</h2></div>
      <div class="kv">
        <?php if ($p): ?>
          <div><small>Surname</small><strong><?= $show($p['surname'] ?? '') ?></strong></div>
          <div><small>Other names</small><strong><?= $show($p['other_names'] ?? '') ?></strong></div>
          <div><small>Title</small><strong><?= $show($p['title'] ?? '') ?></strong></div>
          <div><small>Gender</small><strong><?= $show($p['gender'] ?? '') ?></strong></div>
          <div><small>Date of birth</small><strong><?= !empty($p['dob']) ? date_pretty($p['dob'], 'j F Y') . ($age !== null ? ' <span class="muted">(' . $age . ' years)</span>' : '') : $show('') ?></strong></div>
          <div><small>Nationality</small><strong><?= $show($p['nationality'] ?? '') ?></strong></div>
        <?php endif; ?>
        <div><small>Telephone</small><strong><?= $app['phone'] ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $app['phone'])) . '">' . e($app['phone']) . '</a>' : $show('') ?></strong></div>
        <div><small>Email</small><strong><a href="mailto:<?= e($app['email']) ?>"><?= e($app['email']) ?></a></strong></div>
        <?php if ($p): ?>
          <div><small>Country of residence</small><strong><?= $show($p['country'] ?? '') ?></strong></div>
          <div><small>Town / city</small><strong><?= $show($p['city'] ?? '') ?></strong></div>
          <div class="full"><small>Postal / physical address</small><strong><?= $show($p['address'] ?? '') ?></strong></div>
          <div class="full"><small>Disability / special support</small><strong><?= $show($p['support'] ?? []) ?></strong></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($form): ?>
      <div class="panel">
        <div class="panel-head"><h2><i class="fa-solid fa-hand-holding-dollar"></i> Sponsors</h2></div>
        <?php if (empty($form['sponsors'])): ?>
          <div class="panel-body"><p class="muted">No sponsor given (self-sponsored or not provided).</p></div>
        <?php else: ?>
          <div class="table-card">
            <table>
              <thead><tr><th>Name</th><th>Telephone</th><th>Nationality</th><th>Relationship / organisation</th></tr></thead>
              <tbody>
              <?php foreach ($form['sponsors'] as $s): ?>
                <tr><td><?= $show($s['name']) ?></td><td><?= $show($s['phone']) ?></td><td><?= $show($s['nationality']) ?></td><td><?= $show($s['relationship']) ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <?php foreach (['uace' => ['UACE / equivalent (A-Level)', 'School / institution'], 'uce' => ['UCE / equivalent (O-Level)', 'School']] as $exam => [$title, $schoolLabel]): ?>
        <?php $r = $form[$exam] ?? []; ?>
        <div class="panel">
          <div class="panel-head"><h2><i class="fa-solid fa-book"></i> <?= e($title) ?></h2></div>
          <?php if (empty($r['year']) && empty($r['school']) && empty($r['subjects'])): ?>
            <div class="panel-body"><p class="muted">Not provided.</p></div>
          <?php else: ?>
            <div class="kv">
              <div><small>Year of examination</small><strong><?= $show($r['year'] ?? '') ?></strong></div>
              <div><small><?= e($schoolLabel) ?></small><strong><?= $show($r['school'] ?? '') ?></strong></div>
            </div>
            <?php if (!empty($r['subjects'])): ?>
              <div class="table-card">
                <table class="subject-table">
                  <thead><tr><th style="width:48px">#</th><th>Subject</th><?php if ($exam === 'uace'): ?><th>Level</th><?php endif; ?><th>Grade / result</th></tr></thead>
                  <tbody>
                  <?php foreach ($r['subjects'] as $n => $subject): ?>
                    <tr>
                      <td><?= $n + 1 ?></td>
                      <td><?= $show($subject['subject']) ?></td>
                      <?php if ($exam === 'uace'): ?><td><?= ($subject['level'] ?? '') === 'S' ? 'Subsidiary' : 'Principal' ?></td><?php endif; ?>
                      <td><strong><?= $show($subject['grade']) ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <div class="panel">
        <div class="panel-head"><h2><i class="fa-solid fa-award"></i> Other academic qualifications</h2></div>
        <?php if (empty($form['qualifications'])): ?>
          <div class="panel-body"><p class="muted">None listed.</p></div>
        <?php else: ?>
          <div class="table-card">
            <table>
              <thead><tr><th>Institution</th><th>Qualification / course</th><th>Year</th><th>Document</th></tr></thead>
              <tbody>
              <?php foreach ($form['qualifications'] as $n => $q): ?>
                <tr>
                  <td><?= $show($q['institution']) ?></td>
                  <td><?= $show($q['qualification']) ?></td>
                  <td><?= $show($q['year']) ?></td>
                  <td><?= !empty($q['document']) ? '<a href="' . $base . '/files/qualification?i=' . (int) $n . '" target="_blank" rel="noopener"><i class="fa-regular fa-file-lines"></i> View</a>' : '<span class="muted">—</span>' ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <?php $d = $form['declaration'] ?? []; ?>
      <div class="panel">
        <div class="panel-head"><h2><i class="fa-solid fa-signature"></i> Declaration</h2></div>
        <div class="kv">
          <div class="full"><small>Declaration</small><strong><?= !empty($d['accepted'])
            ? '<i class="fa-solid fa-circle-check" style="color:var(--success)"></i> Accepted — the applicant confirmed the information is correct and complete.'
            : '<i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i> Not accepted' ?></strong></div>
          <div><small>Applicant name (signature)</small><strong><?= $show($d['signature_name'] ?? '') ?></strong></div>
          <div><small>Date</small><strong><?= !empty($d['date']) ? date_pretty($d['date'], 'j F Y') : $show('') ?></strong></div>
        </div>
      </div>
    <?php else: ?>
      <div class="notice"><i class="fa-solid fa-circle-info"></i><p>This application was made before the full online application form was introduced, so only contact details and the uploaded documents are available.</p></div>
    <?php endif; ?>
  </div>

  <aside class="review-aside">
    <div class="panel">
      <div class="panel-head"><h3><i class="fa-solid fa-gavel"></i> Admission decision</h3></div>
      <div class="panel-body">
        <?php if ($open): ?>
          <form method="post" action="<?= $base ?>/decide" class="no-print" data-decision-form>
            <?= csrf_field() ?>
            <div class="choice-list" style="margin-bottom:16px">
              <?php if ($app['status'] === 'submitted'): ?>
                <label class="choice"><input type="radio" name="decision" value="under_review" required>
                  <span><strong>Mark as under review</strong><small>Internal only — the applicant is not emailed.</small></span></label>
              <?php endif; ?>
              <label class="choice"><input type="radio" name="decision" value="admitted" required data-confirm="Admit <?= e($app['applicant_name']) ?> and email the offer of admission now?">
                <span><strong>Admit</strong><small>Creates the student account and emails the offer with login details.</small></span></label>
              <label class="choice"><input type="radio" name="decision" value="rejected" required data-confirm="Decline this application and email <?= e($app['applicant_name']) ?> now? This cannot be undone.">
                <span><strong>Do not admit</strong><small>Emails the applicant a polite decline.</small></span></label>
            </div>
            <div class="form-group">
              <label for="intake_id">Intake (when admitting)</label>
              <select id="intake_id" name="intake_id">
                <option value="">Assign later</option>
                <?php foreach ($intakes as $i): ?>
                  <option value="<?= (int) $i['id'] ?>"><?= e($i['code']) ?><?= $i['start_date'] ? ' — starts ' . date_pretty($i['start_date']) : '' ?></option>
                <?php endforeach; ?>
              </select>
              <p class="help-text"><?= $intakes ? 'Choosing an intake enrols the student with fees pending.' : 'No intakes yet for this programme.' ?>
                <a href="/admin/courses/<?= (int) $programme['course_id'] ?>">Manage intakes</a></p>
            </div>
            <div class="form-group">
              <label for="decision_note">Internal note</label>
              <textarea id="decision_note" name="decision_note" rows="3" placeholder="Visible to admins only — not sent to the applicant"><?= e((string) $app['decision_note']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-check"></i> Record decision</button>
          </form>
        <?php else: ?>
          <dl class="decision-summary">
            <div><dt>Outcome</dt><dd><span class="status status-<?= e($app['status']) ?>"><?= e($labels[$app['status']] ?? $app['status']) ?></span></dd></div>
            <?php if ($app['decided_at']): ?><div><dt>Decided</dt><dd><?= date_pretty($app['decided_at'], 'j M Y, H:i') ?></dd></div><?php endif; ?>
            <?php if ($reviewer): ?><div><dt>By</dt><dd><?= e($reviewer['full_name']) ?></dd></div><?php endif; ?>
            <?php if ($app['status'] === 'admitted'): ?>
              <div><dt>Intake</dt><dd><?php
                $assigned = array_values(array_filter($intakes, fn ($i) => (int) $i['id'] === (int) $app['intake_id']));
                echo $assigned ? e($assigned[0]['code']) : 'Not yet assigned';
              ?></dd></div>
            <?php endif; ?>
          </dl>
          <?php if ($app['decision_note']): ?>
            <p class="help-text" style="margin-top:14px"><strong>Internal note:</strong><br><?= nl2br(e($app['decision_note'])) ?></p>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($open && $app['status'] === 'under_review' && $reviewer): ?>
          <p class="help-text" style="margin-top:12px"><i class="fa-solid fa-magnifying-glass"></i> Under review by <?= e($reviewer['full_name']) ?>.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3><i class="fa-solid fa-paperclip"></i> Documents</h3><span class="muted" style="font-size:.82rem"><?= count($documents) ?> file<?= count($documents) === 1 ? '' : 's' ?></span></div>
      <div class="panel-body">
        <?php if ($documents): ?>
          <ul class="doc-links">
            <?php foreach ($documents as $doc): ?>
              <li><a href="<?= e($doc['url']) ?>" target="_blank" rel="noopener">
                <i class="fa-regular <?= $doc['ext'] === 'PDF' ? 'fa-file-pdf' : 'fa-file-image' ?>"></i><span><?= e($doc['label']) ?></span><small><?= e($doc['ext']) ?></small></a></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="muted">No documents were attached.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel no-print">
      <div class="panel-head"><h3><i class="fa-solid fa-address-card"></i> Contact applicant</h3></div>
      <div class="panel-body" style="display:grid;gap:8px">
        <a href="mailto:<?= e($app['email']) ?>?subject=<?= rawurlencode('Your application' . ($app['application_no'] ? ' ' . $app['application_no'] : '') . ' — Crawford Professionals Institute') ?>" class="btn btn-outline btn-sm btn-block"><i class="fa-regular fa-envelope"></i> Email <?= e(explode(' ', $app['applicant_name'])[0]) ?></a>
        <?php if ($app['phone']): ?>
          <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $app['phone'])) ?>" class="btn btn-outline btn-sm btn-block"><i class="fa-solid fa-phone"></i> Call <?= e($app['phone']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </aside>
</div>
