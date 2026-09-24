<?php
/** @var array $events */ /** @var array $intakes */ /** @var array|null $selectedIntake */ /** @var array $slots */ /** @var array $lecturers */
use App\Models\CalendarEvent;
use App\Models\Timetable;

$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
$today = date('Y-m-d');
$upcoming = array_reverse(array_values(array_filter($events, fn ($ev) => ($ev['ends_on'] ?: $ev['starts_on']) >= $today)));
$past = array_values(array_filter($events, fn ($ev) => ($ev['ends_on'] ?: $ev['starts_on']) < $today));
$classLabel = fn (array $i): string => $i['course_title'] . ' (' . $i['code'] . ')';

$eventRows = function (array $rows): void {
    foreach ($rows as $ev) {
        [$label, $icon, $tone] = CalendarEvent::category($ev['category']); ?>
        <tr>
          <td class="nowrap"><strong><?= e(CalendarEvent::dateRange($ev)) ?></strong></td>
          <td><strong><?= e($ev['title']) ?></strong><?php if ($ev['notes']): ?><span class="cell-sub"><?= e($ev['notes']) ?></span><?php endif; ?></td>
          <td><span class="date-cat <?= e($tone) ?>"><i class="fa-solid <?= e($icon) ?>"></i> <?= e($label) ?></span></td>
          <td><?= $ev['intake_id'] ? e($ev['course_title'] . ' (' . $ev['intake_code'] . ')') : '<span class="muted">All students</span>' ?></td>
          <td class="cell-actions">
            <form method="post" action="/admin/calendar/events/<?= (int) $ev['id'] ?>/delete" data-confirm-submit="Remove this date from the calendar?">
              <?= csrf_field() ?>
              <button type="submit" class="btn-icon" title="Remove" aria-label="Remove <?= e($ev['title']) ?>"><i class="fa-regular fa-trash-can"></i></button>
            </form>
          </td>
        </tr>
    <?php }
};
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Academic</p>
    <h1>Calendar &amp; timetable</h1>
    <p>Key dates show in every student's Academic Calendar, or only in one class's. Each class's weekly timetable shows in its students' portal.</p>
  </div>
</div>

<nav class="section-nav" aria-label="Sections">
  <a href="#dates">Key dates</a><a href="#timetable">Weekly timetable</a>
</nav>

