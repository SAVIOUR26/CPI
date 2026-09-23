<?php
$sidebar = 'partials.sidebar-admin';
/** @var array $programmes */ /** @var array $groups */ /** @var array $levels */ /** @var array $applicationCounts */
$tones = ['certificate' => 'tone-gold', 'diploma' => 'tone-blue', 'degree' => 'tone-crimson', 'postgraduate' => 'tone-purple'];
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Academic system</p>
    <h1>Academic programmes</h1>
    <p>Programmes offered through CPI's university partnerships. Published programmes appear on the public
      <a href="/academic" target="_blank" rel="noopener">Academic Programmes</a> page.</p>
  </div>
  <div class="actions">
    <a href="/academic" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> View public page</a>
    <a href="/admin/academic/programmes/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New programme</a>
  </div>
</div>

<div class="stat-row">
  <?php foreach ($levels as $key => $lvl): ?>
    <?php if ($key === 'postgraduate' && empty($groups[$key])) continue; ?>
    <a href="#level-<?= e($key) ?>" class="stat-box"><span class="stat-icon <?= $tones[$key] ?>"><i class="fa-solid <?= e($lvl['icon']) ?>"></i></span>
      <div><div class="num"><?= count($groups[$key] ?? []) ?></div><div class="label"><?= e($lvl['plural']) ?></div></div></a>
  <?php endforeach; ?>
  <a href="/admin/academic/applications" class="stat-box"><span class="stat-icon tone-green"><i class="fa-solid fa-file-signature"></i></span>
    <div><div class="num"><?= array_sum($applicationCounts) ?></div><div class="label">Applications received</div></div></a>
</div>

<?php foreach ($levels as $key => $lvl): ?>
  <?php if (empty($groups[$key])) continue; ?>
  <div class="panel" id="level-<?= e($key) ?>">
    <div class="panel-head">
      <h2><i class="fa-solid <?= e($lvl['icon']) ?>"></i> <?= e($lvl['plural']) ?></h2>
      <span class="muted" style="font-size:.84rem"><?= count($groups[$key]) ?> programme<?= count($groups[$key]) === 1 ? '' : 's' ?></span>
    </div>
    <div class="table-card">
      <table>
        <thead><tr><th>Programme</th><th>Awarding body</th><th>Duration</th><th>Status</th><th>Applications</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($groups[$key] as $p): ?>
          <?php $duration = $p['duration_note'] ?: $p['course_duration']; ?>
          <tr>
            <td><strong><?= e($p['title']) ?></strong><span class="cell-sub"><?= e($p['category_name'] ?? 'Uncategorised') ?></span></td>
            <td><?= $p['awarding_body'] ? e($p['awarding_body']) : '<span class="muted">Not set</span>' ?></td>
            <td><?= $duration ? e($duration) : '<span class="muted">Not set</span>' ?></td>
            <td><span class="status status-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
            <td><?= (int) ($applicationCounts[$p['id']] ?? 0) ?></td>
            <td class="cell-actions">
              <?php if ($p['status'] === 'published'): ?>
                <a href="/academic/programmes/<?= (int) $p['id'] ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline" title="View public page" aria-label="View public page for <?= e($p['title']) ?>"><i class="fa-solid fa-eye"></i></a>
              <?php endif; ?>
              <a href="/admin/academic/programmes/<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline"><i class="fa-solid fa-pen"></i> Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!$programmes): ?>
  <div class="card empty-state"><i class="fa-solid fa-building-columns"></i><h3>No academic programmes yet</h3>
    <p>Add your first Certificate, Diploma or Degree programme.</p>
    <a href="/admin/academic/programmes/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New programme</a></div>
<?php endif; ?>
