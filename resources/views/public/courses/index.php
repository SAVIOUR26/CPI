<?php /** @var array $courses */ /** @var array $categories */ /** @var ?string $activeCategory */ ?>
<section class="section">
  <div class="container">
    <p class="eyebrow">Course Catalogue</p>
    <h1>All Courses</h1>

    <form method="get" action="/courses" style="max-width:420px;margin-bottom:20px">
      <input type="text" name="q" placeholder="Search courses…" value="<?= e($search ?? '') ?>">
    </form>

    <div class="filters">
      <a href="/courses" class="<?= !$activeCategory ? 'active' : '' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
        <a href="/courses?category=<?= e($cat['slug']) ?>" class="<?= $activeCategory === $cat['slug'] ? 'active' : '' ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="grid-3">
      <?php foreach ($courses as $c): ?>
        <a href="/courses/<?= e($c['slug']) ?>" class="card course-card">
          <span class="badge"><?= e($c['category_name'] ?? ucfirst($c['level'])) ?></span>
          <h3><?= e($c['title']) ?></h3>
          <p><?= e($c['summary']) ?></p>
          <div class="price"><?= money($c['price_amount'], $c['price_currency']) ?></div>
        </a>
      <?php endforeach; ?>
      <?php if (!$courses): ?><p>No courses match your search.</p><?php endif; ?>
    </div>
  </div>
</section>
