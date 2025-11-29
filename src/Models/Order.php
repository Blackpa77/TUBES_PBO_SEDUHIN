<?php
namespace App\Models;

use App\Enums\OrderStatus;

class Order
{
    public ?int $id = null;
    public ?int $customer_id = null;
    public float $total = 0.0;
    public string $status = OrderStatus::PENDING->value;
    /** @var OrderItem[] */
    public array $items = [];
    public ?string $created_at = null;
}
