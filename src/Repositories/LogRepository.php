<?php
namespace App\Repositories;

use App\Core\Database;

class LogRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function log(string $jenis, string $detail): void
    {
        // Pastikan ID Admin 1 ada di database
        $sql = "INSERT INTO log_aktivitas (id_admin, jenis_aktivitas, detail_aktivitas, waktu_aktivitas) VALUES (1, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$jenis, $detail]);
    }
}