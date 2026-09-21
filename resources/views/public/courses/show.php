<?php /** @var array $course */ /** @var array $intakes */ /** @var array $pillars */ ?>
<section class="section">
  <div class="container" style="max-width:900px">
    <p class="eyebrow"><?= e(ucfirst($course['level'])) ?> &middot; <?= e($course['duration_note']) ?></p>
    <h1><?= e($course['title']) ?></h1>
    <p class="lead"><?= e($course['summary']) ?></p>

    <?php if ($pillars): ?>
      <div style="margin-bottom:20px">
        <?php foreach ($pillars as $p): ?><span class="badge" style="margin-right:6px"><?= e($p['name']) ?></span><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div style="white-space:pre-line"><?= nl2br(e($course['description'])) ?></div>

    <h2 style="margin-top:40px">Upcoming Intakes</h2>
    <?php if (!$intakes): ?>
      <p>No open intakes right now. <a href="/corporate/request">Request customized training</a> or check back soon.</p>
    <?php endif; ?>
    <div class="grid-2">
      <?php foreach ($intakes as $i): ?>
        <div class="card">
          <h3><?= e($i['code']) ?></h3>
          <p>
            <strong>Starts:</strong> <?= date_pretty($i['start_date']) ?><br>
            <strong>Mode:</strong> <?= e(ucfirst(str_replace('_', ' ', $i['mode']))) ?><br>
            <?php if ($i['venue']): ?><strong>Venue:</strong> <?= e($i['venue']) ?><br><?php endif; ?>
            <strong>Price:</strong> <?= money($course['price_amount'], $course['price_currency']) ?>
          </p>
          <form method="post" action="/enroll/<?= (int) $i['id'] ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-block">Enroll in this intake</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
