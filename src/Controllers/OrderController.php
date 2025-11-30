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

    // --- FITUR BARU: CETAK STRUK PDF (HTML View) ---
    public function download(int $id): void
    {
        try {
            $order = $this->service->getOrder($id);
            
            // Tampilan HTML untuk Struk
            $html = "
            <html>
            <head>
                <title>Struk #{$order['id']}</title>
                <style>
                    body { font-family: monospace; max-width: 300px; margin: 20px auto; border: 1px solid #ddd; padding: 20px; }
                    h2 { text-align: center; margin-bottom: 5px; }
                    .meta { font-size: 12px; text-align: center; color: #666; margin-bottom: 20px; }
                    .item { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 14px; }
                    .line { border-top: 1px dashed #000; margin: 10px 0; }
                    .total { font-weight: bold; font-size: 16px; display: flex; justify-content: space-between; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; }
                    @media print {
                        body { border: none; margin: 0; }
                        button { display: none; }
                    }
                </style>
            </head>
            <body>
                <h2>SEDUHIN COFFEE</h2>
                <div class='meta'>
                    Jl. Ahmad Yani No. 1<br>
                    Order #{$order['id']} | " . date('d/m/Y H:i', strtotime($order['created_at'])) . "<br>
                    Pelanggan: {$order['nama_pelanggan']}
                </div>
                
                <div class='line'></div>
            ";

            foreach ($order['items'] as $item) {
                $subtotal = $item->qty * $item->price;
                // Karena di order_items nama produk tidak tersimpan (hanya ID), kita tampilkan ID atau ambil ulang
                // Untuk simplifikasi tugas, kita tampilkan format Qty x Harga
                $html .= "
                <div class='item'>
                    <span>Menu #{$item->menuId} (x{$item->qty})</span>
                    <span>Rp " . number_format($subtotal) . "</span>
                </div>";
            }

            $html .= "
                <div class='line'></div>
                <div class='total'>
                    <span>TOTAL</span>
                    <span>Rp " . number_format($order['total']) . "</span>
                </div>
                <div class='item' style='margin-top:5px'>
                    <span>Status</span>
                    <span>" . strtoupper($order['status']) . "</span>
                </div>

                <div class='footer'>
                    Terima Kasih!<br>
                    <i>Simpan struk ini sebagai bukti pembayaran.</i>
                </div>

                <button onclick='window.print()' style='width:100%; padding:10px; margin-top:20px; cursor:pointer'>🖨️ CETAK / SIMPAN PDF</button>
                
                <script>window.print();</script>
            </body>
            </html>";

            echo $html;
            exit;

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }
}