<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection - Singleton Pattern
 * Memastikan hanya ada satu koneksi database
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    // Enkapsulasi: Constructor private
    private function __construct()
    {
        // Muat konfigurasi dari config/database.php
        $config = require __DIR__ . '/../../config/database.php';

        // UPDATE PENTING: Tambahkan 'port' ke dalam DSN
        // Railway (dan cloud provider lain) seringkali butuh port spesifik
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            // Error handling yang lebih bersih
            throw new \RuntimeException("DB connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Mencegah cloning dan unserialize (Pattern Singleton)
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize"); }
}