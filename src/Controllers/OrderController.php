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

    // --- CETAK STRUK PDF ---
    public function download(int $id): void
    {
        try {
            $order = $this->service->getOrder($id);
            
            $date = date('d/m/Y H:i', strtotime($order['created_at']));
            
            // Tampilan Struk Thermal 58mm/80mm
            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Struk #{$order['id']}</title>
                <style>
                    @page { size: 80mm auto; margin: 0; }
                    body {
                        font-family: 'Courier New', monospace;
                        width: 75mm;
                        margin: 0 auto;
                        padding: 10px;
                        background: #fff;
                        color: #000;
                        font-size: 12px;
                    }
                    h2, p { margin: 0; text-align: center; }
                    .line { border-bottom: 1px dashed #000; margin: 8px 0; }
                    .item { display: flex; justify-content: space-between; margin-bottom: 4px; }
                    .item-name { font-weight: bold; display: block; margin-bottom: 2px; }
                    .item-detail { font-size: 11px; color: #333; display: flex; justify-content: space-between; }
                    .total { font-weight: bold; font-size: 14px; margin-top: 5px; display: flex; justify-content: space-between; }
                    .footer { text-align: center; margin-top: 15px; font-size: 10px; }
                    .btn-print {
                        display: block; width: 100%; padding: 10px; 
                        background: #333; color: #fff; border: none; 
                        cursor: pointer; margin-top: 20px;
                    }
                    @media print { .btn-print { display: none; } }
                </style>
            </head>
            <body>
                <h2>SEDUHIN COFFEE</h2>
                <p>Jl. Ahmad Yani No. 1</p>
                
                <div class='line'></div>
                
                <div style='display:flex; justify-content:space-between;'>
                    <span>ORDER #{$order['id']}</span>
                    <span>{$date}</span>
                </div>
                <div>Pelanggan: {$order['nama_pelanggan']}</div>
                <div>Kasir: Admin</div>
                
                <div class='line'></div>
            ";

            foreach ($order['items'] as $item) {
                $subtotal = $item->qty * $item->price;
                $priceFmt = number_format($item->price, 0, ',', '.');
                $subtotalFmt = number_format($subtotal, 0, ',', '.');
                
                // Gunakan itemName yang sudah diambil dari Repository
                $namaProduk = $item->itemName ?? "Menu #{$item->menuId}";

                $html .= "
                <div style='margin-bottom: 8px;'>
                    <span class='item-name'>{$namaProduk}</span>
                    <div class='item-detail'>
                        <span>{$item->qty} x {$priceFmt}</span>
                        <span>{$subtotalFmt}</span>
                    </div>
                </div>";
            }

            $total = number_format($order['total'], 0, ',', '.');

            $html .= "
                <div class='line'></div>
                <div class='total'>
                    <span>TOTAL</span>
                    <span>Rp {$total}</span>
                </div>
                <div style='text-align:right; font-size:11px; margin-top:2px;'>
                    Status: " . strtoupper($order['status']) . "
                </div>
                
                <div class='line'></div>
                <div class='footer'>
                    Terima Kasih atas kunjungan Anda!<br>
                    Password Wifi: kopienak
                </div>

                <button class='btn-print' onclick='window.print()'>🖨️ CETAK</button>
                <script>window.onload = function() { window.print(); }</script>
            </body>
            </html>";

            echo $html;
            exit;

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }
}