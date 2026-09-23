<?php
/** @var array $programmes */ /** @var array $groups */ /** @var array $levels */
// Copy: docs/content/academic/ACADEMIC SYSTEM.pdf and "…ACADEMIC PROGRAMMES.pdf" (client-supplied).
$levelCounts = array_map('count', $groups);
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Academic programmes',
  'title' => 'Building Knowledge. Developing Professionals. Creating Opportunities.',
  'lead' => 'Certificate, Diploma and Degree programmes through CPI\'s academic partnerships with recognized universities in Uganda.',
  'crumbs' => [['label' => 'Academic Programmes']],
  'stats' => array_values(array_filter([
    ['icon' => 'fa-layer-group', 'text' => count($programmes) . ' programmes'],
    !empty($levelCounts['certificate']) ? ['icon' => 'fa-certificate', 'text' => $levelCounts['certificate'] . ' certificates'] : null,
    !empty($levelCounts['diploma']) ? ['icon' => 'fa-scroll', 'text' => $levelCounts['diploma'] . ' diplomas'] : null,
    !empty($levelCounts['degree']) ? ['icon' => 'fa-graduation-cap', 'text' => $levelCounts['degree'] . ' degrees'] : null,
  ])),
  'actions' => [
    ['label' => 'Explore programmes', 'href' => '#programmes', 'class' => 'btn-gold', 'icon' => 'fa-magnifying-glass'],
    ['label' => 'How to apply', 'href' => '#how-to-apply', 'class' => 'btn-ghost-light'],
  ],
]);
?>

<section class="section">
  <div class="container split-grid">
    <div data-reveal>
      <p class="eyebrow">Our academic system</p>
      <h2>Education that goes beyond a qualification</h2>
      <p class="lead">At Crawford Professionals Institute (CPI), we believe that quality education should provide more than a qualification.
        It should equip learners with the knowledge, practical skills, confidence and professional competence required to progress in
        education, employment, business and professional life.</p>
      <p class="muted">CPI provides access to selected Certificate, Diploma and Degree programmes through applicable academic partnerships and
        affiliation arrangements with <strong>TEAM University Uganda</strong> and <strong>Victoria University Uganda</strong>, subject to the
        requirements and approval status of each programme. Our academic programmes provide structured pathways for academic progression and
        professional development.</p>
    </div>
    <div class="progression" data-reveal>
      <p class="eyebrow">Academic progression</p>
      <?php foreach (['certificate', 'diploma', 'degree'] as $n => $key): ?>
        <a href="#level-<?= $key ?>" class="progression-step">
          <span class="ps-icon"><i class="fa-solid <?= $levels[$key]['icon'] ?>"></i></span>
          <span><strong><?= e($levels[$key]['label']) ?></strong><small><?= (int) ($levelCounts[$key] ?? 0) ?> programmes</small></span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        <span class="progression-link" aria-hidden="true"><i class="fa-solid fa-arrow-down"></i></span>
      <?php endforeach; ?>
      <div class="progression-step is-final">
        <span class="ps-icon"><i class="fa-solid fa-rocket"></i></span>
        <span><strong>Further development</strong><small>Postgraduate &amp; professional study</small></span>
      </div>
      <p class="help-text" style="margin-top:14px">Specific progression requirements depend on the programme and the applicable university and regulatory requirements.</p>
    </div>
  </div>
</section>

