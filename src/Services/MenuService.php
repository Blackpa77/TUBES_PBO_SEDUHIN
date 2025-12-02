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
        $m = $this->repo->findById($id); // Pastikan pakai findById
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

    // --- PERBAIKAN BAGIAN INI ---
    public function update(int $id, array $data): array {
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");
        
        // Kita gunakan Getter Bahasa Indonesia (getNamaProduk, getHarga) 
        // karena ini yang PASTI ada di Model kamu (sesuai database)
        
        $nama = $data['nama_produk'] ?? $data['name'] ?? $existing->getNamaProduk();
        $harga = isset($data['harga']) ? (float)$data['harga'] : $existing->getHarga();
        $stok = isset($data['stok']) ? (int)$data['stok'] : $existing->getStok(); // getStok (Indo)
        
        // Cek apakah getter id kategori ada, kalau tidak default ke 1
        $kategori = method_exists($existing, 'getIdKategori') ? $existing->getIdKategori() : 1;
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
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");

        $deleted = $this->repo->delete($id);
        if (!$deleted) {
            throw new \Exception("Gagal menghapus. Menu sedang digunakan di transaksi lain.");
        }
    }
}