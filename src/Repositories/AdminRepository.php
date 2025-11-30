<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Admin;
use PDO;

class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?Admin
    {
        $stmt = $this->db->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? new Admin($row) : null;
    }
}