<section class="section section-soft" id="programmes">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">Our academic programmes</p>
      <h2>Choose your level and programme</h2>
      <p>Each programme page shows its examining and awarding university, entry requirements and study options.</p>
    </div>

    <?php foreach ($levels as $key => $meta): ?>
      <?php if (empty($groups[$key])) { continue; } ?>
      <div class="level-block" id="level-<?= $key ?>">
        <div class="level-head" data-reveal>
          <span class="icon-badge"><i class="fa-solid <?= $meta['icon'] ?>"></i></span>
          <div>
            <h3><?= e($meta['plural']) ?></h3>
            <p><?= e($meta['blurb']) ?></p>
            <ul class="suits">
              <?php foreach ($meta['suits'] as $s): ?><li><i class="fa-solid fa-check"></i> <?= e($s) ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="programme-grid">
          <?php foreach ($groups[$key] as $n => $p): ?>
            <a href="/academic/programmes/<?= (int) $p['id'] ?>" class="programme-card" data-reveal style="--i:<?= $n % 4 ?>">
              <span class="pc-icon"><i class="fa-solid <?= e(category_icon($p['category_slug'])) ?>"></i></span>
              <span class="pc-body">
                <small><?= e($meta['label']) ?></small>
                <strong><?= e(\App\Models\AcademicProgramme::field($p)) ?></strong>
                <?php if ($p['awarding_body']): ?><em><i class="fa-solid fa-building-columns"></i> <?= e($p['awarding_body']) ?></em><?php endif; ?>
              </span>
              <i class="fa-solid fa-arrow-right pc-arrow"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$programmes): ?>
      <div class="card empty-state"><i class="fa-solid fa-graduation-cap"></i><h3>Programmes will be announced soon</h3><p>Contact Admissions for details.</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">Why choose CPI</p>
      <h2>Why choose CPI for your academic journey?</h2>
    </div>
    <div class="why-grid">
      <?php
      $why = [
        ['fa-clock', 'Flexible learning opportunities', 'Many learners have employment, businesses and family responsibilities. Where applicable, CPI offers flexible options:', ['Full-time', 'Part-time', 'Weekend', 'Evening', 'Online', 'Blended']],
        ['fa-briefcase', 'Practical, career-oriented education', 'Education that connects classroom knowledge with real-world application, emphasizing:', ['Practical skills', 'Critical thinking', 'Problem solving', 'Communication', 'Research', 'Digital competence', 'Leadership', 'Professional ethics', 'Teamwork']],
        ['fa-laptop', 'Access to digital learning', 'Depending on the programme, students may access course materials, lecture notes, recorded lectures, videos, assignments, quizzes, online assessments and announcements through their personal Student Portal.', []],
        ['fa-chalkboard-user', 'Qualified lecturers & academic support', 'Learn under lecturers and trainers with relevant academic and professional expertise, who deliver materials, provide feedback and monitor your progress.', []],
        ['fa-route', 'A structured student experience', 'From application to completion, CPI provides a structured academic journey, with academic information available through your personal portal.', []],
        ['fa-arrow-trend-up', 'Education that supports your career', 'Programmes support learners who want to start a career, advance in their profession, change direction, upgrade their qualifications or prepare for further education.', []],
      ];
      foreach ($why as $n => [$icon, $title, $text, $chips]): ?>
        <div class="card why-card" data-reveal style="--i:<?= $n % 3 ?>">
          <span class="f-icon"><i class="fa-solid <?= $icon ?>"></i></span>
          <h3><?= e($title) ?></h3>
          <p><?= e($text) ?></p>
          <?php if ($chips): ?><div class="chip-list"><?php foreach ($chips as $c): ?><span><?= e($c) ?></span><?php endforeach; ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-warm">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">Your student journey</p>
      <h2>From application to completion</h2>
    </div>
    <ol class="journey" data-reveal>
      <?php foreach ([['fa-file-pen', 'Application'], ['fa-envelope-open-text', 'Admission'], ['fa-id-card', 'Registration'], ['fa-book-open-reader', 'Learning'], ['fa-clipboard-check', 'Assessment'], ['fa-square-poll-vertical', 'Results'], ['fa-award', 'Completion']] as [$icon, $label]): ?>
        <li><span><i class="fa-solid <?= $icon ?>"></i></span><?= e($label) ?></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section">
  <div class="container split-grid" style="align-items:start">
    <div data-reveal>
      <p class="eyebrow">Who can apply?</p>
      <h2>Academic programmes for every stage</h2>
      <p class="muted">CPI's academic programmes may be suitable for:</p>
      <div class="audience">
        <?php foreach ([['fa-school', 'School leavers'], ['fa-certificate', 'Certificate holders'], ['fa-scroll', 'Diploma holders'], ['fa-user-tie', 'Working professionals'], ['fa-lightbulb', 'Entrepreneurs'], ['fa-arrow-trend-up', 'Employees seeking career advancement'], ['fa-graduation-cap', 'Professionals seeking academic upgrading'], ['fa-seedling', 'Anyone developing new professional competencies']] as [$icon, $label]): ?>
          <span><i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="notice" style="margin-top:24px"><i class="fa-solid fa-circle-info"></i>
        <p><strong>Entry requirements vary by programme.</strong> Please review the specific requirements of your chosen programme before applying.</p></div>
    </div>
    <div id="how-to-apply" data-reveal>
      <p class="eyebrow">How to apply</p>
      <h2>Eight steps to your award</h2>
      <ol class="apply-steps">
        <?php foreach ([
          ['Select a programme', 'Explore the available Certificate, Diploma and Degree programmes.'],
          ['Check requirements', 'Review the entry requirements, duration, fees and mode of study.'],
          ['Complete the application', 'Fill in the online application form and upload your supporting documents.'],
          ['Application review', 'Your application is reviewed against the applicable admission requirements.'],
          ['Admission', 'Successful applicants receive admission and registration information.'],
          ['Begin learning', 'Access materials, lectures, assignments and resources through the learning platform.'],
          ['Complete your programme', 'Participate in learning activities and complete the required assessments.'],
          ['Progress', 'On completion, the award is issued by the responsible awarding institution.'],
        ] as $n => [$title, $text]): ?>
          <li><span class="as-num"><?= $n + 1 ?></span><div><strong><?= e($title) ?></strong><p><?= e($text) ?></p></div></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </div>
