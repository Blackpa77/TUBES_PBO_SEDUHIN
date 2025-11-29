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

    public function save(Order $order): bool
    {
        // Set waktu sekarang agar tidak null di JSON
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
            $orderId = $this->db->lastInsertId();
            $order->setId((int)$orderId);

            // 2. Simpan Detail Item ke order_items
            if (!empty($order->items)) {
                $stmtItem = $this->db->prepare("INSERT INTO order_items (order_id, produk_id, qty, harga_saat_ini, subtotal) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($order->items as $itm) {
                    $subtotal = $itm->qty * $itm->price;
                    $stmtItem->execute([
                        $orderId, 
                        $itm->menuId, 
                        $itm->qty, 
                        $itm->price,
                        $subtotal
                    ]);
                }
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

        $orderData = [
            'customer_name' => $r['nama_pelanggan'],
            'total' => $r['total_harga'],
            'status' => $r['status']
        ];
        
        $order = new Order($orderData);
        $order->setId((int)$r['id']);
        
        if (!empty($r['created_at'])) {
            $order->setCreatedAt(new DateTime($r['created_at']));
        }
        
        return $order;
    }

    // --- METHOD BARU: Ambil Semua Order ---
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $results = [];
        
        while ($r = $stmt->fetch()) {
            $orderData = [
                'customer_name' => $r['nama_pelanggan'],
                'total' => $r['total_harga'],
                'status' => $r['status']
            ];
            
            $order = new Order($orderData);
            $order->setId((int)$r['id']);
            
            if (!empty($r['created_at'])) {
                $order->setCreatedAt(new DateTime($r['created_at']));
            }
            
            $results[] = $order;
        }
        
        return $results;
    }
}