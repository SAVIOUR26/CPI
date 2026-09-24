<?php
/** @var array $app */ /** @var array $student */ /** @var array|null $level */
use App\Support\Institute;

$form = json_decode((string) ($app['form_data'] ?? ''), true) ?: [];
$title = trim((string) ($form['personal']['title'] ?? ''));
$name = trim(($title !== '' ? $title . ' ' : '') . $app['applicant_name']);
$study = $form['programme']['study_session'] ?? '';
?>
<div class="dash-header no-print">
  <div>
    <p class="eyebrow"><a href="/learner/admission"><i class="fa-solid fa-arrow-left"></i> My admission</a></p>
    <h1>Admission letter</h1>
  </div>
  <div class="actions"><button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / save as PDF</button></div>
</div>

<article class="letter">
  <header class="letter-head">
    <img src="<?= asset('img/logo.png') ?>" alt="CPI crest" width="72" height="72">
    <div>
      <strong>Crawford Professionals Institute</strong>
      <small>Empowering Skills · Transforming Lives</small>
      <small><?= e(Institute::LOCATION) ?> · <?= e(implode(' · ', Institute::PHONES)) ?> · <?= e(Institute::EMAIL) ?></small>
    </div>
  </header>

  <div class="letter-meta">
    <p><strong>Ref:</strong> <?= e($app['application_no'] ?: 'Application #' . $app['id']) ?><br>
      <strong>Date:</strong> <?= date_pretty($app['decided_at'] ?: date('Y-m-d'), 'j F Y') ?></p>
    <p><?= e($name) ?><br><?= e($app['email']) ?><?= $app['phone'] ? '<br>' . e($app['phone']) : '' ?></p>
  </div>

  <h2 class="letter-subject">Offer of admission — <?= e($app['programme_title']) ?></h2>

  <p>Dear <?= e($name) ?>,</p>
  <p>Following review of your application, we are pleased to offer you admission to the <strong><?= e($app['programme_title']) ?></strong>
    at Crawford Professionals Institute<?= $app['awarding_body'] ? ', examined and awarded by <strong>' . e($app['awarding_body']) . '</strong>' : '' ?>.</p>

  <table class="letter-table">
    <tr><th>Programme</th><td><?= e($app['programme_title']) ?></td></tr>
    <tr><th>Award level</th><td><?= e($level['label'] ?? ucfirst((string) $app['award_level'])) ?></td></tr>
    <?php if ($app['awarding_body']): ?><tr><th>Examining / awarding body</th><td><?= e($app['awarding_body']) ?></td></tr><?php endif; ?>
    <tr><th>Intake</th><td><?= $app['intake_code'] ? e($app['intake_code']) . ($app['intake_start'] ? ', starting ' . date_pretty($app['intake_start'], 'j F Y') : '') : 'To be confirmed by Admissions' ?></td></tr>
    <?php if ($study): ?><tr><th>Study session</th><td><?= e($study) ?></td></tr><?php endif; ?>
    <?php if ($app['duration_note']): ?><tr><th>Duration</th><td><?= e($app['duration_note']) ?></td></tr><?php endif; ?>
  </table>

  <p>To take up this offer, please complete your registration and fee payment through the Student Portal, where you will also find your
    course materials, timetable and announcements.</p>
  <p>Please keep your original academic documents: you may be asked to present them for verification. This offer is subject to the
    information and documents in your application being true and complete.</p>
  <p>We look forward to welcoming you to Crawford Professionals Institute.</p>

  <p class="letter-sign">Yours sincerely,<br><br><strong>Admissions Office</strong><br>Crawford Professionals Institute</p>
</article>
