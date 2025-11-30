<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\MenuRepository;
use App\Models\Order;
use App\Exceptions\BusinessException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class OrderService
{
    private OrderRepository $orderRepo;
    private MenuRepository $menuRepo;

    public function __construct(OrderRepository $or, MenuRepository $mr)
    {
        $this->orderRepo = $or;
        $this->menuRepo = $mr;
    }

    public function list(): array
    {
        $orders = $this->orderRepo->findAll();
        return array_map(fn($o) => $o->toArray(), $orders);
    }

    public function getOrder(int $id): array
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new NotFoundException("Order not found");
        return $order->toArray();
    }

   public function createOrder(array $payload): array
    {
        $order = new Order($payload);
        
        // DEBUG: Kasih tau kenapa validasi gagal
        if (!$order->validate()) {
            // Ubah array items jadi string biar kebaca di pesan error
            throw new ValidationException('Invalid order: Data items kosong atau salah format.');
        }

        $total = 0;
        $itemsObjs = [];

        foreach ($payload['items'] as $it) {
            $menu = $this->menuRepo->findById((int)$it['menu_id']);
            
            // DEBUG: Kasih tau menu mana yang ga ketemu
            if (!$menu) throw new BusinessException("Menu dengan ID {$it['menu_id']} tidak ditemukan di database.");
            
            if ($menu->getStock() < (int)$it['qty']) 
                throw new BusinessException("Stok tidak cukup untuk: " . $menu->getName());

            $menu->reduceStock((int)$it['qty']);
            $this->menuRepo->save($menu);

            $oItem = new \stdClass();
            $oItem->menuId = $menu->getId();
            $oItem->qty = (int)$it['qty'];
            $oItem->price = $menu->getPrice();

            $itemsObjs[] = $oItem;
            $total += $oItem->qty * $oItem->price;
        }

        $order->items = $itemsObjs;
        $order->total = $total;

        $this->orderRepo->save($order);
        return $order->toArray();
    }

    // --- METHOD BARU: UPDATE ---
    public function updateOrder(int $id, array $payload): array
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new NotFoundException("Order not found");

        // Update data yang diperbolehkan (misal: Status atau Nama)
        if (isset($payload['status'])) $order->status = $payload['status'];
        if (isset($payload['nama_pelanggan'])) $order->namaPelanggan = $payload['nama_pelanggan'];

        $this->orderRepo->update($order);
        return $order->toArray();
    }

    // --- METHOD BARU: DELETE ---
    public function deleteOrder(int $id): void
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new NotFoundException("Order not found");
        
        $this->orderRepo->delete($id);
    }
}