<?php

namespace App\Services;

use App\Repositories\MenuRepositoryInterface;
use App\Models\Menu;
use Exception;

class MenuService
{
    private MenuRepositoryInterface $repository;

    // CONSTRUCTOR INJECTION: Kunci dari 'D' di SOLID
    public function __construct(MenuRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createMenu(array $data): Menu
    {
        // 1. Buat Objek
        $menu = new Menu($data['nama_produk'], $data['harga'], $data['stok']);

        // 2. Validasi
        if (!$menu->validate()) {
            // Lempar error agar Controller tahu validasi gagal
            // (Asumsi Anda punya class ValidationException)
            throw new \App\Exceptions\ValidationException($menu->getErrors());
        }

        // 3. Simpan via Repository
        if (!$this->repository->save($menu)) {
            throw new Exception("Gagal menyimpan menu ke database.");
        }

        return $menu;
    }

    // ... method lain
}