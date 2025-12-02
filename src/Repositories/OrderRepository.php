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
        // 1. Ambil Header Order
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) return null;
        $order = OrderFactory::fromDb($row);

        // 2. Ambil Item Detail + Nama Produk (JOIN)
        // Perhatikan kita join ke tabel produk untuk dapat nama_produk
        $sql = "SELECT oi.*, p.nama_produk 
                FROM order_items oi
                JOIN produk p ON oi.produk_id = p.id
                WHERE oi.order_id = ?";
                
        $stmtItem = $this->db->prepare($sql);
        $stmtItem->execute([$id]);
        
        $items = [];
        while ($it = $stmtItem->fetch(PDO::FETCH_ASSOC)) {
            $obj = new \stdClass();
            $obj->menuId = (int)$it['produk_id'];
            $obj->qty = (int)$it['qty'];
            $obj->price = (float)$it['harga_saat_ini'];
            $obj->itemName = $it['nama_produk']; // <--- INI YANG PENTING
            $items[] = $obj;
        }
        
        $order->items = $items;
        return $order;
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

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function saveItems(Order $order): void {
        if (!empty($order->items)) {
            $sqlItem = "INSERT INTO order_items (order_id, produk_id, qty, harga_saat_ini, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sqlItem);
            foreach ($order->items as $itm) {
                $stmt->execute([$order->getId(), $itm->menuId, $itm->qty, $itm->price, $itm->qty*$itm->price]);
            }
        }
    }
}