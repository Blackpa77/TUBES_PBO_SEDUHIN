<?php
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;

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

$menuRepo = new MenuRepository();
$orderRepo = new OrderRepository();
$menuService = new MenuService($menuRepo);
$orderService = new OrderService($orderRepo, $menuRepo);

$menuController = new MenuController($menuService);
$orderController = new OrderController($orderService);

$router = new Router();

// Routes Menu
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

// Routes Order
$router->get('/orders', [$orderController, 'index']);       // Get All
$router->get('/orders/:id', [$orderController, 'show']);   // Get One
$router->post('/orders', [$orderController, 'store']);     // Create
$router->put('/orders/:id', [$orderController, 'update']); // Update (Status/Nama)
$router->delete('/orders/:id', [$orderController, 'destroy']); // Delete

$router->dispatch();