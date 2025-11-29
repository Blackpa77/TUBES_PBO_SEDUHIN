<?php
// Set Timezone
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;

// Basic error handler
error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
});

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// DI container (manual)
$menuRepo = new MenuRepository();
$orderRepo = new OrderRepository();
$menuService = new MenuService($menuRepo);
$orderService = new OrderService($orderRepo, $menuRepo);

$menuController = new MenuController($menuService);
$orderController = new OrderController($orderService);

// Router
$router = new Router();
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

$router->post('/orders', [$orderController, 'store']);
$router->get('/orders/:id', [$orderController, 'show']);

// --- RUTE BARU: GET ALL ORDERS ---
$router->get('/orders', [$orderController, 'index']);

$router->dispatch();