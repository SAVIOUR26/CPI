<?php
/** @var array $programme */ /** @var array $level */ /** @var int $maxFileBytes */ /** @var int $maxTotalBytes */ /** @var int $maxFileCount */
use App\Controllers\Academic\GatewayController as G;

// Based on the client's admission form: docs/content/academic/cpi_online_application_form.html
$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
$sel = fn (string $path, string $value): string => old_input($path) === $value ? ' selected' : '';
$mb = fn (int $bytes): string => rtrim(rtrim(number_format($bytes / 1048576, 1), '0'), '.') . 'MB';
$oldSupport = (array) (\App\Core\Session::old('personal', [])['support'] ?? []);
$qualRows = max(3, min(8, count((array) \App\Core\Session::old('qualifications', []))));
$steps = ['Programme', 'Personal details', 'Sponsors', 'UACE (A-Level)', 'UCE (O-Level)', 'Other qualifications', 'Documents', 'Declaration'];

\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Online application for admission',
  'title' => 'Apply — ' . $programme['title'],
  'crumbs' => [['label' => 'Academic Programmes', 'href' => '/academic'], ['label' => \App\Models\AcademicProgramme::field($programme), 'href' => '/academic/programmes/' . (int) $programme['id']], ['label' => 'Apply']],
  'stats' => array_values(array_filter([
    ['icon' => $level['icon'], 'text' => $level['label']],
    $programme['awarding_body'] ? ['icon' => 'fa-building-columns', 'text' => $programme['awarding_body']] : null,
    ['icon' => 'fa-clock', 'text' => 'About 15 minutes'],
  ])),
]);
?>
<section class="section">
  <div class="container apply-layout">
    <form method="post" action="/academic/apply/<?= (int) $programme['id'] ?>" enctype="multipart/form-data" class="apply-form" id="apply-form"
          data-stepper data-max-file="<?= (int) $maxFileBytes ?>" data-max-total="<?= (int) $maxTotalBytes ?>" data-max-count="<?= (int) $maxFileCount ?>" novalidate>
      <?= csrf_field() ?>

      <ol class="stepper" aria-label="Application steps">
        <?php foreach ($steps as $n => $label): ?>
          <li data-goto="<?= $n ?>"><span class="st-num"><?= $n + 1 ?></span><span class="st-label"><?= e($label) ?></span></li>
        <?php endforeach; ?>
      </ol>

      <!-- 1. Programme -->
      <fieldset class="form-step" data-title="Programme">
        <legend><span>1</span> Programme application</legend>
        <div class="notice" style="margin-bottom:22px"><i class="fa-solid fa-circle-info"></i>
          <p><strong>Application instructions:</strong> complete all applicable sections. Fields marked <span class="req">*</span> are required.
            Academic certificates, transcripts and other supporting documents may be uploaded for verification — keep your originals, as you may be
            asked to present them.</p></div>
        <div class="readonly-grid">
          <div><small>Application type</small><strong><?= e($level['label']) ?> programme</strong></div>
          <div><small>Programme applied for</small><strong><?= e($programme['title']) ?></strong></div>
          <div><small>Examining / awarding body</small><strong><?= e($programme['awarding_body'] ?: 'Confirmed by Admissions') ?></strong></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="intake">Preferred intake</label>
            <select id="intake" name="intake">
              <option value="">Next available intake</option>
              <?php foreach (G::INTAKES as $o): ?><option<?= $sel('intake', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="study_session">Study session</label>
            <select id="study_session" name="study_session">
              <option value="">Select a study session</option>
              <?php foreach (G::STUDY_SESSIONS as $o): ?><option<?= $sel('study_session', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
            </select>
            <p class="help-text">Availability of each session depends on the programme.</p>
          </div>
        </div>
      </fieldset>

      <!-- 2. Personal -->
      <fieldset class="form-step" data-title="Personal details">
        <legend><span>2</span> Applicant's personal information</legend>
        <div class="form-row three">
          <div class="form-group">
            <label for="p_title">Title</label>
            <select id="p_title" name="personal[title]">
              <option value="">—</option>
              <?php foreach (G::TITLES as $o): ?><option<?= $sel('personal.title', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="p_surname">Surname <span class="req">*</span></label>
            <input id="p_surname" type="text" name="personal[surname]" value="<?= e(old_input('personal.surname')) ?>" autocomplete="family-name" required>
            <?= $err('personal.surname') ?>
          </div>
          <div class="form-group">
            <label for="p_other">Other names <span class="req">*</span></label>
            <input id="p_other" type="text" name="personal[other_names]" value="<?= e(old_input('personal.other_names')) ?>" autocomplete="given-name" required>
            <?= $err('personal.other_names') ?>
          </div>
        </div>
        <div class="form-row three">
          <div class="form-group">
            <label for="p_gender">Gender <span class="req">*</span></label>
            <select id="p_gender" name="personal[gender]" required>
              <option value="">Select</option>
              <option<?= $sel('personal.gender', 'Male') ?>>Male</option>
              <option<?= $sel('personal.gender', 'Female') ?>>Female</option>
            </select>
            <?= $err('personal.gender') ?>
          </div>
          <div class="form-group">
            <label for="p_dob">Date of birth <span class="req">*</span></label>
            <input id="p_dob" type="date" name="personal[dob]" value="<?= e(old_input('personal.dob')) ?>" max="<?= date('Y-m-d', strtotime('-10 years')) ?>" autocomplete="bday" required>
            <?= $err('personal.dob') ?>
          </div>
          <div class="form-group">
            <label for="p_nat">Nationality <span class="req">*</span></label>
            <input id="p_nat" type="text" name="personal[nationality]" value="<?= e(old_input('personal.nationality')) ?>" placeholder="e.g. Ugandan" required>
            <?= $err('personal.nationality') ?>
          </div>
        </div>
        <div class="form-row three">
          <div class="form-group">
            <label for="p_phone">Telephone <span class="req">*</span></label>
            <input id="p_phone" type="tel" name="personal[phone]" value="<?= e(old_input('personal.phone')) ?>" placeholder="+256 7XX XXX XXX" autocomplete="tel" required>
            <?= $err('personal.phone') ?>
          </div>
          <div class="form-group">
            <label for="p_email">Email <span class="req">*</span></label>
            <input id="p_email" type="email" name="personal[email]" value="<?= e(old_input('personal.email')) ?>" autocomplete="email" required>
            <?= $err('personal.email') ?>
          </div>
          <div class="form-group">
            <label for="p_country">Country of residence</label>
            <input id="p_country" type="text" name="personal[country]" value="<?= e(old_input('personal.country')) ?>" autocomplete="country-name">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="p_city">Town / City</label>
            <input id="p_city" type="text" name="personal[city]" value="<?= e(old_input('personal.city')) ?>" autocomplete="address-level2">
          </div>
          <div class="form-group">
            <label for="p_address">Postal / physical address</label>
            <input id="p_address" type="text" name="personal[address]" value="<?= e(old_input('personal.address')) ?>" autocomplete="street-address">
          </div>
        </div>
        <div class="form-group">
          <label>Disability / special support requirements</label>
          <div class="check-row">
            <?php foreach (G::SUPPORT_NEEDS as $o): ?>
              <label class="check"><input type="checkbox" name="personal[support][]" value="<?= e($o) ?>"<?= in_array($o, $oldSupport, true) ? ' checked' : '' ?>> <?= e($o) ?></label>
            <?php endforeach; ?>
          </div>
          <p class="help-text">This helps us plan any support you may need. It does not affect your application.</p>
        </div>
      </fieldset>

      <!-- 3. Sponsors -->
      <fieldset class="form-step" data-title="Sponsors">
        <legend><span>3</span> Sponsor information</legend>
        <p class="muted">The person or organisation responsible for paying fees and other requirements. Optional — leave blank if self-sponsored.</p>
        <?php for ($s = 0; $s < 2; $s++): ?>
          <div class="sub-card">
            <h4>Sponsor <?= $s + 1 ?></h4>
            <div class="form-row">
              <div class="form-group"><label for="s<?= $s ?>_name">Name</label><input id="s<?= $s ?>_name" type="text" name="sponsors[<?= $s ?>][name]" value="<?= e(old_input("sponsors.$s.name")) ?>"></div>
              <div class="form-group"><label for="s<?= $s ?>_phone">Telephone number</label><input id="s<?= $s ?>_phone" type="tel" name="sponsors[<?= $s ?>][phone]" value="<?= e(old_input("sponsors.$s.phone")) ?>"></div>
              <div class="form-group"><label for="s<?= $s ?>_nat">Nationality</label><input id="s<?= $s ?>_nat" type="text" name="sponsors[<?= $s ?>][nationality]" value="<?= e(old_input("sponsors.$s.nationality")) ?>"></div>
              <div class="form-group"><label for="s<?= $s ?>_rel">Relationship / organisation</label><input id="s<?= $s ?>_rel" type="text" name="sponsors[<?= $s ?>][relationship]" value="<?= e(old_input("sponsors.$s.relationship")) ?>" placeholder="e.g. Parent, Employer"></div>
            </div>
          </div>
        <?php endfor; ?>
      </fieldset>

      <!-- 4. UACE -->
      <fieldset class="form-step" data-title="UACE (A-Level)">
        <legend><span>4</span> UACE / equivalent (A-Level)</legend>
        <p class="muted">Leave blank if not applicable to you.</p>
        <div class="form-row">
          <div class="form-group"><label for="uace_year">Year of examination</label><input id="uace_year" type="number" name="uace[year]" min="1950" max="<?= date('Y') ?>" value="<?= e(old_input('uace.year')) ?>"><?= $err('uace.year') ?></div>
          <div class="form-group"><label for="uace_school">School / institution</label><input id="uace_school" type="text" name="uace[school]" value="<?= e(old_input('uace.school')) ?>"></div>
        </div>
        <div class="table-card">
          <table class="input-table">
            <thead><tr><th style="width:44px">#</th><th>Subject</th><th style="width:170px">Principal / Subsidiary</th><th style="width:150px">Grade / result</th></tr></thead>
            <tbody>
            <?php for ($i = 0; $i < 7; $i++): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><input type="text" name="uace[subjects][<?= $i ?>][subject]" value="<?= e(old_input("uace.subjects.$i.subject")) ?>" aria-label="UACE subject <?= $i + 1 ?>"></td>
                <td><select name="uace[subjects][<?= $i ?>][level]" aria-label="Principal or subsidiary <?= $i + 1 ?>">
                  <option value="P"<?= $sel("uace.subjects.$i.level", 'P') ?>>Principal (P)</option>
                  <option value="S"<?= $sel("uace.subjects.$i.level", 'S') ?>>Subsidiary (S)</option>
                </select></td>
                <td><input type="text" name="uace[subjects][<?= $i ?>][grade]" value="<?= e(old_input("uace.subjects.$i.grade")) ?>" aria-label="UACE grade <?= $i + 1 ?>"></td>
              </tr>
            <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </fieldset>

      <!-- 5. UCE -->
      <fieldset class="form-step" data-title="UCE (O-Level)">
        <legend><span>5</span> UCE / equivalent (O-Level)</legend>
        <div class="form-row">
          <div class="form-group"><label for="uce_year">Year of examination</label><input id="uce_year" type="number" name="uce[year]" min="1950" max="<?= date('Y') ?>" value="<?= e(old_input('uce.year')) ?>"><?= $err('uce.year') ?></div>
          <div class="form-group"><label for="uce_school">Name of school</label><input id="uce_school" type="text" name="uce[school]" value="<?= e(old_input('uce.school')) ?>"></div>
        </div>
        <div class="table-card">
          <table class="input-table">
            <thead><tr><th style="width:44px">No.</th><th>Subject</th><th style="width:170px">Grade / result</th></tr></thead>
            <tbody>
            <?php for ($i = 0; $i < 10; $i++): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><input type="text" name="uce[subjects][<?= $i ?>][subject]" value="<?= e(old_input("uce.subjects.$i.subject")) ?>" aria-label="UCE subject <?= $i + 1 ?>"></td>
                <td><input type="text" name="uce[subjects][<?= $i ?>][grade]" value="<?= e(old_input("uce.subjects.$i.grade")) ?>" aria-label="UCE grade <?= $i + 1 ?>"></td>
              </tr>
            <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </fieldset>

      <!-- 6. Other qualifications -->
      <fieldset class="form-step" data-title="Other qualifications">
        <legend><span>6</span> Other academic qualifications</legend>
        <p class="muted">Certificates, diplomas, degrees or professional qualifications you already hold.</p>
        <div class="table-card">
          <table class="input-table" id="qual-table">
            <thead><tr><th>Institution</th><th>Qualification / course</th><th style="width:120px">Year completed</th><th style="width:220px">Document</th></tr></thead>
            <tbody>
            <?php for ($i = 0; $i < $qualRows; $i++): ?>
              <tr class="qual-row">
                <td><input type="text" name="qualifications[<?= $i ?>][institution]" value="<?= e(old_input("qualifications.$i.institution")) ?>" aria-label="Institution"></td>
                <td><input type="text" name="qualifications[<?= $i ?>][qualification]" value="<?= e(old_input("qualifications.$i.qualification")) ?>" aria-label="Qualification"></td>
                <td><input type="number" name="qualifications[<?= $i ?>][year]" min="1950" max="<?= date('Y') ?>" value="<?= e(old_input("qualifications.$i.year")) ?>" aria-label="Year completed"></td>
                <td><input type="file" name="qualification_docs[<?= $i ?>]" accept=".pdf,.jpg,.jpeg,.png" aria-label="Qualification document"></td>
              </tr>
            <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-add-row="qual-table" data-max-rows="8"><i class="fa-solid fa-plus"></i> Add qualification</button>
      </fieldset>

      <!-- 7. Documents -->
      <fieldset class="form-step" data-title="Documents">
        <legend><span>7</span> Supporting documents</legend>
        <p class="muted">PDF, JPG or PNG. Maximum <strong><?= $mb($maxFileBytes) ?> per file</strong><?php if ($maxTotalBytes): ?> and
          <strong><?= $mb($maxTotalBytes) ?> for the whole application</strong><?php endif; ?><?php if ($maxFileCount): ?> (up to <?= (int) $maxFileCount ?> files)<?php endif; ?> — scan at a moderate resolution to stay within this.</p>
        <div class="upload-grid">
          <?php foreach (G::DOCUMENTS as $field => [$label, $multiple, $imagesOnly]): ?>
            <label class="upload-box">
              <i class="fa-solid <?= $imagesOnly ? 'fa-image-portrait' : ($multiple ? 'fa-copy' : 'fa-file-arrow-up') ?>"></i>
              <strong><?= e($label) ?></strong>
              <small data-file-label><?= $multiple ? 'Choose up to ' . G::MAX_FILES_PER_FIELD . ' files' : 'Choose a file' ?><?= $imagesOnly ? ' (JPG/PNG)' : '' ?></small>
              <input type="file" name="<?= e($field) ?><?= $multiple ? '[]' : '' ?>"<?= $multiple ? ' multiple data-max-files="' . G::MAX_FILES_PER_FIELD . '"' : '' ?> accept="<?= $imagesOnly ? '.jpg,.jpeg,.png' : '.pdf,.jpg,.jpeg,.png' ?>">
            </label>
          <?php endforeach; ?>
        </div>
        <p class="upload-total help-text" data-upload-total></p>
      </fieldset>

      <!-- 8. Declaration -->
      <fieldset class="form-step" data-title="Declaration">
        <legend><span>8</span> Declaration &amp; submission</legend>
        <div class="declaration">I declare that the information provided in this application is correct and complete to the best of my knowledge. I
          understand that impersonation, falsification of documents, or giving false or incomplete information may lead to cancellation of admission
          and other action in accordance with applicable law and institutional regulations.</div>
        <div class="form-group" style="margin-top:18px">
          <label class="check check-lg"><input type="checkbox" name="declaration" value="1"<?= old_input('declaration') ? ' checked' : '' ?> required>
            I have read, understood and agree to this declaration. <span class="req">*</span></label>
          <?= $err('declaration') ?>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="sig">Applicant name (signature)</label><input id="sig" type="text" name="signature_name" value="<?= e(old_input('signature_name')) ?>" placeholder="Type your full name"></div>
          <div class="form-group"><label>Date</label><input type="text" value="<?= date('j F Y') ?>" readonly></div>
        </div>
      </fieldset>

      <div class="step-nav">
        <button type="button" class="btn btn-outline" data-step-back><i class="fa-solid fa-arrow-left"></i> Back</button>
        <span class="step-count" data-step-count></span>
        <button type="button" class="btn btn-primary" data-step-next>Continue <i class="fa-solid fa-arrow-right"></i></button>
        <button type="submit" class="btn btn-primary" data-step-submit><i class="fa-solid fa-paper-plane"></i> Submit application</button>
      </div>
    </form>

    <aside class="apply-aside">
      <div class="card">
        <p class="eyebrow">Your application</p>
        <h3 style="margin-bottom:6px"><?= e($programme['title']) ?></h3>
        <ul class="facts">
          <li><i class="fa-solid <?= e($level['icon']) ?>"></i><div><small>Award level</small><strong><?= e($level['label']) ?></strong></div></li>
          <li><i class="fa-solid fa-building-columns"></i><div><small>Awarding body</small><strong><?= e($programme['awarding_body'] ?: 'Confirmed by Admissions') ?></strong></div></li>
        </ul>
        <hr>
        <p class="help-text" style="margin-top:0"><strong>What happens next?</strong></p>
        <ol class="mini-steps">
          <li>You receive an application number by email.</li>
          <li>Admissions reviews your application.</li>
          <li>Successful applicants receive admission and registration information.</li>
        </ol>
        <a href="/academic/programmes/<?= (int) $programme['id'] ?>" class="link-arrow" style="font-size:.88rem"><i class="fa-solid fa-arrow-left"></i> Back to programme details</a>
      </div>
    </aside>
  </div>
</section>
