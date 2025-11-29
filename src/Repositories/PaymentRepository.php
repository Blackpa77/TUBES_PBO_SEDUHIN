<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Payment;

class PaymentRepository
{
    protected \PDO $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function insert(Payment $p): int
    {
        $stmt = $this->db->prepare('INSERT INTO payments (order_id, method, amount, paid_at) VALUES (?, ?, ?, ?)');
        $paidAt = $p->paid_at ?? date('Y-m-d H:i:s');
        $stmt->execute([$p->order_id, $p->method, $p->amount, $paidAt]);
        return (int)$this->db->lastInsertId();
    }

    public function findByOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}
