<?php /** @var array $pillars */ /** @var array $featured */ ?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <p class="eyebrow">Crawford Professionals Institute (CPI)</p>
      <h1>Empowering Skills, Transforming Lives.</h1>
      <p class="lead">Practical professional training, capacity building for institutions, and corporate training
        delivered directly to your organization — from a Kampala-based institute built for African professionals.</p>
      <div class="hero-actions">
        <a href="/courses" class="btn btn-primary">Browse Courses</a>
        <a href="/corporate-training" class="btn btn-outline">Corporate Training</a>
      </div>
      <div style="margin-top:24px">
        <span class="hero-badge">✓ Certificates verifiable online</span>
      </div>
    </div>
    <div>
      <img src="<?= asset('img/logo.png') ?>" alt="Crawford Professionals Institute crest" style="max-width:280px;margin:0 auto">
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container" style="max-width:760px;text-align:center">
    <p class="eyebrow">Achieve Your Goals with CPI</p>
    <p class="lead">In today's rapidly changing and competitive business environment, new technologies,
      management practices, industry trends, and professional demands are constantly emerging. Organizations
      must therefore continuously strengthen the knowledge, skills, and capabilities of their teams to remain
      effective, competitive, and prepared for change.</p>
    <p>At CPI, we provide practical, customized, and industry-relevant training solutions tailored to the
      specific needs of organizations and professionals. Our training programs are designed to strengthen
      workforce capacity, improve organizational performance, promote innovation, and equip teams with the
      skills needed to achieve their goals.</p>
    <p style="font-weight:700">With CPI, you don't just train your people — you build capacity, improve
      performance, and prepare your organization for the future.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <p class="eyebrow">What we offer</p>
    <h2>Three ways to learn with CPI</h2>
    <div class="grid-3" style="margin-top:28px">
      <?php foreach ($pillars as $p): ?>
        <a href="/<?= e($p['slug']) ?>" class="card pillar-card">
          <h3><?= e($p['name']) ?></h3>
          <p><?= e($p['description']) ?></p>
          <span style="font-weight:700;color:var(--crimson)">Explore &rarr;</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="dash-header">
      <div>
        <p class="eyebrow">Featured courses</p>
        <h2>Start learning this term</h2>
      </div>
      <a href="/courses" class="btn btn-outline">View full catalogue</a>
    </div>
    <div class="grid-3">
      <?php foreach ($featured as $c): ?>
        <a href="/courses/<?= e($c['slug']) ?>" class="card course-card">
          <span class="badge"><?= e(ucfirst($c['level'])) ?></span>
          <h3><?= e($c['title']) ?></h3>
          <p><?= e($c['summary']) ?></p>
          <div class="price"><?= money($c['price_amount'], $c['price_currency']) ?></div>
        </a>
      <?php endforeach; ?>
      <?php if (!$featured): ?><p>New courses are being added soon — check back shortly.</p><?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" style="text-align:center">
    <h2>Training your team? We come to you.</h2>
    <p class="lead">Request customized training for your organization — health, business, technology or a topic you name.</p>
    <a href="/corporate/request" class="btn btn-primary">Request Corporate Training</a>
  </div>
</section>
