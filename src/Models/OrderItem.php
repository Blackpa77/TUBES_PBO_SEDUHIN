<?php
namespace App\Models;

class OrderItem
{
    public ?int $id = null;
    public ?int $order_id = null;
    public int $menu_id;
    public int $qty;
    public float $price;
}
