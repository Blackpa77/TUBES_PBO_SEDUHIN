<?php
// Set Timezone
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;

// Error Handling
error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
});

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- MANUAL DEPENDENCY INJECTION (WIRING) ---

// 1. Siapkan Database Instance
$database = Database::getInstance();

// 2. Rakit Modul Menu (Clean Architecture)
$menuRepo = new MenuRepository($database);
$menuService = new MenuService($menuRepo);
$menuController = new MenuController($menuService);

// 3. Rakit Modul Order (Menggunakan MenuRepo juga untuk cek stok)
// Note: OrderRepository belum kita ubah ke Interface, jadi pakai cara biasa dulu
$orderRepo = new OrderRepository(); 
$orderService = new OrderService($orderRepo, $menuRepo); // MenuRepo di-inject ke sini juga
$orderController = new OrderController($orderService);

// ---------------------------------------------

$router = new Router();

// Routes Menu
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

// Routes Order
$router->get('/orders', [$orderController, 'index']);
$router->get('/orders/:id', [$orderController, 'show']);
$router->post('/orders', [$orderController, 'store']);
$router->put('/orders/:id', [$orderController, 'update']); 
$router->delete('/orders/:id', [$orderController, 'destroy']); 

$router->dispatch();