<?php \App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'About us',
  'title' => 'About Crawford Professionals Institute',
  'lead' => 'A professional training and capacity-building institution equipping individuals and organizations with practical, industry-relevant skills.',
  'crumbs' => [['label' => 'About']],
  'stats' => [['icon' => 'fa-location-dot', 'text' => 'Kampala, Uganda'], ['icon' => 'fa-earth-africa', 'text' => 'Serving professionals across Africa']],
]); ?>
<section class="section">
  <div class="container" style="max-width:860px">
    <p class="eyebrow">Who we are</p>
    <h2>Building competence, strengthening institutions</h2>
    <p class="lead">Crawford Professionals Institute (CPI) is a leading professional training and
      capacity-building institution committed to equipping individuals and organizations with practical,
      industry-relevant skills for today's dynamic workplace.</p>
    <p>We deliver high-quality training across a wide range of sectors, including leadership, finance,
      public health, project management, procurement, human resources, ICT, governance, and monitoring
      &amp; evaluation.</p>
    <p>Our programmes are facilitated by experienced professionals using interactive, competency-based
      learning approaches that combine global best practices with real-world application. Whether delivered
      in-person, online, or through customized in-house programmes, CPI is dedicated to empowering
      professionals, enhancing organizational performance, and driving sustainable development across Africa.</p>
  </div>
</section>

<section class="section section-soft">
  <div class="container" style="max-width:860px">
    <p class="eyebrow">Why choose CPI</p>
    <h2>A trusted partner for professional development</h2>
    <p>CPI is a premier professional training and capacity-building institution dedicated to developing
      competent professionals, strengthening organizational performance, and promoting excellence across
      the public, private, and development sectors. We deliver high-impact, practical, and internationally
      benchmarked training programmes that address the evolving needs of today's workplace.</p>
    <p>With a commitment to quality, innovation, and professional excellence, CPI has positioned itself as a
      trusted partner for governments, NGOs, development agencies, financial institutions, healthcare
      organizations, academic institutions, and private enterprises seeking sustainable capacity development
      solutions.</p>
    <p>Our training programmes are designed and delivered by experienced consultants, industry practitioners,
      researchers, and subject-matter experts with extensive regional and international experience. Every
      programme combines current global best practices with practical, real-world applications, ensuring
      participants acquire knowledge and skills that can be implemented immediately within their organizations.</p>
    <p>At CPI, learning goes beyond the classroom. We employ interactive and participant-centered training
      methodologies — case studies, simulations, practical exercises, group discussions, workshops, coaching,
      and action planning — to maximize knowledge transfer and measurable learning outcomes. Our programmes are
      offered through physical, virtual, hybrid, and customized in-house delivery models, enabling organizations
      and individuals across Africa and beyond to access world-class professional development.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <p class="eyebrow">Our Competitive Advantage</p>
    <h2>What sets CPI apart</h2>
    <div class="grid-3" style="margin-top:28px">
      <?php $advantages = [
        'Internationally benchmarked training programmes',
        'Highly qualified and experienced facilitators',
        'Competency-based and practical learning approach',
        'Customized corporate and institutional training solutions',
        'Modern, interactive, and technology-enabled learning methods',
        'High-quality training materials and learning resources',
        'Professional networking opportunities across multiple industries',
        'Flexible delivery options — Physical, Virtual, Hybrid, and In-house',
        'Affordable, value-driven training solutions',
        'Commitment to continuous professional development and organizational transformation',
      ]; ?>
      <?php $advIcons = ['fa-earth-africa','fa-chalkboard-user','fa-list-check','fa-building','fa-laptop-code','fa-book','fa-people-arrows','fa-house-laptop','fa-hand-holding-dollar','fa-arrow-trend-up']; ?>
      <?php foreach ($advantages as $n => $a): ?>
        <div class="card value-card" data-reveal style="--i:<?= $n % 3 ?>"><span class="f-icon"><i class="fa-solid <?= $advIcons[$n] ?? 'fa-check' ?>"></i></span><p><?= e($a) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="grid-2">
      <div class="card mv-card" data-reveal>
        <h3><i class="fa-solid fa-bullseye"></i> Our Mission</h3>
        <p style="margin:0">To empower professionals and institutions through innovative, practical, and
          internationally recognized training programmes that enhance competence, improve organizational
          performance, and contribute to sustainable development.</p>
      </div>
      <div class="card mv-card" data-reveal style="--i:1">
        <h3><i class="fa-solid fa-eye"></i> Our Vision</h3>
        <p style="margin:0">To be Africa's leading professional training and capacity development institute,
          recognized for excellence, innovation, and transformative learning that creates lasting impact for
          individuals, organizations, and communities.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px;text-align:center">
    <p class="lead">At Crawford Professionals Institute (CPI), we do not simply deliver training — we build
      professional competence, strengthen institutions, develop leaders, and create lasting impact through
      knowledge, innovation, and excellence.</p>
    <div class="hero-actions" style="justify-content:center;margin-top:18px">
      <a href="/courses" class="btn btn-primary">Browse Courses <i class="fa-solid fa-arrow-right"></i></a>
      <a href="/corporate/request" class="btn btn-outline">Request Corporate Training</a>
    </div>
  </div>
</section>
