<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Order;
use App\Factories\OrderFactory;
use PDO;
use DateTime;

class OrderRepository
{
    private PDO $db;

    public function __construct() { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = OrderFactory::fromDb($row);
        }
        return $results;
    }

    public function findById(int $id): ?Order {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? OrderFactory::fromDb($row) : null;
    }

    public function save(Order $order): bool {
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

    // --- UPDATE ---
    public function update(Order $order): bool {
        $order->setUpdatedAt(new DateTime());
        $sql = "UPDATE orders SET status=?, updated_at=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $order->status,
            $order->getUpdatedAt(),
            $order->getId()
        ]);
    }

    // --- DELETE ---
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function saveItems(Order $order): void {
        if (!empty($order->items)) {
            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, produk_id, qty, harga_saat_ini, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($order->items as $itm) {
                $stmt->execute([$order->getId(), $itm->menuId, $itm->qty, $itm->price, $itm->qty*$itm->price]);
            }
        }
    }
}