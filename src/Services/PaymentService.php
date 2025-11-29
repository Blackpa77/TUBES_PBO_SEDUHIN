<?php
namespace App\Services;

use App\Interfaces\Payable;
use App\Factories\PaymentFactory;
use App\Repositories\PaymentRepository;
use App\Models\Payment;
use App\Enums\PaymentMethod;

class PaymentService implements Payable
{
    protected PaymentRepository $repo;
    public function __construct(PaymentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function pay(array $payload): Payment
    {
        $payment = PaymentFactory::fromPayload($payload);
        if (!in_array($payment->method, array_map(fn($m)=>$m->value, PaymentMethod::cases()))) {
            throw new \InvalidArgumentException('Invalid payment method', 400);
        }
        $payment->paid_at = date('Y-m-d H:i:s');
        $id = $this->repo->insert($payment);
        $payment->id = $id;
        return $payment;
    }
}
