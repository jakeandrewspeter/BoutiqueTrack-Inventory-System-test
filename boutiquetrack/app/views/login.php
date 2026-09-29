<?php $flashMessage = take_flash(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · BoutiqueTrack</title><link rel="stylesheet" href="assets/app.css">
</head>
<body class="auth-page">
<main class="auth-card login-card">
    <div class="brand auth-brand"><span class="brand-mark">B</span><span><strong>BoutiqueTrack</strong><small>SHOP MANAGEMENT</small></span></div>
    <div class="eyebrow">YOUR DAILY BUSINESS, IN ONE PLACE</div>
    <h1>Good to see you.</h1>
    <p class="auth-copy">Sign in to pick up where your boutique left off.</p>
    <?php if ($flashMessage): ?><div class="notice notice-<?= h($flashMessage['type']) ?>" role="alert"><span>!</span><div><?= h($flashMessage['message']) ?></div></div><?php endif; ?>
    <form method="post" action="index.php?page=login" class="form-stack">
        <?= csrf_field() ?><input type="hidden" name="action" value="login">
        <label>Username<input name="username" autocomplete="username" required autofocus></label>
        <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
        <button class="button button-primary button-wide" type="submit">Sign in <span>→</span></button>
    </form>
    <p class="auth-footnote">Access is managed by your shop administrator.</p>
</main>
</body></html>