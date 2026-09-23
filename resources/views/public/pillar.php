<?php /** @var array $meta */ /** @var array $courses */ /** @var string $slug */
$actions = $slug === 'corporate-training'
  ? [['label' => 'Request customized training', 'href' => '/corporate/request', 'class' => 'btn-gold', 'icon' => 'fa-handshake'], ['label' => 'Browse full catalogue', 'href' => '/courses', 'class' => 'btn-ghost-light']]
  : [['label' => 'Browse full catalogue', 'href' => '/courses', 'class' => 'btn-gold', 'icon' => 'fa-magnifying-glass']];
\App\Core\View::partial('partials.page-hero', [
  'eyebrow' => 'Learning pathway',
  'title' => $meta['title'],
  'lead' => $meta['lead'],
  'crumbs' => [['label' => 'Programmes'], ['label' => $meta['title']]],
  'stats' => [['icon' => pillar_icon($slug), 'text' => count($courses) . ' courses in this pathway']],
  'actions' => $actions,
]);
?>
<section class="section">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($courses as $n => $c): ?>
        <?php \App\Core\View::partial('partials.course-card', ['c' => $c, 'i' => $n]); ?>
      <?php endforeach; ?>
      <?php if (!$courses): ?>
        <div class="card empty-state"><i class="fa-solid fa-book-open"></i><h3>Courses are being finalized</h3>
          <p>Check back shortly, or <a href="/corporate/request">request customized training</a>.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
