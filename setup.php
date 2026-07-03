<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Script setup untuk membuat ULANG (reset) database, tabel, dan data awal
 * setiap kali file ini dijalankan lewat browser.
 *
 * PERINGATAN: seluruh data lama (users, expenses, dll) akan DIHAPUS TOTAL
 * setiap kali script ini dieksekusi. Jangan taruh file ini di production
 * tanpa proteksi tambahan (mis. cek environment, hapus setelah dipakai,
 * atau lindungi dengan auth).
 */

function createServerConnection(): PDO
{
    $dsn = sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET);

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function createAppConnection(): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function importSqlFile(PDO $connection, string $sqlFilePath): void
{
    $sqlContent = file_get_contents($sqlFilePath);

    if ($sqlContent === false) {
        throw new RuntimeException('File database.sql tidak dapat dibaca.');
    }

    // Ganti semua kemunculan nama database di file SQL dengan DB_NAME dari
    // config, apa pun bentuk statement-nya (DROP/CREATE/USE, dengan atau
    // tanpa IF NOT EXISTS/IF EXISTS). Pendekatan literal str_replace yang
    // lama gampang berhenti bekerja begitu isi database.sql diubah.
    $sqlContent = str_ireplace('smartexpense', DB_NAME, $sqlContent);

    $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent) ?? $sqlContent;
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

    foreach ($statements as $statement) {
        $connection->exec($statement);
    }
}

function seedDefaultAdmin(PDO $connection): void
{
    $statement = $connection->query('SELECT COUNT(*) FROM users');
    $userCount = (int) $statement->fetchColumn();

    $adminStatement = $connection->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $adminStatement->execute([
        ':username' => 'admin',
        ':email' => 'admin@smartexpense.local',
    ]);

    $adminExists = $adminStatement->fetchColumn();

    if ($adminExists === false) {
        $insertStatement = $connection->prepare('INSERT INTO users (name, username, email, role, password) VALUES (:name, :username, :email, :role, :password)');
        $insertStatement->execute([
            ':name' => 'Administrator',
            ':username' => 'admin',
            ':email' => 'admin@smartexpense.local',
            ':role' => 'admin',
            ':password' => password_hash('admin123', PASSWORD_DEFAULT),
        ]);

        return;
    }

    $updateStatement = $connection->prepare('UPDATE users SET role = :role WHERE username = :username OR email = :email');
    $updateStatement->execute([
        ':role' => 'admin',
        ':username' => 'admin',
        ':email' => 'admin@smartexpense.local',
    ]);
}

function ensureUsersRoleColumn(PDO $connection): void
{
    $statement = $connection->prepare("SHOW COLUMNS FROM users LIKE 'role'");
    $statement->execute();

    if ($statement->fetchColumn() === false) {
        $connection->exec("ALTER TABLE users ADD role ENUM('admin', 'user') NOT NULL DEFAULT 'user' AFTER email");
    }
}

try {
    // Import lewat koneksi SERVER (tanpa dbname terpasang), bukan koneksi
    // yang sudah nempel ke database "smartexpense". database.sql berisi
    // DROP DATABASE + CREATE DATABASE + USE sendiri, jadi kalau dieksekusi
    // lewat koneksi yang current database-nya adalah database yang sedang
    // di-DROP, ini bisa bikin statement berikutnya gagal/tidak konsisten
    // tergantung driver.
    $serverConnection = createServerConnection();
    importSqlFile($serverConnection, __DIR__ . '/database.sql');

    // Buka koneksi baru yang sudah terarah ke database (yang baru saja
    // dibuat ulang) untuk langkah-langkah selanjutnya.
    $appConnection = createAppConnection();
    ensureUsersRoleColumn($appConnection);
    seedDefaultAdmin($appConnection);

    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Setup Selesai</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><div class="container py-5"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h4 mb-3">Setup berhasil</h1><p class="mb-3">Database, tabel, dan data awal sudah berhasil di-import.</p><p class="mb-3"><strong>Akun default admin:</strong> admin / admin123</p><a class="btn btn-primary" href="pages/login.php">Masuk ke Login</a></div></div></div></body></html>';
} catch (Throwable $throwable) {
    http_response_code(500);
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Setup Gagal</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><div class="container py-5"><div class="card border-danger shadow-sm"><div class="card-body p-4"><h1 class="h4 text-danger mb-3">Setup gagal</h1><p class="mb-3">' . htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8') . '</p></div></div></div></body></html>';
}