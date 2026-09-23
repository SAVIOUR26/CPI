<?php \App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Get in touch',
  'title' => 'Contact CPI',
  'lead' => 'Have a question about a course, a corporate training need, or an academic programme? We\'re here to help.',
  'crumbs' => [['label' => 'Contact']],
]); ?>
<section class="section">
  <div class="container">
    <div class="contact-cards">
      <div class="card contact-card" data-reveal>
        <span class="icon-badge"><i class="fa-solid fa-envelope"></i></span>
        <h3>Email us</h3>
        <p>For course, admissions and general enquiries.</p>
        <a href="mailto:info@crawfordinstitute.online" class="link-arrow">info@crawfordinstitute.online</a>
      </div>
      <div class="card contact-card" data-reveal style="--i:1">
        <span class="icon-badge"><i class="fa-solid fa-location-dot"></i></span>
        <h3>Visit us</h3>
        <p>Crawford Professionals Institute</p>
        <strong>Kampala, Uganda</strong>
      </div>
      <div class="card contact-card" data-reveal style="--i:2">
        <span class="icon-badge"><i class="fa-solid fa-handshake"></i></span>
        <h3>Corporate training</h3>
        <p>Training for your team, designed around your needs.</p>
        <a href="/corporate/request" class="link-arrow">Request a proposal <i class="fa-solid fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>
