<?php

declare(strict_types=1);

session_start();

function auth_is_logged_in(): bool
{
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']);
}

function auth_current_user(): ?array
{
    return auth_is_logged_in() ? $_SESSION['auth_user'] : null;
}

function auth_require_login(): void
{
    if (!auth_is_logged_in()) {
        header('Location: /pages/login.php');
        exit;
    }
}

function auth_require_admin(): void
{
    auth_require_login();

    $currentUser = auth_current_user();
    if (($currentUser['role'] ?? 'user') !== 'admin') {
        header('Location: /pages/dashboard.php');
        exit;
    }
}

function auth_require_guest(): void
{
    if (auth_is_logged_in()) {
        $currentUser = auth_current_user();
        $redirectPath = (($currentUser['role'] ?? 'user') === 'admin')
            ? '/pages/admin/dashboard.php'
            : '/pages/dashboard.php';

        header('Location: ' . $redirectPath);
        exit;
    }
}

function auth_login(array $user): void
{
    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'name' => (string) $user['name'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'role' => (string) ($user['role'] ?? 'user'),
    ];
}

function auth_logout(): void
{
    unset($_SESSION['auth_user']);
}
