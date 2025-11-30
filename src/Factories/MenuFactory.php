<?php
namespace App\Factories;

use App\Models\Menu;
use DateTime;

class MenuFactory
{
    /**
     * Factory Method: Mengubah array database menjadi Objek Menu
     */
    public static function fromDb(array $row): Menu
    {
        // 1. Buat Objek Dasar
        $menu = new Menu(
            $row['nama_produk'],
            (float)$row['harga'],
            (int)$row['stok'],
            (int)$row['id_kategori'],
            $row['deskripsi'] ?? '',
            $row['foto_produk'] ?? null
        );

        // 2. Set Data Tambahan (ID & Timestamp)
        $menu->setId((int)$row['id']);
        
        if (!empty($row['created_at'])) {
            $menu->setCreatedAt(new DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $menu->setUpdatedAt(new DateTime($row['updated_at']));
        }
        
        return $menu;
    }
}