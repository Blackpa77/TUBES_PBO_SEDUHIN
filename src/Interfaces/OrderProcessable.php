<?php
namespace App\Interfaces;

interface OrderProcessable
{
    public function calculateTotal(int $orderId): float;
    public function finalize(int $orderId): bool;
}
