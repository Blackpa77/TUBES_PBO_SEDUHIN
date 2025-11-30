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

// --- MANUAL DEPENDENCY INJECTION (WIRING) ---
$database = Database::getInstance();

$menuRepo = new MenuRepository($database);
$menuService = new MenuService($menuRepo);
$menuController = new MenuController($menuService);

$orderRepo = new OrderRepository(); 
$orderService = new OrderService($orderRepo, $menuRepo);
$orderController = new OrderController($orderService);

// ---------------------------------------------

$router = new Router();

// --- RUTE BARU: HALAMAN UTAMA (ROOT) ---
$router->get('/', function() {
    ApiResponseBuilder::success(null, 'Welcome to Seduhin API! Server is Running.')->send();
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

$router->dispatch();