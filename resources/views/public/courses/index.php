<?php
/** @var array $courses */ /** @var array $categories */ /** @var ?string $activeCategory */
/** @var int $total */ /** @var int $page */ /** @var int $pages */
$activeName = null;
foreach ($categories as $cat) { if ($cat['slug'] === $activeCategory) { $activeName = $cat['name']; } }
$pageUrl = function (int $p) use ($activeCategory, $search): string {
    $q = array_filter(['category' => $activeCategory, 'q' => $search, 'page' => $p > 1 ? $p : null], fn ($v) => $v !== null && $v !== '');
    return '/courses' . ($q ? '?' . http_build_query($q) : '');
};
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Course catalogue',
  'title' => $activeName ?? 'Explore our courses',
  'lead' => 'Practical, industry-relevant programmes across ' . count($categories) . ' fields — for individuals, teams and institutions.',
  'crumbs' => $activeName ? [['label' => 'Courses', 'href' => '/courses'], ['label' => $activeName]] : [['label' => 'Courses']],
]);
?>
<section class="section">
  <div class="container">
    <div class="catalogue-toolbar">
      <form method="get" action="/courses" class="search-bar" role="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <?php if ($activeCategory): ?><input type="hidden" name="category" value="<?= e($activeCategory) ?>"><?php endif; ?>
        <input type="search" name="q" placeholder="Search by course title or topic…" value="<?= e($search ?? '') ?>" aria-label="Search courses">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
      </form>
      <div class="filters">
        <a href="<?= $search ? '/courses?q=' . urlencode($search) : '/courses' ?>" class="<?= !$activeCategory ? 'active' : '' ?>"><i class="fa-solid fa-border-all"></i> All fields</a>
        <?php foreach ($categories as $cat): ?>
          <a href="/courses?<?= e(http_build_query(array_filter(['category' => $cat['slug'], 'q' => $search]))) ?>" class="<?= $activeCategory === $cat['slug'] ? 'active' : '' ?>"><i class="fa-solid <?= e(category_icon($cat['slug'])) ?>"></i> <?= e($cat['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="results-meta">
      <span>Showing <strong><?= $total ? (($page - 1) * 24 + 1) . '–' . min($total, $page * 24) : 0 ?></strong> of <strong><?= (int) $total ?></strong> course<?= $total === 1 ? '' : 's' ?><?= $search ? ' for “' . e($search) . '”' : '' ?></span>
      <?php if ($search || $activeCategory): ?><a href="/courses" class="link-arrow"><i class="fa-solid fa-xmark"></i> Clear filters</a><?php endif; ?>
    </div>

    <div class="grid-3">
      <?php foreach ($courses as $n => $c): ?>
        <?php \App\Core\View::partial('partials.course-card', ['c' => $c, 'i' => $n]); ?>
      <?php endforeach; ?>
      <?php if (!$courses): ?>
        <div class="card empty-state"><i class="fa-solid fa-magnifying-glass"></i><h3>No courses match your search</h3>
          <p>Try a different keyword or field — or <a href="/corporate/request">request customized training</a>.</p></div>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Pagination">
        <?php if ($page > 1): ?><a href="<?= e($pageUrl($page - 1)) ?>"><i class="fa-solid fa-chevron-left"></i> Prev</a><?php else: ?><span class="disabled"><i class="fa-solid fa-chevron-left"></i> Prev</span><?php endif; ?>
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === 1 || $p === $pages || abs($p - $page) <= 1): ?>
            <?php if ($p === $page): ?><span class="current" aria-current="page"><?= $p ?></span><?php else: ?><a href="<?= e($pageUrl($p)) ?>"><?= $p ?></a><?php endif; ?>
          <?php elseif (abs($p - $page) === 2): ?><span class="gap">…</span><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $pages): ?><a href="<?= e($pageUrl($page + 1)) ?>">Next <i class="fa-solid fa-chevron-right"></i></a><?php else: ?><span class="disabled">Next <i class="fa-solid fa-chevron-right"></i></span><?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
