<?php
/** @var array $announcements */ /** @var array $intakes */
use App\Models\Announcement;

$err = function (string $key): string {
    $out = '';
    foreach (field_errors($key) as $msg) {
        $out .= '<div class="field-error">' . e($msg) . '</div>';
    }
    return $out;
};
$target = old('target', 'everyone');
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Academic</p>
    <h1>Announcements</h1>
    <p>News for the Student and Lecturer portals. Institute-wide notices appear on everyone's dashboard; a class notice only reaches that class.
      Lecturers can also post to their own classes.</p>
  </div>
</div>

<div class="panel" id="post">
  <div class="panel-head"><h2><i class="fa-solid fa-bullhorn"></i> Post an announcement</h2></div>
  <div class="panel-body">
    <form method="post" action="/admin/announcements" class="announce-form">
      <?= csrf_field() ?>
      <div class="form-row">
        <div class="form-group">
          <label for="ann_title">Title</label>
          <input id="ann_title" type="text" name="title" maxlength="190" value="<?= e(old('title')) ?>" required placeholder="e.g. Examination timetable released">
          <?= $err('title') ?>
        </div>
        <div class="form-group">
          <label for="ann_target">Who should see it</label>
          <select id="ann_target" name="target" required>
            <optgroup label="Whole institute">
              <?php foreach (Announcement::AUDIENCES as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $target === $key ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </optgroup>
            <?php if ($intakes): ?>
              <optgroup label="One class">
                <?php foreach ($intakes as $i): ?>
                  <?php $value = 'intake:' . (int) $i['id']; ?>
                  <option value="<?= e($value) ?>"<?= $target === $value ? ' selected' : '' ?>><?= e($i['course_title'] . ' (' . $i['code'] . ')') ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endif; ?>
          </select>
          <?= $err('target') ?>
        </div>
      </div>
      <div class="form-group">
        <label for="ann_body">Message</label>
        <textarea id="ann_body" name="body" rows="4" maxlength="5000" required><?= e(old('body')) ?></textarea>
        <?= $err('body') ?>
      </div>
      <div class="announce-form-foot">
        <label class="check"><input type="checkbox" name="pinned" value="1"<?= old('pinned') ? ' checked' : '' ?>> Pin to the top</label>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publish</button>
      </div>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2><i class="fa-solid fa-list"></i> Published</h2><span class="muted" style="font-size:.82rem"><?= count($announcements) ?> most recent</span></div>
  <div class="panel-body">
    <?php if ($announcements): ?>
      <?php \App\Core\View::partial('partials.portal.announcements', ['announcements' => $announcements, 'showAudience' => true,
        'deleteUrl' => fn ($a) => '/admin/announcements/' . (int) $a['id'] . '/delete']); ?>
    <?php else: ?>
      <div class="empty-state"><i class="fa-solid fa-bullhorn"></i>Nothing published yet.</div>
    <?php endif; ?>
  </div>
</div>