<div class="panel" id="dates">
  <div class="panel-head"><h2><i class="fa-solid fa-calendar-plus"></i> Add a key date</h2></div>
  <div class="panel-body">
    <form method="post" action="/admin/calendar/events">
      <?= csrf_field() ?>
      <div class="form-row">
        <div class="form-group">
          <label for="ev_title">Title</label>
          <input id="ev_title" type="text" name="title" maxlength="190" value="<?= e(old('title')) ?>" required placeholder="e.g. End-of-term examinations">
          <?= $err('title') ?>
        </div>
        <div class="form-group">
          <label for="ev_category">Type</label>
          <select id="ev_category" name="category" required>
            <?php foreach (CalendarEvent::CATEGORIES as $key => [$label]): ?>
              <option value="<?= e($key) ?>"<?= old('category', 'term') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('category') ?>
        </div>
      </div>
      <div class="form-row three">
        <div class="form-group">
          <label for="ev_start">Date (or first day)</label>
          <input id="ev_start" type="date" name="starts_on" value="<?= e(old('starts_on')) ?>" required>
          <?= $err('starts_on') ?>
        </div>
        <div class="form-group">
          <label for="ev_end">Last day <span class="muted">(optional)</span></label>
          <input id="ev_end" type="date" name="ends_on" value="<?= e(old('ends_on')) ?>">
          <?= $err('ends_on') ?>
        </div>
        <div class="form-group">
          <label for="ev_intake">For</label>
          <select id="ev_intake" name="intake_id">
            <option value="">All students</option>
            <?php foreach ($intakes as $i): ?>
              <option value="<?= (int) $i['id'] ?>"<?= old('intake_id') === (string) $i['id'] ? ' selected' : '' ?>><?= e($classLabel($i)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="ev_notes">Notes <span class="muted">(optional)</span></label>
        <input id="ev_notes" type="text" name="notes" maxlength="500" value="<?= e(old('notes')) ?>" placeholder="e.g. Venue, reporting time or what to bring">
        <?= $err('notes') ?>
      </div>
      <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-calendar-plus"></i> Add to calendar</button></div>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-calendar-days"></i> Upcoming dates</h2><span class="muted" style="font-size:.82rem"><?= count($upcoming) ?> upcoming</span></div>
  <div class="table-card">
    <table>
      <thead><tr><th>Date</th><th>Title</th><th>Type</th><th>For</th><th></th></tr></thead>
      <tbody>
        <?php $eventRows($upcoming); ?>
        <?php if (!$upcoming): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-calendar-days"></i>No upcoming dates. Add term dates, examinations, holidays and deadlines above.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($past): ?>
    <details class="past-dates">
      <summary>Past dates (<?= count($past) ?>)</summary>
      <div class="table-card">
        <table>
          <thead><tr><th>Date</th><th>Title</th><th>Type</th><th>For</th><th></th></tr></thead>
          <tbody><?php $eventRows($past); ?></tbody>
        </table>
      </div>
    </details>
  <?php endif; ?>
</div>

<div class="panel" id="timetable">
  <div class="panel-head"><h2><i class="fa-solid fa-calendar-week"></i> Weekly timetable</h2></div>
  <div class="panel-body">
    <form method="get" action="/admin/calendar#timetable" class="class-picker">
      <label for="tt_class">Class</label>
      <select id="tt_class" name="intake">
        <option value="">Choose a class…</option>
        <?php foreach ($intakes as $i): ?>
          <option value="<?= (int) $i['id'] ?>"<?= $selectedIntake && (int) $selectedIntake['id'] === (int) $i['id'] ? ' selected' : '' ?>><?= e($classLabel($i)) ?> · starts <?= date_pretty($i['start_date']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-outline btn-sm">Open</button>
    </form>

    <?php if (!$selectedIntake): ?>
      <div class="empty-state"><i class="fa-solid fa-calendar-week"></i>Choose a class to see and edit its weekly class times.</div>
    <?php else: ?>
      <div class="table-card">
        <table>
          <thead><tr><th>Day</th><th>Time</th><th>Venue</th><th>Lecturer</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($slots as $t): ?>
            <tr>
              <td><strong><?= e(Timetable::dayName((int) $t['day_of_week'])) ?></strong></td>
              <td><?= e(substr($t['start_time'], 0, 5)) ?>–<?= e(substr($t['end_time'], 0, 5)) ?></td>
              <td><?= e($t['venue'] ?: '—') ?></td>
              <td><?= e($t['lecturer_name'] ?: '—') ?></td>
              <td class="cell-actions">
                <form method="post" action="/admin/calendar/slots/<?= (int) $t['id'] ?>/delete" data-confirm-submit="Remove this class time?">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-icon" title="Remove" aria-label="Remove class time"><i class="fa-regular fa-trash-can"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$slots): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-clock"></i>No class times yet for <?= e($classLabel($selectedIntake)) ?>.</div></td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <h3 class="subhead">Add a class time</h3>
      <form method="post" action="/admin/calendar/slots" class="slot-form">
        <?= csrf_field() ?>
        <input type="hidden" name="intake_id" value="<?= (int) $selectedIntake['id'] ?>">
        <div class="form-group">
          <label for="slot_day">Day</label>
          <select id="slot_day" name="day_of_week" required>
            <?php foreach (Timetable::days() as $n => $day): ?>
              <option value="<?= $n ?>"<?= old('day_of_week') === (string) $n ? ' selected' : '' ?>><?= e($day) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="slot_start">Starts</label>
          <input id="slot_start" type="time" name="start_time" value="<?= e(old('start_time', '09:00')) ?>" required>
        </div>
        <div class="form-group">
          <label for="slot_end">Ends</label>
          <input id="slot_end" type="time" name="end_time" value="<?= e(old('end_time', '11:00')) ?>" required>
        </div>
        <div class="form-group">
          <label for="slot_venue">Venue or link</label>
          <input id="slot_venue" type="text" name="venue" maxlength="190" value="<?= e(old('venue')) ?>" placeholder="e.g. Room 2 or Zoom">
        </div>
        <div class="form-group">
          <label for="slot_lecturer">Lecturer</label>
          <select id="slot_lecturer" name="lecturer_id">
            <option value="">—</option>
            <?php foreach ($lecturers as $l): ?>
              <option value="<?= (int) $l['id'] ?>"<?= old('lecturer_id') === (string) $l['id'] ? ' selected' : '' ?>><?= e($l['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group slot-submit"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add</button></div>
      </form>
    <?php endif; ?>
  </div>
</div>
