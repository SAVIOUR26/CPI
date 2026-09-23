<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<p class="dash-nav-label">Teaching</p>
<a href="/lecturer" class="<?= ($path === '/lecturer' || str_starts_with($path, '/lecturer/')) ? 'active' : '' ?>"><i class="fa-solid fa-chalkboard-user"></i> My Classes</a>
<p class="dash-nav-label">Explore</p>
<a href="/courses"><i class="fa-solid fa-magnifying-glass"></i> Course Catalogue <i class="fa-solid fa-arrow-up-right-from-square ext"></i></a>
