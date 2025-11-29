<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Menu;
use PDO;

class MenuRepository
{
<<<<<<< HEAD
    private \PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
=======
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
>>>>>>> b54210bbf0ed62d3d12e04fa357e61d26cf6f1bf

    public function findById(int $id): ?Menu
    {
        // Sesuaikan nama tabel ke 'produk'
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        
        $menu = new Menu($row);
        $menu->setId((int)$row['id']);
        if (!empty($row['created_at'])) $menu->setCreatedAt(new \DateTime($row['created_at']));
        return $menu;
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM produk WHERE 1=1";
        $params = [];
        
        // Filter berdasarkan kategori (sesuaikan kolom id_kategori)
        if (!empty($filters['category'])) { 
            $sql .= " AND id_kategori = ?"; 
            $params[] = $filters['category']; 
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = [];
        while ($r = $stmt->fetch()) {
            $m = new Menu($r); 
            $m->setId((int)$r['id']);
            $res[] = $m;
        }
        return $res;
    }

    public function save(Menu $menu): bool { return $menu->save(); }
    
    public function delete(int $id): bool
    {
        // Panggil method delete dari Model yang sudah diperbaiki
        $menu = $this->findById($id);
        if (!$menu) return false;
        return $menu->delete();
    }
}