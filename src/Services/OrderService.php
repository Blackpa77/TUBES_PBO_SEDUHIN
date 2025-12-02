<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\LogRepository; // Pastikan ini ada
use App\Models\Order;
use App\Exceptions\BusinessException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class OrderService
{
    private OrderRepository $orderRepo;
    private MenuRepositoryInterface $menuRepo;
    private LogRepository $logger;

    public function __construct(OrderRepository $or, MenuRepositoryInterface $mr)
    {
        $this->orderRepo = $or;
        $this->menuRepo = $mr;
        // Inisialisasi Logger Manual (Pengganti Trigger)
        $this->logger = new LogRepository();
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
        if (!$order->validate()) throw new ValidationException('Invalid order');

        $total = 0;
        $itemsObjs = [];

        foreach ($payload['items'] as $it) {
            $menu = $this->menuRepo->findById((int)$it['menu_id']);
            if (!$menu) throw new BusinessException("Menu id {$it['menu_id']} not found");
            
            if ($menu->getStock() < (int)$it['qty']) 
                throw new BusinessException("Not enough stock for {$menu->getName()}");

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
        
        // LOG MANUAL: Transaksi Baru
        $this->logger->log('Transaksi Baru', "Pelanggan: {$order->namaPelanggan}, Total: Rp " . number_format($order->total));

        return $order->toArray();
    }

    public function updateOrder(int $id, array $payload): array
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new NotFoundException("Order not found");

        // Update data
        if (isset($payload['status'])) {
            $oldStatus = $order->status;
            $order->status = $payload['status'];
            
            // LOG MANUAL: Update Status
            $this->logger->log('Update Status', "Order #{$id}: {$oldStatus} -> {$order->status}");
        }
        
        if (isset($payload['nama_pelanggan'])) {
            $order->namaPelanggan = $payload['nama_pelanggan'];
        }

        $this->orderRepo->update($order);
        return $order->toArray();
    }

    public function deleteOrder(int $id): void
    {
        $order = $this->orderRepo->findById($id);
        if (!$order) throw new NotFoundException("Order not found");
        
        $this->orderRepo->delete($id);

        // LOG MANUAL: Hapus Order
        $this->logger->log('Hapus Order', "Order #{$id} dihapus dari sistem");
    }
}