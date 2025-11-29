<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Menu;
use PDO;

class MenuRepository
{
    private PDO $db;

    public function __construct()
    {
        // PERBAIKAN: Tambahkan ->getConnection()
        // Kita butuh objek PDO aslinya untuk bisa melakukan query SQL
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Menu
    {
        // PERBAIKAN: Hapus label 'query:' dan 'params:' biar standar dan tidak error
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        
        $row = $stmt->fetch();
        if (!$row) return null;

        // Hydrate data ke Model Menu
        $menu = new Menu($row);
        $menu->setId((int)$row['id']);
        
        // Pastikan created_at ada sebelum di-set
        if (!empty($row['created_at'])) {
            $menu->setCreatedAt(new \DateTime($row['created_at']));
        }
        
        return $menu;
    }
    
    // ... method lainnya (findAll, save, delete) ikuti pola yang sama ...
}