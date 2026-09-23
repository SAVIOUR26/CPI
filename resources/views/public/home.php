<?php
/** @var array $pillars */
/** @var array $pillarCounts */
/** @var array $categories */
/** @var int $courseCount */
/** @var array $featured */
$floatingIcons = [
  ['fa-graduation-cap', '4%', '26s', '-2s', '1.9rem', '', '.14'],
  ['fa-book-open', '12%', '32s', '-18s', '1.3rem', 'gold', '.2'],
  ['fa-lightbulb', '20%', '24s', '-9s', '1.5rem', '', '.12'],
  ['fa-award', '29%', '30s', '-24s', '1.4rem', 'gold', '.22'],
  ['fa-chart-line', '37%', '28s', '-4s', '1.2rem', '', '.1'],
  ['fa-earth-africa', '46%', '36s', '-15s', '2.1rem', '', '.08'],
  ['fa-certificate', '54%', '27s', '-21s', '1.3rem', 'gold', '.2'],
  ['fa-users', '62%', '31s', '-7s', '1.5rem', '', '.12'],
  ['fa-laptop', '70%', '25s', '-13s', '1.4rem', '', '.1'],
  ['fa-briefcase', '78%', '29s', '-27s', '1.3rem', 'gold', '.18'],
  ['fa-handshake', '86%', '33s', '-11s', '1.6rem', '', '.12'],
  ['fa-brain', '93%', '27s', '-19s', '1.3rem', 'gold', '.18'],
  ['fa-scale-balanced', '8%', '38s', '-30s', '1.2rem', '', '.08'],
  ['fa-heart-pulse', '66%', '35s', '-32s', '1.1rem', '', '.1'],
];
$pillarText = [
  'professional-training' => 'Short, practical courses and certifications for individuals investing in their own careers.',
  'capacity-building' => 'Programmes that strengthen institutional and staff capacity across NGOs, government and projects.',
  'corporate-training' => 'Enrol your staff from our catalogue, or let us design training around your team\'s needs.',
];
$heroChips = $categories;
usort($heroChips, fn ($a, $b) => $b['course_count'] <=> $a['course_count']);
$heroChips = array_slice($heroChips, 0, 4);
?>

