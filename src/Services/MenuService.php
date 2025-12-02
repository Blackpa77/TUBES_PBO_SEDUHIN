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

    public function list(array $filters = []): array {
        $items = $this->repo->findAll($filters);
        return array_map(fn($m)=>$m->toArray(), $items);
    }

    public function get(int $id): array {
        // PERBAIKAN: Pakai findById
        $m = $this->repo->findById($id);
        if (!$m) throw new NotFoundException("Menu not found");
        return $m->toArray();
    }

    public function create(array $data): array {
        $menu = new Menu(
            $data['nama_produk'] ?? $data['name'] ?? '',
            (float)($data['harga'] ?? $data['price'] ?? 0),
            (int)($data['stok'] ?? 0),
            (int)($data['id_kategori'] ?? $data['category'] ?? 1),
            $data['deskripsi'] ?? '',
            $data['foto_produk'] ?? null
        );

        if (!$menu->validate()) throw new ValidationException("Validation failed", $menu->getErrors());
        
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array {
        // PERBAIKAN: Pakai findById
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");
        
        // Update data (Pake Getter Bahasa Indonesia yang pasti ada di Model)
        $nama = $data['nama_produk'] ?? $data['name'] ?? $existing->getNamaProduk();
        $harga = isset($data['harga']) ? (float)$data['harga'] : $existing->getHarga();
        $stok = isset($data['stok']) ? (int)$data['stok'] : $existing->getStok();
        
        // Cek method getIdKategori (jaga-jaga kalau model belum update)
        $kategori = 1;
        if(method_exists($existing, 'getIdKategori')) {
            $kategori = $existing->getIdKategori();
        }
        $kategori = isset($data['id_kategori']) ? (int)$data['id_kategori'] : $kategori;

        $deskripsi = $data['deskripsi'] ?? $existing->getDeskripsi();
        $foto = $data['foto_produk'] ?? $existing->getFotoProduk();

        $updatedMenu = new Menu($nama, $harga, $stok, $kategori, $deskripsi, $foto);
        $updatedMenu->setId($id);
        
        if (!$updatedMenu->validate()) throw new ValidationException("Validation failed", $updatedMenu->getErrors());
        
        $this->repo->save($updatedMenu);
        return $updatedMenu->toArray();
    }

    public function delete(int $id): void {
        // PERBAIKAN: Cek exist dulu
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");

        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new \Exception("Gagal menghapus. Menu sedang digunakan di transaksi lain.");
        }
    }
}