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
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }

    public function show(int $id): void
    {
        try {
            $o = $this->service->getOrder($id);
            $this->send($o);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }

    // --- UPDATE STATUS ---
    public function update(int $id): void
    {
        try {
            $payload = $this->getJson();
            $order = $this->service->updateOrder($id, $payload);
            $this->send($order);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }

    // --- DELETE ORDER ---
    public function destroy(int $id): void
    {
        try {
            $this->service->deleteOrder($id);
            $this->send(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }
    
    // --- DOWNLOAD STRUK ---
    public function download(int $id): void
    {
        try {
            $order = $this->service->getOrder($id);
            // HTML Struk Sederhana
            echo "<html><body style='font-family:monospace; width:300px; border:1px solid #ccc; padding:20px;'>";
            echo "<h2 style='text-align:center'>SEDUHIN</h2>";
            echo "<p>Order #{$order['id']}<br>Date: {$order['created_at']}</p>";
            echo "<hr>";
            foreach($order['items'] as $item) {
                echo "<div style='display:flex; justify-content:space-between'>";
                echo "<span>Menu #{$item->menuId} x{$item->qty}</span>";
                echo "<span>".number_format($item->price * $item->qty)."</span>";
                echo "</div>";
            }
            echo "<hr><h3 style='text-align:right'>Total: ".number_format($order['total'])."</h3>";
            echo "<script>window.print();</script></body></html>";
            exit;
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }
}