<?php
// Set Timezone
date_default_timezone_set('Asia/Jakarta');

// 1. Load Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Core\App;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\MemberController; // Jika mau dipakai
use App\Builders\ApiResponseBuilder;

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

// --- 2. SETUP CONTAINER & WIRING (PERBAIKAN DI SINI) ---

// Binding Database (Pakai getInstance karena Singleton)
App::bind(Database::class, function() {
    return Database::getInstance();
});

// Binding Repository
App::bind(MenuRepositoryInterface::class, function() {
    // MenuRepository butuh Database
    return new MenuRepository(App::get(Database::class));
});

// Binding Service
App::bind(MenuService::class, function() {
    // MenuService butuh MenuRepositoryInterface
    return new MenuService(App::get(MenuRepositoryInterface::class));
});

// --- 3. INISIALISASI CONTROLLER ---

// Controller Menu (Pakai Service dari Container)
$menuController = new MenuController(App::get(MenuService::class));

// Controller Order (Manual Wiring untuk Order karena belum full DI di file lain)
// Kita gunakan MenuRepository yang sudah benar untuk OrderService
$menuRepo = App::get(MenuRepositoryInterface::class); 
$orderRepo = new OrderRepository(); 
$orderService = new OrderService($orderRepo, $menuRepo);
$orderController = new OrderController($orderService);

// --- 4. DEFINISI ROUTER ---
$router = new Router();

// Halaman Depan
$router->get('/', function() {
    ApiResponseBuilder::success(null, 'Seduhin API Backend Ready!')->send();
});

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

// Jalankan Router
$router->dispatch();