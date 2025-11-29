<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\OrderService;
use App\Builders\ApiResponseBuilder;

class OrderController extends Controller
{
    private OrderService $service;
    public function __construct(OrderService $s) { $this->service = $s; }

    public function index(): void
    {
        try {
            $data = $this->service->list();
            $this->send($data);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    public function store(): void
    {
        try {
            $payload = $this->getJson();
            $order = $this->service->createOrder($payload);
            ApiResponseBuilder::created($order, 'Order placed')->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    public function show(int $id): void
    {
        try {
            $o = $this->service->getOrder($id);
            $this->send($o);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 404)->send();
        }
    }

    // --- METHOD BARU: UPDATE ---
    public function update(int $id): void
    {
        try {
            $payload = $this->getJson();
            $order = $this->service->updateOrder($id, $payload);
            $this->send($order, 200);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    // --- METHOD BARU: DESTROY ---
    public function destroy(int $id): void
    {
        try {
            $this->service->deleteOrder($id);
            $this->send(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }
}