<?php
/** @var array $accounts [name, email, password, portal, emailed] — see App\Support\NewAccounts */
use App\Support\NewAccounts;

$count = count($accounts);
$unsent = count(array_filter($accounts, fn ($a) => !$a['emailed']));
$copy = implode("\n", array_map(
    fn ($a) => $a['name'] . ' — email: ' . $a['email'] . ' — temporary password: ' . $a['password']
        . ' — sign in at ' . NewAccounts::loginUrl($a['portal']),
    $accounts
));
?>
<div class="panel new-accounts" role="status">
  <div class="panel-head">
    <h2><i class="fa-solid fa-key"></i> <?= $count === 1 ? 'Login details for the new account' : 'Login details for ' . $count . ' new accounts' ?></h2>
    <button type="button" class="btn btn-outline btn-sm" data-copy="<?= e($copy) ?>"><i class="fa-regular fa-copy"></i> Copy <?= $count === 1 ? 'details' : 'all' ?></button>
  </div>
  <div class="panel-body">
    <p class="new-accounts-note"><i class="fa-solid fa-eye-slash"></i>
      <span>These temporary passwords are shown <strong>only once</strong>. Copy them now and share them privately, in person or by phone.
      <?php if ($unsent === $count): ?><strong>The welcome email could not be sent<?= $count > 1 ? ' to anyone' : '' ?></strong>, so these details are the only way in.
      <?php elseif ($unsent): ?><strong><?= $unsent ?> welcome email<?= $unsent === 1 ? '' : 's' ?> could not be sent</strong> — share those details yourself.
      <?php else: ?>They were also emailed.<?php endif; ?>
      Ask each person to change their password under <strong>My account</strong> after signing in.</span></p>
  </div>
  <div class="table-card">
    <table>
      <thead><tr><th>Name</th><th>Email (sign-in)</th><th>Temporary password</th><th>Signs in at</th><th>Welcome email</th></tr></thead>
      <tbody>
      <?php foreach ($accounts as $a): ?>
        <tr>
          <td><strong><?= e($a['name']) ?></strong></td>
          <td><?= e($a['email']) ?></td>
          <td><code class="temp-pass"><?= e($a['password']) ?></code></td>
          <td><a href="<?= e(NewAccounts::loginUrl($a['portal'])) ?>" target="_blank" rel="noopener"><?= e(NewAccounts::PORTALS[$a['portal']][0] ?? 'Login') ?></a></td>
          <td><?= $a['emailed'] ? '<span class="status status-sent">Sent</span>' : '<span class="status status-failed">Not sent</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
