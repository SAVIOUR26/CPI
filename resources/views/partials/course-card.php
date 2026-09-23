<?php /** @var array $c */ /** @var int|null $i */ ?>
<a href="/courses/<?= e($c['slug']) ?>" class="card course-card" data-reveal style="--i:<?= (int) (($i ?? 0) % 6) ?>">
  <span class="course-cat">
    <i class="fa-solid <?= e(category_icon($c['category_slug'] ?? null)) ?>"></i>
    <span><?= e($c['category_name'] ?? ucfirst($c['level'] ?? 'Course')) ?></span>
  </span>
  <h3><?= e($c['title']) ?></h3>
  <?php if (!empty($c['summary'])): ?><p class="course-summary"><?= e($c['summary']) ?></p><?php endif; ?>
  <div class="course-meta">
    <span><i class="fa-solid fa-signal"></i><?= e(ucfirst($c['level'] ?? 'foundation')) ?></span>
    <?php if (!empty($c['duration_note'])): ?><span><i class="fa-regular fa-clock"></i><?= e($c['duration_note']) ?></span><?php endif; ?>
  </div>
  <div class="course-foot">
    <span class="price"><?= e(course_price($c)) ?></span>
    <span class="course-go" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
  </div>
</a>
