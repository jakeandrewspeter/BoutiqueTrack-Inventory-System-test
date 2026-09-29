<?php
$flashMessage = take_flash();
$navItems = [
    ['dashboard', 'Overview', '⌂', ['admin', 'cashier']],
    ['pos', 'New sale', '＋', ['admin', 'cashier']],
    ['sales', 'Sales history', '↗', ['admin', 'cashier']],
    ['products', 'Products', '▦', ['admin']],
    ['inventory', 'Inventory', '◫', ['admin']],
    ['reports', 'Reports', '▤', ['admin']],
    ['users', 'Team access', '♙', ['admin']],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f3ee">
    <title><?= h($pageTitle) ?> · <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/app.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="app-shell">
<aside class="sidebar">
    <a class="brand" href="<?= h(app_path('dashboard')) ?>">
        <span class="brand-mark">B</span>
        <span><strong>BoutiqueTrack</strong><small>SHOP MANAGEMENT</small></span>
    </a>
    <div class="side-label">WORKSPACE</div>
    <nav class="side-nav" aria-label="Main navigation">
        <?php foreach ($navItems as [$slug, $label, $icon, $roles]): ?>
            <?php if (in_array($user['role'], $roles, true)): ?>
                <a class="<?= $activePage === $slug ? 'active' : '' ?>" href="<?= h(app_path($slug)) ?>">
                    <span class="nav-icon" aria-hidden="true"><?= h($icon) ?></span><?= h($label) ?>
                    <?php if ($slug === 'pos' && !empty($_SESSION['cart'])): ?>
                        <span class="nav-count"><?= array_sum($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="store-chip">
            <span class="store-avatar">J</span>
            <span><strong>My Boutique</strong><small>Juba · South Sudan</small></span>
        </div>
        <div class="user-row">
            <span class="user-avatar"><?= h(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
            <span class="user-identity"><strong><?= h($user['full_name']) ?></strong><small><?= h(auth_label($user['role'])) ?></small></span>
            <form method="post" action="<?= h(app_path('dashboard')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="logout">
                <button class="icon-button" type="submit" title="Sign out" aria-label="Sign out">↗</button>
            </form>
        </div>
    </div>
</aside>
<main class="main-panel">
    <header class="topbar">
        <div class="breadcrumb"><span>Workspace</span><b>/</b><strong><?= h($pageTitle) ?></strong></div>
        <div class="topbar-right"><span class="status-dot"></span><span>Local shop</span><span class="topbar-date"><?= date('D, j M Y') ?></span></div>
    </header>
    <div class="page-content">
        <?php if ($flashMessage): ?>
            <div class="notice notice-<?= h($flashMessage['type']) ?>" role="status">
                <span><?= $flashMessage['type'] === 'success' ? '✓' : '!' ?></span>
                <div><?= h($flashMessage['message']) ?></div>
                <button type="button" class="notice-close" aria-label="Dismiss" data-dismiss>×</button>
            </div>
        <?php endif; ?>
        <?php require $contentView; ?>
        <footer class="app-footer"><span>BoutiqueTrack</span><span>Simple records. Better decisions.</span></footer>
    </div>
</main>
</body>
</html>