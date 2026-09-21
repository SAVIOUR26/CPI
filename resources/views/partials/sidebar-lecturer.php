<?php $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<a href="/lecturer" class="<?= $path === '/lecturer' ? 'active' : '' ?>">My Classes</a>
