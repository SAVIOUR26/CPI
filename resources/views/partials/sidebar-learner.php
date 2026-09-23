<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$on = fn (string $p): string => str_starts_with($path, $p) ? 'active' : '';
?>
<p class="dash-nav-label">Learning</p>
<a href="/learner" class="<?= $path === '/learner' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
<a href="/learner/courses" class="<?= $on('/learner/courses') ?: $on('/learner/quizzes') ?>"><i class="fa-solid fa-book-open-reader"></i> My Courses</a>
<a href="/learner/certificates" class="<?= $on('/learner/certificates') ?>"><i class="fa-solid fa-award"></i> Certificates</a>
<p class="dash-nav-label">Account</p>
<a href="/learner/fees" class="<?= $on('/learner/fees') ?: $on('/learner/pay') ?>"><i class="fa-solid fa-receipt"></i> Fees &amp; Payments</a>
<a href="/learner/profile" class="<?= $on('/learner/profile') ?>"><i class="fa-solid fa-user-gear"></i> My account</a>
<p class="dash-nav-label">Explore</p>
<a href="/courses"><i class="fa-solid fa-magnifying-glass"></i> Browse Catalogue <i class="fa-solid fa-arrow-up-right-from-square ext"></i></a>
