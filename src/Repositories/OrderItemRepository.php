<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\OrderItem;

class OrderItemRepository
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function insert(OrderItem $item): int
    {
        $stmt = $this->db->prepare('INSERT INTO order_items (order_id, menu_id, qty, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$item->order_id, $item->menu_id, $item->qty, $item->price]);
        return (int)$this->db->lastInsertId();
    }

    public function findByOrderId(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
