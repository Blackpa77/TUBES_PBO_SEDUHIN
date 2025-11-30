<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Menu;
use PDO;

class MenuRepository implements MenuRepositoryInterface
{
    private PDO $db;

    // Dependency Injection: Database disuntikkan, bukan dipanggil statis
    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function find(int $id): ?Menu
    {
        $stmt = $this->db->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        // Mapping Manual Row ke Object (Bisa juga pakai Factory)
        $menu = new Menu($row['nama_produk'], $row['harga'], $row['stok']);
        $menu->setId($row['id']);
        
        return $menu;
    }

    public function save(Menu $menu): bool
    {
        // Logika Pintar: Cek ID untuk tentukan Insert atau Update
        if ($menu->getId() === null) {
            return $this->insert($menu);
        } else {
            return $this->update($menu);
        }
    }

    private function insert(Menu $menu): bool
    {
        $sql = "INSERT INTO produk (nama_produk, harga, stok) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            $menu->getNamaProduk(),
            $menu->getHarga(),
            $menu->getStok()
        ]);

        if ($result) {
            // Set ID yang baru dibuat kembali ke objek
            $menu->setId((int)$this->db->lastInsertId());
        }

        return $result;
    }

    private function update(Menu $menu): bool
    {
        $sql = "UPDATE produk SET nama_produk = ?, harga = ?, stok = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $menu->getNamaProduk(),
            $menu->getHarga(),
            $menu->getStok(),
            $menu->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM produk WHERE id = ?");
        return $stmt->execute([$id]);
    }
}