<?php
declare(strict_types=1);

function current_user(PDO $pdo): ?array
{
    $id = (int)($_SESSION['user_id'] ?? 0);
    if ($id < 1) {
        return null;
    }

    $statement = $pdo->prepare('SELECT id, full_name, username, role, is_active FROM users WHERE id = ?');
    $statement->execute([$id]);
    $user = $statement->fetch();
    if (!$user || !(bool)$user['is_active']) {
        unset($_SESSION['user_id'], $_SESSION['cart']);
        return null;
    }
    return $user;
}

function require_role(?array $user, array $roles): void
{
    if (!$user || !in_array($user['role'], $roles, true)) {
        flash('error', 'You do not have permission to open that page.');
        redirect(app_path('dashboard'));
    }
}

function input_text(string $key, int $maxLength = 255): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if (strlen($value) > $maxLength) {
        throw new InvalidArgumentException(ucfirst(str_replace('_', ' ', $key)) . ' is too long.');
    }
    return $value;
}

function input_id(string $key): int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value < 1) {
        throw new InvalidArgumentException('Please choose a valid record.');
    }
    return (int)$value;
}

function input_money(string $key, bool $allowZero = true): float
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_FLOAT);
    if ($value === false || !is_finite((float)$value) || $value < 0 || (!$allowZero && $value == 0.0)) {
        throw new InvalidArgumentException('Enter a valid amount.');
    }
    return round((float)$value, 2);
}

function input_quantity(string $key, bool $allowZero = true): int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value < 0 || (!$allowZero && $value === 0)) {
        throw new InvalidArgumentException('Enter a valid whole-number quantity.');
    }
    return (int)$value;
}