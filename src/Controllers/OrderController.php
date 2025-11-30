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

    public function destroy(int $id): void
    {
        try {
            $this->service->deleteOrder($id);
            $this->send(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }

    // --- FITUR BARU: DOWNLOAD STRUK ---
    public function download(int $id): void
    {
        try {
            $order = $this->service->getOrder($id);
            
            // Format Struk Sederhana (Text)
            $struk = "=== STRUK BELANJA SEDUHIN ===\n";
            $struk .= "ID Order: #" . $order['id'] . "\n";
            $struk .= "Tanggal : " . $order['created_at'] . "\n";
            $struk .= "Pelanggan: " . $order['nama_pelanggan'] . "\n";
            $struk .= "-----------------------------\n";
            
            foreach ($order['items'] as $item) {
                $subtotal = $item->qty * $item->price;
                $struk .= "- ID Produk: " . $item->menuId . " x " . $item->qty . " = Rp " . number_format($subtotal) . "\n";
            }
            
            $struk .= "-----------------------------\n";
            $struk .= "TOTAL   : Rp " . number_format($order['total']) . "\n";
            $struk .= "STATUS  : " . strtoupper($order['status']) . "\n";
            $struk .= "=============================\n";
            $struk .= "Terima Kasih!";

            // Kirim sebagai file download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="struk_order_'.$id.'.txt"');
            echo $struk;
            exit;

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }
}