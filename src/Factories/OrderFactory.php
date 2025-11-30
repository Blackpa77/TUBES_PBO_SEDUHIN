<?php
namespace App\Factories;

use App\Models\Order;
use DateTime;

class OrderFactory
{
    public static function fromDb(array $row): Order
    {
        $order = new Order([
            'customer_name' => $row['nama_pelanggan'],
            'total' => (float)$row['total_harga'],
            'status' => $row['status']
        ]);
        
        $order->setId((int)$row['id']);
        
        if (!empty($row['created_at'])) {
            $order->setCreatedAt(new DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $order->setUpdatedAt(new DateTime($row['updated_at']));
        }

        return $order;
    }
}