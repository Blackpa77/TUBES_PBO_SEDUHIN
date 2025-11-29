<?php
namespace App\Factories;

use App\Models\Payment;
use App\Enums\PaymentMethod;

class PaymentFactory
{
    public static function create(array $data): Payment
    {
        return new Payment(
            method: PaymentMethod::from($data['method']),
            amount: $data['amount'],
            orderId: $data['order_id'],
        );
    }
}
