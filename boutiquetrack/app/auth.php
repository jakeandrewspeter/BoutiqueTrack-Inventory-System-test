<?php
declare(strict_types=1);

function auth_label(string $role): string
{
    return $role === 'admin' ? 'Administrator' : 'Cashier';
}