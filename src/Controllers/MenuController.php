<?php

namespace App\Controllers;

use App\Services\MenuService;
use App\Core\Request; // Sesuaikan dengan class Request framework Anda
use App\Core\Response; // Sesuaikan dengan class Response framework Anda
use Exception;

class MenuController
{
    private MenuService $menuService;

    // INJECTION: Controller minta Service, bukan bikin sendiri
    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function store()
    {
        // 1. Ambil input (misal dari $_POST atau Request object)
        $data = [
            'nama_produk' => $_POST['nama_produk'] ?? '',
            'harga' => (float) ($_POST['harga'] ?? 0),
            'stok' => (int) ($_POST['stok'] ?? 0)
        ];

        try {
            // 2. Lempar ke Service
            $menu = $this->menuService->createMenu($data);

            // 3. Sukses
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => $menu->getId(),
                    'nama_produk' => $menu->getNamaProduk()
                ]
            ]);

        } catch (\App\Exceptions\ValidationException $e) {
            // 4. Gagal Validasi (Error 400)
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'status' => 'fail',
                'errors' => $e->getErrors() // Array error dari exception
            ]);

        } catch (Exception $e) {
            // 5. Gagal Server/DB (Error 500)
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // ... method index, show, update, delete lainnya
}