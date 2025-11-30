<?php
// 1. Set Timezone Wajib
date_default_timezone_set('Asia/Jakarta');

// 2. Load Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Core\App;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\AuthService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\MemberController;
use App\Builders\ApiResponseBuilder;

// 3. Error Handling (Biar ketahuan kalau ada error)
error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    exit;
});

// 4. CORS (Agar bisa diakses dari frontend manapun)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- 5. SETUP CONTAINER (Dependency Injection) ---

// Database Singleton
App::bind(Database::class, function() {
    return Database::getInstance();
});

// Menu Repository (Pake Interface)
App::bind(MenuRepositoryInterface::class, function() {
    return new MenuRepository(App::get(Database::class));
});

// Menu Service
App::bind(MenuService::class, function() {
    return new MenuService(App::get(MenuRepositoryInterface::class));
});

// Order Service & Repository (Manual wiring karena kompleks)
$orderRepo = new OrderRepository();
// Ambil MenuRepo dari container untuk dipakai di OrderService
$menuRepo = App::get(MenuRepositoryInterface::class); 
$orderService = new OrderService($orderRepo, $menuRepo);

// --- 6. INISIALISASI CONTROLLER ---
$menuController = new MenuController(App::get(MenuService::class));
$orderController = new OrderController($orderService);

// --- 7. DEFINISI ROUTE ---
$router = new Router();

// A. Rute Halaman Utama (Cek Server)
$router->get('/', function() {
    ApiResponseBuilder::success(null, 'Seduhin API Backend Ready!')->send();
});

// B. Rute Khusus Demo UI (Ini yang bikin UI temanmu muncul)
$router->get('/demo', function() {
    $file = __DIR__ . '/demo/index.html';
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
        exit;
    } else {
        echo "File UI tidak ditemukan di: " . $file;
        exit;
    }
});

// C. Routes Menu (CRUD)
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

// D. Routes Order (Transaksi)
$router->get('/orders', [$orderController, 'index']);       // Get All
$router->get('/orders/:id', [$orderController, 'show']);   // Get One
$router->post('/orders', [$orderController, 'store']);     // Create
$router->put('/orders/:id', [$orderController, 'update']); // Update
$router->delete('/orders/:id', [$orderController, 'destroy']); // Delete

// --- 8. JALANKAN ---
$router->dispatch();