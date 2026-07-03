<?php

declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/register.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if ($name === '' || $username === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Semua field wajib diisi.'];
    header('Location: ../pages/register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Format email tidak valid.'];
    header('Location: ../pages/register.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Password minimal 6 karakter.'];
    header('Location: ../pages/register.php');
    exit;
}

if ($password !== $confirmPassword) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Konfirmasi password tidak cocok.'];
    header('Location: ../pages/register.php');
    exit;
}

$connection = Database::getInstance()->getConnection();
$checkStatement = $connection->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$checkStatement->execute([
    ':username' => $username,
    ':email' => $email,
]);

if ($checkStatement->fetch() !== false) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Username atau email sudah terdaftar.'];
    header('Location: ../pages/register.php');
    exit;
}

$insertStatement = $connection->prepare('INSERT INTO users (name, username, email, role, password) VALUES (:name, :username, :email, :role, :password)');
$insertStatement->execute([
    ':name' => $name,
    ':username' => $username,
    ':email' => $email,
    ':role' => 'user',
    ':password' => password_hash($password, PASSWORD_DEFAULT),
]);

$_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registrasi berhasil. Silakan login.'];
header('Location: ../pages/login.php');
exit;
