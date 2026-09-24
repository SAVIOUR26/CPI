<?php
use App\Support\Institute;

\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Get in touch',
  'title' => 'Contact CPI',
  'lead' => 'Questions about a course, corporate training or an academic programme? Call, WhatsApp or email us — we\'re here to help.',
  'crumbs' => [['label' => 'Contact']],
  'stats' => [['icon' => 'fa-location-dot', 'text' => Institute::LOCATION], ['icon' => 'fa-earth-africa', 'text' => 'Training coverage: ' . Institute::COVERAGE]],
]); ?>
<section class="section">
  <div class="container">
    <div class="contact-cards">
      <div class="card contact-card" data-reveal>
        <span class="icon-badge"><i class="fa-solid fa-phone"></i></span>
        <h3>Call us</h3>
        <p>Talk to our team about courses, fees and admissions.</p>
        <?php foreach (Institute::PHONES as $phone): ?>
          <a href="<?= e(Institute::tel($phone)) ?>" class="contact-big"><?= e($phone) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="card contact-card" data-reveal style="--i:1">
        <span class="icon-badge icon-whatsapp"><i class="fa-brands fa-whatsapp"></i></span>
        <h3>WhatsApp</h3>
        <p>Chat with us for quick answers.</p>
        <span class="contact-big"><?= e(Institute::WHATSAPP) ?></span>
        <a href="<?= e(Institute::whatsappUrl()) ?>" class="btn btn-whatsapp btn-sm" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
      </div>
      <div class="card contact-card" data-reveal style="--i:2">
        <span class="icon-badge"><i class="fa-solid fa-envelope"></i></span>
        <h3>Email us</h3>
        <p>For course, admissions and general enquiries.</p>
        <a href="mailto:<?= e(Institute::EMAIL) ?>" class="link-arrow"><?= e(Institute::EMAIL) ?></a>
      </div>
      <div class="card contact-card" data-reveal style="--i:3">
        <span class="icon-badge"><i class="fa-solid fa-location-dot"></i></span>
        <h3>Where we are</h3>
        <p>Crawford Professionals Institute</p>
        <strong class="contact-big"><?= e(Institute::LOCATION) ?></strong>
        <p class="contact-note"><i class="fa-solid fa-earth-africa"></i> Training coverage: <?= e(Institute::COVERAGE) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <?php \App\Core\View::partial('partials.training-modes', []); ?>
    <div class="modes-cta" data-reveal>
      <p><strong>Planning training for your team?</strong> Tell us what you need and we'll send a tailored proposal.</p>
      <div class="hero-actions">
        <a href="/corporate/request" class="btn btn-primary"><i class="fa-solid fa-handshake"></i> Request a proposal</a>
        <a href="/corporate-training" class="btn btn-outline">Corporate training</a>
      </div>
    </div>
  </div>
</section>
