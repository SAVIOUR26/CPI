<?php /** @var array $announcements */ ?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Student portal</p>
    <h1>Announcements</h1>
    <p>News from CPI and from the lecturers of your classes.</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> All announcements</h2><span class="muted" style="font-size:.84rem"><?= count($announcements) ?> total</span></div>
  <div class="panel-body">
    <?php if ($announcements): ?>
      <?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements, 'showAudience' => true]); ?>
    <?php else: ?>
      <div class="empty-state"><i class="fa-solid fa-bullhorn"></i>No announcements yet. Anything CPI or your lecturers post will appear here.</div>
    <?php endif; ?>
  </div>
</div>