</section>

<section class="section section-soft" id="partners">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">Our university partnerships</p>
      <h2>Academic programmes through recognized universities</h2>
      <p>CPI facilitates selected academic programmes through applicable academic partnership and affiliation arrangements.</p>
    </div>
    <div class="grid-2">
      <div class="card partner-card" data-reveal>
        <span class="icon-badge"><i class="fa-solid fa-building-columns"></i></span>
        <h3>TEAM University, Uganda</h3>
        <span class="badge"><i class="fa-solid fa-stamp"></i> Examining &amp; awarding body</span>
        <p>Where a programme is offered under the TEAM University arrangement, TEAM University is responsible for the programme's academic
          governance, assessment and examinations, quality assurance and the award of the relevant qualification, subject to the applicable
          university and regulatory requirements.</p>
        <p class="muted">TEAM University is a private university licensed by the National Council for Higher Education (NCHE) and is mandated
          to offer undergraduate, postgraduate and other approved programmes.</p>
      </div>
      <div class="card partner-card" data-reveal style="--i:1">
        <span class="icon-badge"><i class="fa-solid fa-building-columns"></i></span>
        <h3>Victoria University, Uganda</h3>
        <span class="badge"><i class="fa-solid fa-handshake"></i> Academic partner</span>
        <p>Selected programmes are also offered through applicable academic arrangements with Victoria University Uganda.</p>
        <p class="muted">The university responsible for each programme is shown on that programme's page.</p>
      </div>
    </div>
    <div class="role-box" data-reveal>
      <span class="vb-icon"><i class="fa-solid fa-scale-balanced"></i></span>
      <div>
        <h3>CPI's role</h3>
        <p>CPI serves as the professional training and student-support platform through which learners receive guidance, professional
          training, application support, learning support and other services applicable to the programme. CPI does not independently represent
          itself as the awarding university — the academic qualification is awarded by the university responsible for that programme, in
          accordance with its academic regulations and applicable regulatory requirements.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">A connected learning environment</p>
      <h2>Student and lecturer portals</h2>
      <p>Connecting students, lecturers and academic administration.</p>
    </div>
    <div class="grid-2">
      <div class="card portal-card" data-reveal>
        <h3><i class="fa-solid fa-user-graduate"></i> Student Portal</h3>
        <p class="muted">Once admitted and registered, students can access their personal academic dashboard:</p>
        <ul class="check-list cols-2">
          <?php foreach (['My Courses', 'Lecture Materials', 'Videos', 'Assignments', 'Quizzes', 'Examinations', 'Results', 'Academic Calendar', 'Announcements', 'Fees and Payments', 'Admission Documents', 'Certificates', 'Student Profile'] as $f): ?>
            <li><i class="fa-solid fa-circle-check"></i> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="card portal-card" data-reveal style="--i:1">
        <h3><i class="fa-solid fa-chalkboard-user"></i> Lecturer Portal</h3>
        <p class="muted">Lecturers access their assigned courses and can:</p>
        <ul class="check-list cols-2">
          <?php foreach (['Upload learning materials', 'Upload lecture videos', 'Create assignments', 'Create quizzes', 'Conduct assessments', 'Grade students', 'Record attendance', 'Monitor student performance', 'Communicate with students'] as $f): ?>
            <li><i class="fa-solid fa-circle-check"></i> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container">
    <div class="cta-band" data-reveal>
      <div class="hero-bg" aria-hidden="true"><span class="orb orb-2"></span><div class="bg-dots"></div></div>
      <div>
        <p class="eyebrow" style="color:var(--gold-300)">Start your academic journey</p>
        <h2>Your Education. Your Skills. Your Future.</h2>
        <p>Whether you are beginning your academic journey, upgrading your qualification or preparing for the next stage of your career,
          CPI provides access to selected programmes designed to support your educational and professional goals.</p>
      </div>
      <div class="cta-actions">
        <a href="#programmes" class="btn btn-gold btn-lg">Apply now <i class="fa-solid fa-arrow-right"></i></a>
        <a href="/contact" class="btn btn-ghost-light btn-lg"><i class="fa-solid fa-headset"></i> Contact Admissions</a>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="notice" data-reveal><i class="fa-solid fa-circle-info"></i>
      <p><strong>Important information.</strong> Programme availability, entry requirements, fees, study mode, university affiliation,
        accreditation and awarding arrangements may vary by programme and intake. Applicants should carefully review the information on the
        specific programme page and confirm the applicable requirements before enrolment.</p>
    </div>
  </div>
</section>
