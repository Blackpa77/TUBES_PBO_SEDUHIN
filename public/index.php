<?php
// 1. Set Timezone (Wajib untuk Indonesia)
date_default_timezone_set('Asia/Jakarta');

// 2. Load Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Import Semua Class Penting
use App\Core\Router;
use App\Core\Database;
use App\Core\App;
use App\Repositories\MenuRepositoryInterface;
use App\Repositories\MenuRepository;
use App\Repositories\OrderRepository;
use App\Repositories\AdminRepository;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\AdminAuthService;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\AdminAuthController;
use App\Controllers\MemberController;
use App\Builders\ApiResponseBuilder;

// 3. Error Handling (Agar error terlihat jelas dalam format JSON)
error_reporting(E_ALL);
set_exception_handler(function($e){
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});

// 4. CORS Headers (Penting agar bisa diakses dari browser)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- 5. WIRING (PERAKITAN) ---
// Kita rakit manual agar tidak ada error "Class Not Found" di Container

// Database
$db = Database::getInstance();

// Auth Module (Login Admin)
$adminRepo = new AdminRepository();
$authService = new AdminAuthService($adminRepo);
$authController = new AdminAuthController($authService);

// Menu Module (Pakai Factory & Interface)
$menuRepo = new MenuRepository($db);
$menuService = new MenuService($menuRepo);
$menuController = new MenuController($menuService);

// Order Module (Pakai Log Manual & Factory)
$orderRepo = new OrderRepository();
// Kita inject MenuRepo ke OrderService untuk cek stok
$orderService = new OrderService($orderRepo, $menuRepo);
$orderController = new OrderController($orderService);

// --- 6. DEFINISI ROUTER ---
$router = new Router();

// A. Rute Halaman Utama (Root)
// Jika user membuka /public/, langsung lempar ke /public/demo
$router->get('/', function() {
    header('Location: /public/demo'); 
    exit;
});

// B. Rute UI Demo (Kasir)
$router->get('/demo', function() {
    $file = __DIR__ . '/demo/index.html';
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
        exit;
    } else {
        echo "<h1>Error 404</h1><p>File UI Kasir (index.html) tidak ditemukan di folder public/demo.</p>";
    }
});

// C. API Authentication
$router->post('/auth/login', [$authController, 'login']);

// D. API Menu (CRUD)
$router->get('/menus', [$menuController, 'index']);
$router->get('/menus/:id', [$menuController, 'show']);
$router->post('/menus', [$menuController, 'store']);
$router->put('/menus/:id', [$menuController, 'update']);
$router->delete('/menus/:id', [$menuController, 'destroy']);

// E. API Order (Transaksi)
$router->get('/orders', [$orderController, 'index']);        // Lihat Riwayat
$router->get('/orders/:id', [$orderController, 'show']);    // Lihat Detail
$router->post('/orders', [$orderController, 'store']);      // Buat Pesanan
$router->put('/orders/:id', [$orderController, 'update']);  // Update Status
$router->delete('/orders/:id', [$orderController, 'destroy']); // Hapus Transaksi
$router->get('/orders/:id/download', [$orderController, 'download']); // Cetak Struk

// --- 7. JALANKAN APLIKASI ---
$router->dispatch();