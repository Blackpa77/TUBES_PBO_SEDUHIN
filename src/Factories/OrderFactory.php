<?php
namespace App\Factories;

use App\Models\Order;
use App\Enums\OrderStatus;

class OrderFactory
{
    public static function create(array $data): Order
    {
        return new Order(
            null,
            $data['customer_id'],
            0,
            OrderStatus::Pending,
            date('Y-m-d H:i:s')
        );
    }
}
