<?php /** @var array $events */ /** @var array $timetable */ ?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Student portal</p>
    <h1>Academic calendar</h1>
    <p>Term dates, examinations, holidays and deadlines, plus your weekly class times.</p>
  </div>
</div>

<div class="calendar-grid">
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-days"></i> Key dates</h2></div>
    <div class="panel-body">
      <?php if ($events): ?>
        <?php \App\Core\View::partial('partials.portal.dates', ['events' => $events, 'grouped' => true]); ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-calendar-days"></i>No upcoming dates yet. CPI will publish term, exam and holiday dates here.</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="panel">
    <div class="panel-head"><h2><i class="fa-solid fa-calendar-week"></i> Weekly timetable</h2></div>
    <?php if ($timetable): ?>
      <?php \App\Core\View::partial('partials.portal.timetable', ['timetable' => $timetable, 'showCourse' => true]); ?>
    <?php else: ?>
      <div class="panel-body"><div class="empty-state"><i class="fa-solid fa-calendar-week"></i>Your class times will appear here once they are scheduled.</div></div>
    <?php endif; ?>
  </div>
</div>
