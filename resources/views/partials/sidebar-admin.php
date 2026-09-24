<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$on = fn (string $p): string => str_starts_with($path, $p) ? 'active' : '';
?>
<p class="dash-nav-label">Overview</p>
<a href="/admin" class="<?= $path === '/admin' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
<a href="/admin/reports" class="<?= $on('/admin/reports') ?>"><i class="fa-solid fa-chart-pie"></i> Reports</a>
<p class="dash-nav-label">Training</p>
<a href="/admin/courses" class="<?= $on('/admin/courses') ?>"><i class="fa-solid fa-book-open"></i> Courses &amp; Intakes</a>
<a href="/admin/certificates" class="<?= $on('/admin/certificates') ?>"><i class="fa-solid fa-award"></i> Certificates</a>
<p class="dash-nav-label">Clients</p>
<a href="/admin/corporate-requests" class="<?= $on('/admin/corporate-requests') ?>"><i class="fa-solid fa-handshake"></i> Corporate Requests</a>
<a href="/admin/organizations" class="<?= $on('/admin/organizations') ?>"><i class="fa-solid fa-building"></i> Organizations</a>
<a href="/admin/payments" class="<?= $on('/admin/payments') ?>"><i class="fa-solid fa-money-bill-wave"></i> Payments</a>
<p class="dash-nav-label">Academic</p>
<a href="/admin/academic/applications" class="<?= $on('/admin/academic/applications') ?>"><i class="fa-solid fa-file-signature"></i> Admissions</a>
<a href="/admin/academic/programmes" class="<?= $on('/admin/academic/programmes') ?>"><i class="fa-solid fa-building-columns"></i> Programmes</a>
<a href="/admin/announcements" class="<?= $on('/admin/announcements') ?>"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
<a href="/admin/calendar" class="<?= $on('/admin/calendar') ?>"><i class="fa-solid fa-calendar-days"></i> Calendar &amp; Timetable</a>
<p class="dash-nav-label">System</p>
<a href="/admin/users" class="<?= $on('/admin/users') ?>"><i class="fa-solid fa-users-gear"></i> Users &amp; Roles</a>
<a href="/admin/account" class="<?= $on('/admin/account') ?>"><i class="fa-solid fa-user-gear"></i> My account</a>
