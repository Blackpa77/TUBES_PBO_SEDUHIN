<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\MenuRepository;
use App\Models\Order;
use App\Exceptions\BusinessException;
use App\Exceptions\ValidationException;

class OrderService
{
    private OrderRepository $orderRepo;
    private MenuRepository $menuRepo;

    public function __construct(OrderRepository $or, MenuRepository $mr)
    {
        $this->orderRepo = $or;
        $this->menuRepo = $mr;
    }

    // --- METHOD BARU: List Semua Order ---
    public function list(): array
    {
        $orders = $this->orderRepo->findAll();
        // Ubah objek menjadi array JSON-ready
        return array_map(fn($order) => $order->toArray(), $orders);
    }

    public function createOrder(array $payload): array
    {
        $order = new Order($payload);

        if (!$order->validate()) {
            throw new ValidationException('Invalid order', $order->items);
        }

        $total = 0;
        $itemsObjs = [];

        foreach ($payload['items'] as $it) {
            $menu = $this->menuRepo->findById((int)$it['menu_id']);

            if (!$menu) throw new BusinessException("Menu id {$it['menu_id']} not found");
            if ($menu->getStock() < (int)$it['qty']) 
                throw new BusinessException("Not enough stock for {$menu->toArray()['nama_produk']}");

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

    public function getOrder(int $id): array
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new BusinessException("Order not found");
        return $order->toArray();
    }
}