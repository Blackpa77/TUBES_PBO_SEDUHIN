<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Menu;
use PDO;
use DateTime;

class MenuRepository implements MenuRepositoryInterface
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    // --- UBAH NAMA METHOD INI JADI findById ---
    public function findById(int $id): ?Menu
    {
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return $this->hydrate($row);
    }
    // -------------------------------------------

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM produk WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category'])) { 
            $sql .= " AND id_kategori = ?"; 
            $params[] = $filters['category']; 
        }
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->hydrate($row);
        }
        return $results;
    }

    public function save(Menu $menu): bool
    {
        if ($menu->getId() === null) {
            return $this->insert($menu);
        } else {
            return $this->update($menu);
        }
    }

    private function insert(Menu $menu): bool
    {
        $menu->setCreatedAt(new DateTime());
        // Default id_kategori 1 jika kosong
        $sql = "INSERT INTO produk (nama_produk, id_kategori, harga, deskripsi, stok, foto_produk, created_at) VALUES (?, 1, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $menu->getNamaProduk(),
            $menu->getHarga(),
            'Deskripsi default', 
            $menu->getStok(),
            null,
            $menu->getCreatedAt()
        ]);

        if ($res) {
            $menu->setId((int)$this->db->lastInsertId());
        }
        return $res;
    }

    private function update(Menu $menu): bool
    {
        $menu->setUpdatedAt(new DateTime());
        $sql = "UPDATE produk SET nama_produk=?, harga=?, stok=?, updated_at=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $menu->getNamaProduk(),
            $menu->getHarga(),
            $menu->getStok(),
            $menu->getUpdatedAt(),
            $menu->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM produk WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function hydrate(array $row): Menu
    {
        $menu = new Menu(
            $row['nama_produk'],
            (float)$row['harga'],
            (int)$row['stok']
        );
        $menu->setId((int)$row['id']);
        
        if (!empty($row['created_at'])) $menu->setCreatedAt(new DateTime($row['created_at']));
        if (!empty($row['updated_at'])) $menu->setUpdatedAt(new DateTime($row['updated_at']));
        
        return $menu;
    }
}