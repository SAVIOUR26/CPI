<?php $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<a href="/corporate/portal" class="<?= $path === '/corporate/portal' ? 'active' : '' ?>">Dashboard</a>
<a href="/corporate/portal/cohorts" class="<?= str_starts_with($path, '/corporate/portal/cohorts') ? 'active' : '' ?>">Our Cohorts</a>
<a href="/corporate/portal/invoices" class="<?= str_starts_with($path, '/corporate/portal/invoices') ? 'active' : '' ?>">Invoices</a>
<a href="/corporate/request">New Training Request</a>
