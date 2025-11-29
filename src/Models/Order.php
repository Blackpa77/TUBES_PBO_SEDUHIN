<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

class Order
{
    public ?int $id = null;
    public ?int $customer_id = null;
    public array $items = [];
    public float $total = 0;

    public OrderStatus $status;
    public PaymentMethod $payment_method;

    private array $errors = [];

    public function __construct(array $data)
    {
        $this->customer_id = $data['customer_id'] ?? null;

        // Set default enum jika belum ada
        $this->status = isset($data['status']) && $data['status'] instanceof OrderStatus
            ? $data['status']
            : OrderStatus::PENDING;

        $this->payment_method = isset($data['payment_method']) && $data['payment_method'] instanceof PaymentMethod
            ? $data['payment_method']
            : PaymentMethod::CASH;
    }

    public function validate(): bool
    {
        $this->errors = [];

        if (!$this->customer_id || !is_numeric($this->customer_id)) {
            $this->errors['customer_id'] = "Customer ID is required and must be numeric";
        }

        // Validasi Enum
        if (!($this->status instanceof OrderStatus)) {
            $this->errors['status'] = "Invalid order status";
        }

        if (!($this->payment_method instanceof PaymentMethod)) {
            $this->errors['payment_method'] = "Invalid payment method";
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'items' => $this->items,
            'total' => $this->total,
            'status' => $this->status->value,
            'payment_method' => $this->payment_method->value
        ];
    }
}