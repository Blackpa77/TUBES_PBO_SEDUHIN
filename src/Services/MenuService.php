<?php
namespace App\Services;

use App\Repositories\MenuRepositoryInterface;
use App\Models\Menu;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class MenuService
{
    private MenuRepositoryInterface $repo;

    // Dependency Injection: Minta Interface
    public function __construct(MenuRepositoryInterface $repo) 
    { 
        $this->repo = $repo; 
    }

    public function list(array $filters = []): array {
        $items = $this->repo->findAll($filters);
        return array_map(fn($m)=>$m->toArray(), $items);
    }

    public function get(int $id): array {
        $m = $this->repo->findById($id);
        if (!$m) throw new NotFoundException("Menu not found");
        return $m->toArray();
    }

    public function create(array $data): array {
        // Mapping input JSON ke Model Constructor
        $menu = new Menu(
            $data['nama_produk'] ?? $data['name'] ?? '',
            (float)($data['harga'] ?? $data['price'] ?? 0),
            (int)($data['stok'] ?? 0),
            (int)($data['id_kategori'] ?? $data['category'] ?? 1),
            $data['deskripsi'] ?? ''
        );

        if (!$menu->validate()) {
            throw new ValidationException("Validation failed", $menu->getErrors());
        }
        
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array {
        $menu = $this->repo->findById($id);
        if (!$menu) throw new NotFoundException("Menu not found");
        
        // Update Data: Buat objek baru dengan data gabungan
        $updatedMenu = new Menu(
            $data['nama_produk'] ?? $data['name'] ?? $menu->getNamaProduk(),
            (float)($data['harga'] ?? $data['price'] ?? $menu->getHarga()),
            (int)($data['stok'] ?? $menu->getStok()),
            (int)($data['id_kategori'] ?? $data['category'] ?? $menu->getIdKategori()),
            $data['deskripsi'] ?? $menu->getDeskripsi()
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