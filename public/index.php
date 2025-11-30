<?php

use App\Core\App;
use App\Core\Database;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Services\MenuService;

// 1. Load Autoloader (Wajib)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. SETUP CONTAINER (WIRING)
// Bagian ini memberi tahu aplikasi cara membuat object yang rumit

// A. Jika ada yang minta "MenuRepositoryInterface", kasih "MenuRepository" yang sudah pegang Database
App::bind(MenuRepositoryInterface::class, function() {
    // Pastikan class Database Anda bisa di-instantiate seperti ini
    return new MenuRepository(new Database()); 
});

// B. Jika ada yang minta "MenuService", kasih MenuService yang sudah diisi Repository
App::bind(MenuService::class, function() {
    // Kita minta container untuk carikan implementasi MenuRepositoryInterface
    $repo = App::resolve(MenuRepositoryInterface::class);
    return new MenuService($repo);
});

// 3. JALANKAN APLIKASI
// Pastikan Router Anda menggunakan App::resolve() untuk memanggil Controller!
$app = new App();
$app->run();