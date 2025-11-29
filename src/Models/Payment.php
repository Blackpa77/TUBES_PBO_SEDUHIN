<?php
namespace App\Models;

class Payment
{
    public ?int $id = null;
    public ?int $order_id = null;
    public string $method;
    public float $amount;
    public ?string $paid_at = null;
}
