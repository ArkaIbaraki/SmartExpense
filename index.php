<?php

require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    // Cek apakah tabel users ada
    $pdo->query("SELECT 1 FROM users LIMIT 1");

    // Jika berhasil, langsung ke login
    header('Location: pages/login.php');
    exit;

} catch (Throwable $e) {
    // Jika database/tabel belum ada, jalankan setup
    header('Location: setup.php');
    exit;
}