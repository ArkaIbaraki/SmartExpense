<?php

declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$identifier = trim((string) ($_POST['identifier'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($identifier === '' || $password === '') {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Username/email dan password wajib diisi.'];
    header('Location: ../pages/login.php');
    exit;
}

$connection = Database::getInstance()->getConnection();
$statement = $connection->prepare('SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1');
$statement->execute([
    ':username' => $identifier,
    ':email' => $identifier,
]);
$user = $statement->fetch();

if ($user === false || !password_verify($password, (string) $user['password'])) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Login gagal. Periksa kembali username/email dan password.'];
    header('Location: ../pages/login.php');
    exit;
}

auth_login($user);
$_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Login berhasil.'];
$redirectPath = ((string) ($user['role'] ?? 'user') === 'admin')
    ? '../pages/admin/dashboard.php'
    : '../pages/dashboard.php';

header('Location: ' . $redirectPath);
exit;
