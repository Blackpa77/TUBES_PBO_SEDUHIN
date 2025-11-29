<?php
namespace App\Models;

class Order
{
    public ?int $id = null;
    public ?int $customer_id = null;
    public array $items = [];
    public float $total = 0;

    private array $errors = [];

    public function __construct(array $data)
    {
        $this->customer_id = $data['customer_id'] ?? null;
    }

    public function validate(): bool
    {
        $this->errors = [];

        if (!$this->customer_id || !is_numeric($this->customer_id)) {
            $this->errors['customer_id'] = "Customer ID is required and must be numeric";
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
            'total' => $this->total
        ];
    }
}