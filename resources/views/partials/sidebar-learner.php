<?php $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<a href="/learner" class="<?= $path === '/learner' ? 'active' : '' ?>">Dashboard</a>
<a href="/learner/courses" class="<?= str_starts_with($path, '/learner/courses') ? 'active' : '' ?>">My Courses</a>
<a href="/learner/certificates" class="<?= str_starts_with($path, '/learner/certificates') ? 'active' : '' ?>">Certificates</a>
<a href="/learner/fees" class="<?= str_starts_with($path, '/learner/fees') ? 'active' : '' ?>">Fees</a>
<a href="/learner/profile" class="<?= str_starts_with($path, '/learner/profile') ? 'active' : '' ?>">Profile</a>
<a href="/courses">Browse Catalogue</a>
