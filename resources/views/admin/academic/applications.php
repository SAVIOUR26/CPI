<?php
$sidebar = 'partials.sidebar-admin';
/** @var array $applications */ /** @var string $status */ /** @var array $counts */ /** @var array $levels */
$labels = ['submitted' => 'New', 'under_review' => 'Under review', 'admitted' => 'Admitted', 'rejected' => 'Not admitted'];
$icons = ['submitted' => 'fa-inbox', 'under_review' => 'fa-magnifying-glass', 'admitted' => 'fa-circle-check', 'rejected' => 'fa-circle-xmark'];
?>
<div class="dash-header">
  <div>
    <p class="eyebrow">Academic system</p>
    <h1>Admissions</h1>
    <p>Online applications for Certificate, Diploma and Degree programmes.</p>
  </div>
  <div class="actions">
    <a href="/admin/academic/programmes" class="btn btn-outline btn-sm"><i class="fa-solid fa-building-columns"></i> Programmes</a>
    <a href="/academic" target="_blank" rel="noopener" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> Public page</a>
  </div>
</div>

<div class="filters" style="margin-bottom:20px">
  <a href="/admin/academic/applications" class="<?= $status === '' ? 'active' : '' ?>"><i class="fa-solid fa-layer-group"></i> All (<?= array_sum($counts) ?>)</a>
  <?php foreach ($labels as $key => $label): ?>
    <a href="/admin/academic/applications?status=<?= e($key) ?>" class="<?= $status === $key ? 'active' : '' ?>"><i class="fa-solid <?= $icons[$key] ?>"></i> <?= e($label) ?> (<?= (int) $counts[$key] ?>)</a>
  <?php endforeach; ?>
</div>

<div class="table-card">
  <table>
    <thead><tr><th>Applicant</th><th>Application no.</th><th>Programme</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($applications as $a): ?>
      <tr>
        <td>
          <div class="cell-person">
            <span class="avatar"><?= e(initials($a['applicant_name'])) ?></span>
            <div><strong><?= e($a['applicant_name']) ?></strong><span class="cell-sub"><?= e($a['email']) ?></span></div>
          </div>
        </td>
        <td><?= $a['application_no'] ? '<strong>' . e($a['application_no']) . '</strong>' : '<span class="muted">—</span>' ?></td>
        <td><?= e($a['programme_title']) ?><span class="cell-sub"><?= e($levels[$a['award_level']]['label'] ?? ucfirst($a['award_level'])) ?></span></td>
        <td><?= date_pretty($a['created_at'], 'j M Y') ?><span class="cell-sub"><?= date_pretty($a['created_at'], 'H:i') ?></span></td>
        <td><span class="status status-<?= e($a['status']) ?>"><?= e($labels[$a['status']] ?? $a['status']) ?></span></td>
        <td class="cell-actions"><a href="/admin/academic/applications/<?= (int) $a['id'] ?>" class="btn btn-sm <?= in_array($a['status'], ['submitted', 'under_review'], true) ? 'btn-primary' : 'btn-outline' ?>">
          <?= in_array($a['status'], ['submitted', 'under_review'], true) ? 'Review' : 'View' ?></a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$applications): ?>
      <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-file-signature"></i>
        <?= $status ? 'No applications with this status.' : 'No applications yet. They will appear here as soon as applicants submit the online form.' ?></div></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
