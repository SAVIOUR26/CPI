<?php
/**
 * @var array $announcements
 * @var bool|null $showAudience  show who each one is for
 * @var callable|null $deleteUrl fn(array $a): ?string — adds a delete button where it returns a URL
 */
$showAudience = $showAudience ?? false;
$deleteUrl = $deleteUrl ?? null;
?>
<ul class="announce-list">
  <?php foreach ($announcements as $a): ?>
    <li class="announce<?= $a['pinned'] ? ' is-pinned' : '' ?>">
      <div class="announce-head">
        <h3><?php if ($a['pinned']): ?><i class="fa-solid fa-thumbtack" title="Pinned"></i> <?php endif; ?><?= e($a['title']) ?></h3>
        <?php if ($deleteUrl && ($url = $deleteUrl($a))): ?>
          <form method="post" action="<?= e($url) ?>" data-confirm-submit="Delete this announcement?">
            <?= csrf_field() ?>
            <button type="submit" class="btn-icon" title="Delete" aria-label="Delete announcement"><i class="fa-regular fa-trash-can"></i></button>
          </form>
        <?php endif; ?>
      </div>
      <p><?= nl2br(e($a['body'])) ?></p>
      <small>
        <?php if ($showAudience): ?><span class="announce-for"><i class="fa-solid fa-bullhorn"></i> <?= e(\App\Models\Announcement::audienceLabel($a)) ?></span> · <?php endif; ?>
        <?= e($a['author_name'] ?? 'CPI') ?> · <?= date_pretty($a['created_at'], 'j M Y, H:i') ?>
      </small>
    </li>
  <?php endforeach; ?>
</ul>
