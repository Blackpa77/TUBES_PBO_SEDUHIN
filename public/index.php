<?php
// Set Timezone
date_default_timezone_set('Asia/Jakarta');

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
use App\Builders\ApiResponseBuilder;

// Error Handling
error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    exit;
});

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- MANUAL WIRING (PASTI JALAN) ---

// 1. Database
$db = Database::getInstance();

// 2. Menu (Repo -> Service -> Controller)
// Kita bypass Interface binding biar tidak ribet, langsung inject object
$menuRepo = new MenuRepository($db);
$menuService = new MenuService($menuRepo);
$menuController = new MenuController($menuService);

// 3. Order
$orderRepo = new OrderRepository();
$orderService = new OrderService($orderRepo, $menuRepo);
$orderController = new OrderController($orderService);

// --- ROUTER ---
$router = new Router();

// Demo UI Route
$router->get('/demo', function() {
    $file = __DIR__ . '/demo/index.html';
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
    } else {
        echo "File UI tidak ditemukan.";
    }
});

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

$router->dispatch();