<section class="hero">
  <div class="hero-bg" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <span class="orb orb-3"></span>
    <div class="bg-dots"></div>
    <div class="floating-icons">
      <?php foreach ($floatingIcons as [$icon, $x, $d, $delay, $size, $tone, $o]): ?>
        <i class="fa-solid <?= $icon ?> <?= $tone ?>" style="--x:<?= $x ?>;--d:<?= $d ?>;--delay:<?= $delay ?>;--s:<?= $size ?>;--o:<?= $o ?>"></i>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="hero-kicker"><span class="pulse-dot"></span> Professional Training &middot; Capacity Building &middot; Corporate Training</span>
      <h1>Empowering Skills,<br><span class="text-gradient">Transforming Lives.</span></h1>
      <p class="lead">Practical, industry-relevant training for professionals and organizations — delivered in person, online or in-house
        by a Kampala-based institute built for African professionals.</p>
      <form class="hero-search" action="/courses" method="get" role="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" name="q" placeholder="What do you want to learn?" aria-label="Search courses">
        <button type="submit" class="btn btn-primary">Search</button>
      </form>
      <div class="hero-actions">
        <a href="/courses" class="btn btn-gold btn-lg">Browse <?= (int) $courseCount ?> Courses <i class="fa-solid fa-arrow-right"></i></a>
        <a href="/corporate-training" class="btn btn-ghost-light btn-lg"><i class="fa-solid fa-building"></i> Corporate Training</a>
      </div>
      <ul class="hero-trust">
        <li><i class="fa-solid fa-shield-halved"></i> Certificates verifiable online</li>
        <li><i class="fa-solid fa-mobile-screen-button"></i> Pay by Mobile Money or card</li>
        <li><i class="fa-solid fa-house-laptop"></i> Online, in-person &amp; hybrid</li>
      </ul>
    </div>

    <div class="hero-visual" aria-hidden="true">
      <div class="glass hero-card">
        <div class="hc-head">
          <span class="hc-icon"><i class="fa-solid fa-graduation-cap"></i></span>
          <div><small>Course catalogue</small><strong><span data-count="<?= (int) $courseCount ?>"><?= (int) $courseCount ?></span> courses</strong></div>
        </div>
        <div class="hc-stats">
          <div class="hc-stat"><strong data-count="<?= count($categories) ?>"><?= count($categories) ?></strong><span>Fields of study</span></div>
          <div class="hc-stat"><strong data-count="<?= count($pillars) ?>"><?= count($pillars) ?></strong><span>Learning pathways</span></div>
          <div class="hc-stat"><strong data-count="4">4</strong><span>Delivery modes</span></div>
        </div>
        <div class="hc-chips">
          <?php foreach ($heroChips as $cat): ?>
            <a href="/courses?category=<?= e($cat['slug']) ?>" tabindex="-1"><?= e($cat['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="hero-float hf-1">
        <span class="hf-icon green"><i class="fa-solid fa-circle-check"></i></span>
        <div><strong>Verified certificates</strong><small>QR-coded, checkable online</small></div>
      </div>
      <div class="hero-float hf-2">
        <span class="hf-icon gold"><i class="fa-solid fa-mobile-screen-button"></i></span>
        <div><strong>MTN &amp; Airtel Money</strong><small>Cards and bank transfer too</small></div>
      </div>
    </div>
  </div>

  <div class="marquee">
    <div class="marquee-track">
      <?php for ($loop = 0; $loop < 2; $loop++): ?>
        <?php foreach ($categories as $cat): ?>
          <a href="/courses?category=<?= e($cat['slug']) ?>" class="marquee-item"<?= $loop ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
            <i class="fa-solid <?= e(category_icon($cat['slug'])) ?>"></i> <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">What we offer</p>
      <h2>Three pathways to grow with CPI</h2>
      <p>Whether you are advancing your own career or building the capability of an entire organization, there is a pathway designed for you.</p>
    </div>
    <div class="grid-3">
      <?php foreach ($pillars as $n => $p): ?>
        <a href="/<?= e($p['slug']) ?>" class="card pillar-card" data-reveal style="--i:<?= (int) $n ?>">
          <span class="icon-badge"><i class="fa-solid <?= e(pillar_icon($p['slug'])) ?>"></i></span>
          <h3><?= e($p['name']) ?></h3>
          <p><?= e($pillarText[$p['slug']] ?? $p['description']) ?></p>
          <div class="pillar-meta">
            <span class="count"><i class="fa-solid fa-book-open"></i> <?= (int) ($pillarCounts[(int) $p['id']] ?? 0) ?> courses</span>
            <span class="link-arrow">Explore <i class="fa-solid fa-arrow-right"></i></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head split" data-reveal>
      <div>
        <p class="eyebrow">Explore by field</p>
        <h2><?= count($categories) ?> fields of professional study</h2>
        <p>From finance and public health to AI and leadership — find the programme that fits your goals.</p>
      </div>
      <a href="/courses" class="btn btn-outline">View all courses <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="category-grid">
      <?php foreach ($categories as $n => $cat): ?>
        <a href="/courses?category=<?= e($cat['slug']) ?>" class="category-card" data-reveal style="--i:<?= $n % 4 ?>">
          <span class="cat-icon"><i class="fa-solid <?= e(category_icon($cat['slug'])) ?>"></i></span>
          <span class="cat-text"><strong><?= e($cat['name']) ?></strong><small><?= (int) $cat['course_count'] ?> course<?= (int) $cat['course_count'] === 1 ? '' : 's' ?></small></span>
          <i class="fa-solid fa-arrow-right cat-arrow"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container split-grid">
    <div class="about-copy" data-reveal>
      <p class="eyebrow">Achieve your goals with CPI</p>
      <h2>Training that builds capacity, not just certificates</h2>
      <p class="lead">In today's rapidly changing and competitive business environment, new technologies, management practices,
        industry trends and professional demands are constantly emerging.</p>
      <p>At CPI, we provide practical, customized and industry-relevant training solutions tailored to the specific needs of
        organizations and professionals — designed to strengthen workforce capacity, improve organizational performance and
        equip teams with the skills they need to achieve their goals.</p>
      <p class="about-quote"><i class="fa-solid fa-quote-left"></i>With CPI, you don't just train your people — you build capacity,
        improve performance, and prepare your organization for the future.</p>
    </div>
    <div class="feature-list">
      <?php
      $features = [
        ['fa-earth-africa', 'Internationally benchmarked', 'Global best practice, applied to the African workplace.'],
        ['fa-chalkboard-user', 'Expert facilitators', 'Experienced consultants and industry practitioners.'],
        ['fa-list-check', 'Competency-based', 'Practical, hands-on learning you can apply immediately.'],
        ['fa-building', 'Tailored for organizations', 'Customized corporate and institutional programmes.'],
        ['fa-house-laptop', 'Flexible delivery', 'Physical, virtual, hybrid and in-house options.'],
        ['fa-hand-holding-dollar', 'Value-driven', 'Affordable training with measurable outcomes.'],
      ];
      foreach ($features as $n => [$icon, $title, $text]): ?>
        <div class="feature" data-reveal style="--i:<?= $n ?>">
          <span class="f-icon"><i class="fa-solid <?= $icon ?>"></i></span>
          <div><h4><?= e($title) ?></h4><p><?= e($text) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-warm">
  <div class="container">
    <div class="section-head center" data-reveal>
      <p class="eyebrow">How it works</p>
      <h2>From enrolment to certificate in four steps</h2>
      <p>A simple, fully online journey — from finding the right course to earning a certificate employers can verify.</p>
    </div>
    <div class="steps">
      <?php
      $steps = [
        ['fa-magnifying-glass', 'Find your course', 'Browse ' . $courseCount . ' courses or search by field and level.'],
        ['fa-calendar-check', 'Enrol in an intake', 'Choose an intake date and delivery mode that suits you.'],
        ['fa-credit-card', 'Pay securely', 'Mobile Money, card or bank transfer — confirmed quickly.'],
        ['fa-award', 'Learn & get certified', 'Study in your learner portal and earn a verifiable certificate.'],
      ];
      foreach ($steps as $n => [$icon, $title, $text]): ?>
        <div class="step" data-reveal style="--i:<?= $n ?>">
          <div class="step-icon"><i class="fa-solid <?= $icon ?>"></i><span class="step-num"><?= $n + 1 ?></span></div>
          <h4><?= e($title) ?></h4>
          <p><?= e($text) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head split" data-reveal>
      <div>
        <p class="eyebrow">Featured courses</p>
        <h2>Start learning this term</h2>
        <p>A selection from across our fields of study.</p>
      </div>
      <a href="/courses" class="btn btn-outline">View full catalogue <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="grid-3">
      <?php foreach ($featured as $n => $c): ?>
        <?php \App\Core\View::partial('partials.course-card', ['c' => $c, 'i' => $n]); ?>
      <?php endforeach; ?>
      <?php if (!$featured): ?>
        <div class="empty-state"><i class="fa-solid fa-book-open"></i><h3>New courses coming soon</h3><p>Check back shortly.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-tight">
  <div class="container">
    <div class="verify-band" data-reveal>
      <span class="vb-icon"><i class="fa-solid fa-shield-halved"></i></span>
      <div>
        <h3>Verify a CPI certificate</h3>
        <p>Employers and institutions can confirm any CPI certificate instantly using the code printed on it.</p>
      </div>
      <form class="inline-form" action="/verify/lookup" method="get">
        <input type="text" name="code" placeholder="e.g. CPI-2026-000123" aria-label="Certificate code" required>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Verify</button>
      </form>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band" data-reveal>
      <div class="hero-bg" aria-hidden="true">
        <span class="orb orb-2"></span>
        <div class="bg-dots"></div>
      </div>
      <div>
        <p class="eyebrow" style="color:var(--gold-300)">Corporate &amp; institutional training</p>
        <h2>Training your team? We come to you.</h2>
        <p>Request customized training for your organization — health, business, technology or a topic you name. We'll design,
          quote and deliver it on your schedule.</p>
      </div>
      <div class="cta-actions">
        <a href="/corporate/request" class="btn btn-gold btn-lg">Request Corporate Training <i class="fa-solid fa-arrow-right"></i></a>
        <a href="/contact" class="btn btn-ghost-light btn-lg">Talk to us</a>
      </div>
    </div>
  </div>
</section>
