<?php $flashMessage = take_flash(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up your boutique · BoutiqueTrack</title><link rel="stylesheet" href="assets/app.css">
</head>
<body class="auth-page">
<main class="auth-card setup-card">
    <div class="brand auth-brand"><span class="brand-mark">B</span><span><strong>BoutiqueTrack</strong><small>SHOP MANAGEMENT</small></span></div>
    <div class="eyebrow">WELCOME TO YOUR WORKSPACE</div>
    <h1>Set up your boutique</h1>
    <p class="auth-copy">Create the first administrator account. You’ll use it to manage products, stock, sales and staff access.</p>
    <?php if ($flashMessage): ?><div class="notice notice-error" role="alert"><span>!</span><div><?= h($flashMessage['message']) ?></div></div><?php endif; ?>
    <form method="post" action="index.php?page=setup" class="form-stack">
        <?= csrf_field() ?><input type="hidden" name="action" value="setup-admin">
        <label>Full name<input name="full_name" autocomplete="name" required maxlength="120" placeholder="Your name"></label>
        <label>Username<input name="username" autocomplete="username" required minlength="3" maxlength="60" pattern="[A-Za-z0-9._-]+" placeholder="e.g. owner"></label>
        <div class="form-grid two">
            <label>Password<input name="password" type="password" autocomplete="new-password" required minlength="10" placeholder="At least 10 characters"></label>
            <label>Confirm password<input name="confirm_password" type="password" autocomplete="new-password" required minlength="10" placeholder="Enter it again"></label>
        </div>
        <button class="button button-primary button-wide" type="submit">Create administrator account <span>→</span></button>
    </form>
    <div class="setup-note"><span class="lock-icon">⌑</span><span>This account is stored in your boutique database. There is no default password.</span></div>
</main>
</body></html>