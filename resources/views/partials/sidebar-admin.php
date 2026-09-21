<?php $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<a href="/admin" class="<?= $path === '/admin' ? 'active' : '' ?>">Dashboard</a>
<a href="/admin/courses" class="<?= str_starts_with($path, '/admin/courses') ? 'active' : '' ?>">Courses &amp; Intakes</a>
<a href="/admin/corporate-requests" class="<?= str_starts_with($path, '/admin/corporate-requests') ? 'active' : '' ?>">Corporate Requests</a>
<a href="/admin/organizations" class="<?= str_starts_with($path, '/admin/organizations') ? 'active' : '' ?>">Organizations</a>
<a href="/admin/payments" class="<?= str_starts_with($path, '/admin/payments') ? 'active' : '' ?>">Payments</a>
<a href="/admin/certificates" class="<?= str_starts_with($path, '/admin/certificates') ? 'active' : '' ?>">Certificates</a>
<a href="/admin/academic" class="<?= str_starts_with($path, '/admin/academic') ? 'active' : '' ?>">Academic Admissions</a>
<a href="/admin/users" class="<?= str_starts_with($path, '/admin/users') ? 'active' : '' ?>">Users &amp; Roles</a>
<a href="/admin/reports" class="<?= str_starts_with($path, '/admin/reports') ? 'active' : '' ?>">Reports</a>
