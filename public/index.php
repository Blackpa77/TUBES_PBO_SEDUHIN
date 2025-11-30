<?php
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Core\App;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Repositories\AdminRepository; // Baru
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\AdminAuthService;    // Baru
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminAuthController; // Baru
use App\Builders\ApiResponseBuilder;

error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    exit;
});

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- WIRING DEPENDENCY ---
$db = Database::getInstance();

// Auth Module
$adminRepo = new AdminRepository();
$authService = new AdminAuthService($adminRepo);
$authController = new AdminAuthController($authService);

// Menu Module
$menuRepo = new MenuRepository($db);
$menuService = new MenuService($menuRepo);
$menuController = new MenuController($menuService);

// Order Module
$orderRepo = new OrderRepository();
$orderService = new OrderService($orderRepo, $menuRepo);
$orderController = new OrderController($orderService);

// --- ROUTER ---
$router = new Router();

// UI Frontend
$router->get('/demo', function() {
    $file = __DIR__ . '/demo/index.html';
    if (file_exists($file)) { header('Content-Type: text/html'); readfile($file); } 
    else { echo "File UI tidak ditemukan."; }
});

// Auth Routes
$router->post('/auth/login', [$authController, 'login']);

// Menu Routes
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

// Order Routes
$router->get('/orders', [$orderController, 'index']);
$router->get('/orders/:id', [$orderController, 'show']);
$router->post('/orders', [$orderController, 'store']);
$router->put('/orders/:id', [$orderController, 'update']);
$router->delete('/orders/:id', [$orderController, 'destroy']);
$router->get('/orders/:id/download', [$orderController, 'download']); // <--- Fitur Unduh

$router->dispatch();