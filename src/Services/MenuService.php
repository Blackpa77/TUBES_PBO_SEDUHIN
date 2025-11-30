<?php
namespace App\Services;

use App\Repositories\MenuRepositoryInterface;
use App\Models\Menu;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class MenuService
{
    private MenuRepositoryInterface $repo;

    public function __construct(MenuRepositoryInterface $repo) 
    { 
        $this->repo = $repo; 
    }

    // --- METHOD BARU: List untuk UI ---
    public function list(array $filters = []): array {
        $items = $this->repo->findAll($filters);
        return array_map(fn($m)=>$m->toArray(), $items);
    }

    public function get(int $id): array {
        $m = $this->repo->find($id);
        if (!$m) throw new NotFoundException("Menu not found");
        return $m->toArray();
    }

    public function create(array $data): array {
        // Mapping input
        $nama = $data['nama_produk'] ?? $data['name'] ?? '';
        $harga = (float)($data['harga'] ?? $data['price'] ?? 0);
        $stok = (int)($data['stok'] ?? 0);

        $menu = new Menu($nama, $harga, $stok);

        if (!$menu->validate()) {
            throw new ValidationException("Validation failed", $menu->getErrors());
        }
        
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array {
        $menu = $this->repo->find($id);
        if (!$menu) throw new NotFoundException("Menu not found");
        
        // Buat objek baru untuk update (sederhana)
        $updatedMenu = new Menu(
            $data['nama_produk'] ?? $data['name'] ?? $menu->getNamaProduk(),
            (float)($data['harga'] ?? $data['price'] ?? $menu->getHarga()),
            (int)($data['stok'] ?? $menu->getStok())
        );
        $updatedMenu->setId($id);
        
        if (!$updatedMenu->validate()) {
            throw new ValidationException("Validation failed", $updatedMenu->getErrors());
        }
        
        $this->repo->save($updatedMenu);
        return $updatedMenu->toArray();
    }

    public function delete(int $id): void {
        $deleted = $this->repo->delete($id);
        if (!$deleted) throw new NotFoundException("Menu not found");
    }
}