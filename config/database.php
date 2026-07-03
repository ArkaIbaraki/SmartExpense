<?php

declare(strict_types=1);

/**
 * Konfigurasi koneksi database untuk Smart Daily Expense Planner.
 * Gunakan file ini di semua class yang membutuhkan akses MySQL.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'smartexpense';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Membuat koneksi PDO ke database.
 *
 * @return PDO
 */
function getDatabaseConnection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $exception) {
        die('Koneksi database gagal: ' . $exception->getMessage());
    }

    return $connection;
}
