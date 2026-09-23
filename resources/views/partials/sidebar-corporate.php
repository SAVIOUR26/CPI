<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$on = fn (string $p): string => str_starts_with($path, $p) ? 'active' : '';
?>
<p class="dash-nav-label">Organization</p>
<a href="/corporate/portal" class="<?= $path === '/corporate/portal' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="/corporate/portal/cohorts" class="<?= $on('/corporate/portal/cohorts') ?>"><i class="fa-solid fa-people-group"></i> Our Cohorts</a>
<a href="/corporate/portal/invoices" class="<?= $on('/corporate/portal/invoices') ?>"><i class="fa-solid fa-file-invoice-dollar"></i> Invoices</a>
<a href="/corporate/portal/account" class="<?= $on('/corporate/portal/account') ?>"><i class="fa-solid fa-user-gear"></i> My account</a>
<p class="dash-nav-label">Training</p>
<a href="/corporate/request"><i class="fa-solid fa-plus"></i> New Training Request <i class="fa-solid fa-arrow-up-right-from-square ext"></i></a>
