<?php /** @var array $course */ /** @var array $intakes */ /** @var array $pillars */ /** @var ?array $category */
$crumbs = [['label' => 'Courses', 'href' => '/courses']];
if ($category) { $crumbs[] = ['label' => $category['name'], 'href' => '/courses?category=' . $category['slug']]; }
$crumbs[] = ['label' => $course['title']];
$stats = [['icon' => 'fa-signal', 'text' => ucfirst($course['level']) . ' level']];
if ($course['duration_note']) { $stats[] = ['icon' => 'fa-clock', 'text' => $course['duration_note']]; }
foreach ($pillars as $p) { $stats[] = ['icon' => pillar_icon($p['slug']), 'text' => $p['name']]; }
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => $category['name'] ?? 'Course',
  'title' => $course['title'],
  'lead' => $course['summary'],
  'crumbs' => $crumbs,
  'stats' => $stats,
]);
?>
<section class="section">
  <div class="container course-layout">
    <div>
      <p class="eyebrow">About this course</p>
      <h2>Course overview</h2>
      <div class="prose"><?= nl2br(e($course['description'] ?: $course['summary'])) ?></div>

      <h2 style="margin-top:48px" id="intakes">Upcoming intakes</h2>
      <?php if (!$intakes): ?>
        <div class="card empty-state"><i class="fa-regular fa-calendar"></i><h3>No open intakes right now</h3>
          <p>New dates are added regularly. Need this for your team? <a href="/corporate/request">Request customized training</a>.</p></div>
      <?php endif; ?>
      <div class="grid-2">
        <?php foreach ($intakes as $i): ?>
          <div class="card intake-card">
            <div class="intake-top"><h3><?= e($i['code']) ?></h3><span class="status status-open">Open</span></div>
            <ul class="intake-facts">
              <li><i class="fa-regular fa-calendar"></i>Starts <?= date_pretty($i['start_date']) ?></li>
              <li><i class="fa-solid fa-laptop"></i><?= e(ucfirst(str_replace('_', ' ', $i['mode']))) ?></li>
              <?php if ($i['venue']): ?><li><i class="fa-solid fa-location-dot"></i><?= e($i['venue']) ?></li><?php endif; ?>
              <li><i class="fa-solid fa-tag"></i><?= e(course_price($course)) ?></li>
            </ul>
            <form method="post" action="/enroll/<?= (int) $i['id'] ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-primary btn-block">Enrol in this intake <i class="fa-solid fa-arrow-right"></i></button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="course-aside">
      <div class="card">
        <p class="eyebrow">Course fee</p>
        <div class="price-tag"><?= e(course_price($course)) ?></div>
        <?php if ((float) ($course['price_amount'] ?? 0) <= 0): ?><p class="price-note">Pricing is tailored to your group size and delivery mode.</p><?php endif; ?>
        <ul class="facts">
          <li><i class="fa-solid fa-signal"></i><div><small>Level</small><strong><?= e(ucfirst($course['level'])) ?></strong></div></li>
          <?php if ($course['duration_note']): ?><li><i class="fa-regular fa-clock"></i><div><small>Duration</small><strong><?= e($course['duration_note']) ?></strong></div></li><?php endif; ?>
          <?php if ($category): ?><li><i class="fa-solid <?= e(category_icon($category['slug'])) ?>"></i><div><small>Field</small><strong><?= e($category['name']) ?></strong></div></li><?php endif; ?>
          <li><i class="fa-solid fa-shield-halved"></i><div><small>Certificate</small><strong>Verifiable online</strong></div></li>
        </ul>
        <?php if ($intakes): ?>
          <a href="#intakes" class="btn btn-primary btn-block">View intakes &amp; enrol</a>
        <?php else: ?>
          <a href="/corporate/request" class="btn btn-primary btn-block">Request this training</a>
        <?php endif; ?>
        <a href="/contact" class="btn btn-outline btn-block" style="margin-top:10px">Ask a question</a>
      </div>
    </aside>
  </div>
</section>
