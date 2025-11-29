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
        // 1. Simpan Header Order
        // Pastikan model Order.php kamu nanti disesuaikan dengan kolom 'nama_pelanggan', 'total_harga'
        $sql = "INSERT INTO orders (nama_pelanggan, total_harga, status, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        // Asumsi di Order Model propertinya sudah disesuaikan (lihat langkah selanjutnya jika perlu)
        $res = $stmt->execute([
            $order->namaPelanggan ?? 'Guest', // Handle nama
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
                    $subtotal = $itm->qty * $itm->price; // Hitung subtotal
                    $stmtItem->execute([
                        $orderId, 
                        $itm->menuId, // Ini ID Produk
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

        // Mapping balik dari DB ke Model Order
        // Note: Kamu mungkin perlu update Model Order.php juga agar propertinya cocok
        $orderData = [
            'customer_name' => $r['nama_pelanggan'], // Mapping manual
            'total' => $r['total_harga'],
            'status' => $r['status']
        ];
        
        $order = new Order($orderData);
        $order->setId((int)$r['id']);
        
        return $order;
    }
}