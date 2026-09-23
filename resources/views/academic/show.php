<?php
/** @var array $programme */ /** @var array $level */ /** @var array $pathway */
$duration = $programme['duration_note'] ?: $programme['course_duration'];
$stats = [['icon' => $level['icon'], 'text' => $level['label']]];
if ($programme['awarding_body']) { $stats[] = ['icon' => 'fa-building-columns', 'text' => 'Awarded by ' . $programme['awarding_body']]; }
if ($duration) { $stats[] = ['icon' => 'fa-clock', 'text' => $duration]; }
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => $level['label'] . ' programme',
  'title' => $programme['title'],
  'lead' => $programme['summary'],
  'crumbs' => [['label' => 'Academic Programmes', 'href' => '/academic'], ['label' => $level['plural'], 'href' => '/academic#level-' . $programme['award_level']], ['label' => \App\Models\AcademicProgramme::field($programme)]],
  'stats' => $stats,
  'actions' => [['label' => 'Apply now', 'href' => '/academic/apply/' . (int) $programme['id'], 'class' => 'btn-gold', 'icon' => 'fa-file-pen']],
]);
?>
<section class="section">
  <div class="container course-layout">
    <div>
      <p class="eyebrow">Programme overview</p>
      <h2>About this programme</h2>
      <div class="prose"><?= nl2br(e($programme['description'] ?: $programme['summary'])) ?></div>

      <h3 style="margin-top:36px">Suitable for learners who want to</h3>
      <ul class="check-list cols-2">
        <?php foreach ($level['suits'] as $s): ?><li><i class="fa-solid fa-circle-check"></i> <?= e($s) ?></li><?php endforeach; ?>
      </ul>

      <h3 style="margin-top:36px">Entry requirements</h3>
      <?php if (trim((string) $programme['entry_requirements']) !== ''): ?>
        <div class="card"><div class="prose"><?= nl2br(e($programme['entry_requirements'])) ?></div></div>
      <?php else: ?>
        <div class="notice"><i class="fa-solid fa-circle-info"></i>
          <p>Entry requirements for this programme are set by the awarding university and confirmed by CPI Admissions.
            <a href="/contact">Contact Admissions</a> to confirm your eligibility, or apply and our team will review your qualifications.</p></div>
      <?php endif; ?>

      <?php if (count($pathway) > 1): ?>
        <h3 style="margin-top:36px">Academic progression in <?= e(\App\Models\AcademicProgramme::field($programme)) ?></h3>
        <div class="pathway">
          <?php foreach ($pathway as $n => $step): ?>
            <?php if ($n): ?><i class="fa-solid fa-arrow-right pathway-arrow"></i><?php endif; ?>
            <a href="/academic/programmes/<?= (int) $step['id'] ?>" class="pathway-step<?= (int) $step['id'] === (int) $programme['id'] ? ' is-current' : '' ?>">
              <i class="fa-solid <?= e(\App\Models\AcademicProgramme::levels()[$step['award_level']]['icon']) ?>"></i>
              <?= e(\App\Models\AcademicProgramme::levels()[$step['award_level']]['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <p class="help-text">Progression depends on the programme and the applicable university and regulatory requirements.</p>
      <?php endif; ?>

      <h3 style="margin-top:36px">Awarding arrangements</h3>
      <div class="role-box">
        <span class="vb-icon"><i class="fa-solid fa-building-columns"></i></span>
        <div>
          <?php if ($programme['awarding_body']): ?>
            <p><strong>Examining &amp; awarding body: <?= e($programme['awarding_body']) ?>.</strong> The awarding university is responsible for
              this programme's academic governance, assessment and examinations, quality assurance and the award of the qualification, in
              accordance with its academic regulations and applicable regulatory requirements.</p>
          <?php else: ?>
            <p><strong>The awarding university for this programme will be confirmed by CPI Admissions.</strong></p>
          <?php endif; ?>
          <p class="muted" style="margin:0">CPI provides professional training, guidance, application support and learning support. CPI does not
            itself award this qualification.</p>
        </div>
      </div>

      <div class="notice" style="margin-top:28px"><i class="fa-solid fa-circle-info"></i>
        <p>Programme availability, admission requirements, duration, fees, mode of study, examination arrangements, university affiliation and
          awarding arrangements may vary by programme and intake. Please confirm the programme, awarding university and applicable regulatory
          status before enrolment.</p></div>
    </div>

    <aside class="course-aside">
      <div class="card">
        <p class="eyebrow">Apply for this programme</p>
        <ul class="facts">
          <li><i class="fa-solid <?= e($level['icon']) ?>"></i><div><small>Award level</small><strong><?= e($level['label']) ?></strong></div></li>
          <li><i class="fa-solid fa-building-columns"></i><div><small>Awarding body</small><strong><?= e($programme['awarding_body'] ?: 'Confirmed by Admissions') ?></strong></div></li>
          <?php if ($duration): ?><li><i class="fa-regular fa-clock"></i><div><small>Duration</small><strong><?= e($duration) ?></strong></div></li><?php endif; ?>
          <li><i class="fa-regular fa-calendar"></i><div><small>Intakes</small><strong>January · May · August · October</strong></div></li>
          <li><i class="fa-solid fa-house-laptop"></i><div><small>Study options</small><strong>In class (day, evening/weekend) or online</strong></div></li>
        </ul>
        <a href="/academic/apply/<?= (int) $programme['id'] ?>" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-file-pen"></i> Apply online</a>
        <a href="/contact" class="btn btn-outline btn-block" style="margin-top:10px">Contact Admissions</a>
        <hr>
        <p class="help-text" style="margin-top:0"><strong>Have these ready</strong> (PDF or photo, max 5MB each):</p>
        <ul class="doc-list">
          <li><i class="fa-regular fa-image"></i> Passport photograph</li>
          <li><i class="fa-regular fa-id-card"></i> National ID or passport</li>
          <li><i class="fa-regular fa-file-lines"></i> UCE / UACE result slips</li>
          <li><i class="fa-regular fa-file-lines"></i> Other certificates &amp; transcripts</li>
        </ul>
      </div>
    </aside>
  </div>
</section>
