<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Database setup · BoutiqueTrack</title><link rel="stylesheet" href="assets/app.css"></head>
<body class="auth-page">
<main class="auth-card setup-card">
    <div class="brand auth-brand"><span class="brand-mark">B</span><span><strong>BoutiqueTrack</strong><small>SHOP MANAGEMENT</small></span></div>
    <div class="eyebrow">ONE-TIME SETUP</div>
    <h1>Connect your shop database</h1>
    <p class="auth-copy">BoutiqueTrack could not connect to the MySQL database. Create a database named <strong>boutique_track</strong> in phpMyAdmin, then confirm the connection details in <code>config/database.php</code>.</p>
    <div class="setup-steps">
        <div><span>1</span><p>Start Apache and MySQL from the XAMPP Control Panel.</p></div>
        <div><span>2</span><p>Open phpMyAdmin and create an empty database named <code>boutique_track</code>.</p></div>
        <div><span>3</span><p>Check the host, username, and password in <code>config/database.php</code>, then refresh this page.</p></div>
    </div>
    <details class="error-detail"><summary>Connection detail</summary><code><?= h($databaseError) ?></code></details>
</main>
</body></html>