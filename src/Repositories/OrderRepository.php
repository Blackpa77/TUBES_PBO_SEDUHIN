<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Order;
use PDO;
use DateTime;

class OrderRepository
{
    private PDO $db;

    public function __construct() 
    { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $results = [];
        while ($r = $stmt->fetch()) {
            $results[] = $this->hydrate($r);
        }
        return $results;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ? $this->hydrate($r) : null;
    }

    public function save(Order $order): bool
    {
        $order->setCreatedAt(new DateTime());
        
        $sql = "INSERT INTO orders (nama_pelanggan, total_harga, status, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $order->namaPelanggan ?? 'Guest',
            $order->total, 
            $order->status, 
            $order->getCreatedAt()
        ]);

        if ($res) {
            $orderId = $this->db->lastInsertId();
            $order->setId((int)$orderId);
            $this->saveItems($order);
        }
        return $res;
    }

    // --- METHOD BARU: UPDATE ---
    public function update(Order $order): bool
    {
        $order->setUpdatedAt(new DateTime());
        
        $sql = "UPDATE orders SET nama_pelanggan = ?, status = ?, updated_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        // Kita hanya update info header (Status & Nama)
        return $stmt->execute([
            $order->namaPelanggan,
            $order->status,
            $order->getUpdatedAt(),
            $order->getId()
        ]);
    }

    // --- METHOD BARU: DELETE ---
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Helper untuk menyimpan item
    private function saveItems(Order $order): void
    {
        if (!empty($order->items)) {
            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, produk_id, qty, harga_saat_ini, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($order->items as $itm) {
                $subtotal = $itm->qty * $itm->price;
                $stmt->execute([$order->getId(), $itm->menuId, $itm->qty, $itm->price, $subtotal]);
            }
        }
    }

    // Helper untuk mapping DB ke Object
    private function hydrate(array $row): Order
    {
        $order = new Order([
            'customer_name' => $row['nama_pelanggan'],
            'total' => $row['total_harga'],
            'status' => $row['status']
        ]);
        $order->setId((int)$row['id']);
        
        if (!empty($row['created_at'])) $order->setCreatedAt(new DateTime($row['created_at']));
        if (!empty($row['updated_at'])) $order->setUpdatedAt(new DateTime($row['updated_at']));
        
        return $order;
    }
}