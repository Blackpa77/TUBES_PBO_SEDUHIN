<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Order;
use App\Factories\OrderFactory; // Wajib ada
use PDO;
use DateTime;

class OrderRepository
{
    private PDO $db;

    // Konstruktor tanpa parameter (sesuai manual wiring di index.php)
    public function __construct() 
    { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Gunakan Factory
            $results[] = OrderFactory::fromDb($row);
        }
        return $results;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) return null;

        // Gunakan Factory
        return OrderFactory::fromDb($row);
    }

    public function save(Order $order): bool
    {
        // Set waktu dibuat
        $order->setCreatedAt(new DateTime());
        
        // 1. Simpan Header Order
        $sql = "INSERT INTO orders (nama_pelanggan, total_harga, status, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $res = $stmt->execute([
            $order->namaPelanggan ?? 'Guest',
            $order->total, 
            $order->status, 
            $order->getCreatedAt()
        ]);

        if ($res) {
            // Ambil ID yang baru dibuat
            $orderId = $this->db->lastInsertId();
            $order->setId((int)$orderId);

            // 2. Simpan Detail Item ke tabel order_items
            $this->saveItems($order);
        }
        return $res;
    }

    public function update(Order $order): bool
    {
        $order->setUpdatedAt(new DateTime());
        
        $sql = "UPDATE orders SET nama_pelanggan = ?, status = ?, updated_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $order->namaPelanggan,
            $order->status,
            $order->getUpdatedAt(),
            $order->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Helper untuk menyimpan item belanjaan
    private function saveItems(Order $order): void
    {
        if (!empty($order->items)) {
            $sqlItem = "INSERT INTO order_items (order_id, produk_id, qty, harga_saat_ini, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sqlItem);
            
            foreach ($order->items as $itm) {
                // Pastikan properti item tersedia
                // Service mengirim object stdClass, jadi akses pake ->
                $menuId = $itm->menuId ?? 0;
                $qty    = $itm->qty ?? 0;
                $price  = $itm->price ?? 0;
                $subtotal = $qty * $price;

                $stmt->execute([
                    $order->getId(), 
                    $menuId, 
                    $qty, 
                    $price, 
                    $subtotal
                ]);
            }
        }
    }
}