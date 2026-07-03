<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Menyediakan koneksi PDO tunggal untuk seluruh aplikasi.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $this->connection = getDatabaseConnection();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
