<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Order;
use PDO;

class OrderRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function save(Order $order): bool
    {
        // save order
        $res = $order->save();
        // Also save items
        if ($res && !empty($order->items)) {
            foreach ($order->items as $itm) {
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, menu_id, qty, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order->getId(), $itm->menuId, $itm->qty, $itm->price]);
            }
        }
        return $res;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) return null;
        $order = new Order($r);
        $order->setId((int)$r['id']);
        // load items
        $stmt2 = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt2->execute([$id]);
        $items = [];
        while ($it = $stmt2->fetch()) {
            $oi = new \stdClass();
            $oi->menuId = (int)$it['menu_id'];
            $oi->qty = (int)$it['qty'];
            $oi->price = (float)$it['price'];
            $oi->toArray = fn() => ['menu_id'=>$oi->menuId,'qty'=>$oi->qty,'price'=>$oi->price];
            $items[] = $oi;
        }
        $order->items = $items;
        return $order;
    }
}
