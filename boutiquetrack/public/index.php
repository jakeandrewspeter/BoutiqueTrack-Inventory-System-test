<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/auth.php';
require_once dirname(__DIR__) . '/app/actions.php';
require_once dirname(__DIR__) . '/app/page_data.php';

try {
    $pdo = db();
} catch (Throwable $error) {
    http_response_code(503);
    $databaseError = $error->getMessage();
    require dirname(__DIR__) . '/app/views/database-error.php';
    exit;
}

$page = preg_replace('/[^a-z-]/', '', (string)($_GET['page'] ?? 'dashboard')) ?: 'dashboard';
$user = table_exists($pdo, 'users') ? current_user($pdo) : null;
$setupNeeded = !table_exists($pdo, 'users') || (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;

if ($setupNeeded && $page !== 'setup') {
    redirect(app_path('setup'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        process_action($pdo, $user, (string)($_POST['action'] ?? ''));
    } catch (Throwable $error) {
        if ($error instanceof InvalidArgumentException || $error instanceof RuntimeException) {
            flash('error', $error->getMessage());
        } else {
            error_log('BoutiqueTrack request failed: ' . $error->getMessage());
            flash('error', 'That could not be completed. Check the information and try again.');
        }
        $fallback = $page === 'setup' ? 'setup' : ($user ? $page : 'login');
        redirect(app_path($fallback));
    }
}

if ($page === 'setup') {
    if (!$setupNeeded) {
        redirect(app_path('login'));
    }
    require dirname(__DIR__) . '/app/views/setup.php';
    exit;
}

if ($setupNeeded) {
    redirect(app_path('setup'));
}

if ($page === 'login') {
    if ($user) {
        redirect(app_path('dashboard'));
    }
    require dirname(__DIR__) . '/app/views/login.php';
    exit;
}

if (!$user) {
    redirect(app_path('login'));
}

if ($page === 'export') {
    require_role($user, ['admin']);
    export_report($pdo, (string)($_GET['period'] ?? 'month'));
}

$pageRoles = [
    'products' => ['admin'],
    'product-form' => ['admin'],
    'inventory' => ['admin'],
    'reports' => ['admin'],
    'users' => ['admin'],
];
if (isset($pageRoles[$page])) {
    require_role($user, $pageRoles[$page]);
}

try {
    $data = page_data($pdo, $page, $user);
} catch (Throwable $error) {
    flash('error', $error instanceof InvalidArgumentException
        ? $error->getMessage()
        : 'This page could not be loaded. Please try again.');
    if (!$error instanceof InvalidArgumentException) {
        error_log('BoutiqueTrack page failed: ' . $error->getMessage());
    }
    redirect(app_path('dashboard'));
}

extract($data, EXTR_SKIP);

$pageTitle = match ($page) {
    'products' => 'Products',
    'product-form' => empty($data['product']) ? 'Add product' : 'Edit product',
    'inventory' => 'Inventory',
    'pos' => 'Point of sale',
    'sales' => 'Sales',
    'receipt' => 'Receipt',
    'reports' => 'Reports',
    'users' => 'Team',
    default => 'Dashboard',
};
$activePage = $page === 'product-form' ? 'products' : $page;
$contentView = dirname(__DIR__) . '/app/views/' . $page . '.php';
if (!is_file($contentView)) {
    $contentView = dirname(__DIR__) . '/app/views/dashboard.php';
}
require dirname(__DIR__) . '/app/views/layout.php';