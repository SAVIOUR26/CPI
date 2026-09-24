<?php
/** @var array $events calendar_events rows, soonest first */
use App\Models\CalendarEvent;
$grouped = $grouped ?? false;
$month = null;
?>
<ul class="date-list">
  <?php foreach ($events as $ev): ?>
    <?php [$label, $icon, $tone] = CalendarEvent::category($ev['category']); ?>
    <?php if ($grouped && ($m = date('F Y', strtotime($ev['starts_on']))) !== $month): $month = $m; ?>
      <li class="date-month"><?= e($month) ?></li>
    <?php endif; ?>
    <li class="date-item">
      <span class="date-badge"><strong><?= date('j', strtotime($ev['starts_on'])) ?></strong><small><?= date('M', strtotime($ev['starts_on'])) ?></small></span>
      <div>
        <strong><?= e($ev['title']) ?></strong>
        <small><span class="date-cat <?= e($tone) ?>"><i class="fa-solid <?= e($icon) ?>"></i> <?= e($label) ?></span>
          <?= e(CalendarEvent::dateRange($ev)) ?><?= $ev['intake_code'] ? ' · ' . e($ev['course_title'] . ' (' . $ev['intake_code'] . ')') : '' ?></small>
        <?php if (!empty($ev['notes'])): ?><p><?= e($ev['notes']) ?></p><?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
