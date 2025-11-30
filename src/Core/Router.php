<?php

namespace App\Core;

use App\Core\App; // Pastikan kita menggunakan App Container

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, $handler): void { $this->add('POST', $path, $handler); }
    public function put(string $path, $handler): void { $this->add('PUT', $path, $handler); }
    public function delete(string $path, $handler): void { $this->add('DELETE', $path, $handler); }

    private function add(string $method, string $path, $handler): void 
    { 
        $this->routes[] = compact('method', 'path', 'handler'); 
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // --- UPDATE PINTAR: Hapus '/public' otomatis ---
        // Ini mengatasi masalah "Not Found" di Laragon atau Apache config tertentu
        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, 7);
        }
        // ----------------------------------------------

        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            
            $pattern = $this->toRegex($r['path']);
            if (preg_match($pattern, $uri, $m)) {
                array_shift($m);
                
                // Bersihkan parameter (ubah string angka jadi integer)
                $params = array_map(function($v){ 
                    return is_numeric($v) ? (int)$v : $v; 
                }, $m);

                // --- LOGIKA BARU: Dependency Injection untuk Controller ---
                if (is_array($r['handler']) && count($r['handler']) === 2) {
                    $controllerName = $r['handler'][0];
                    $actionName = $r['handler'][1];

                    if (class_exists($controllerName)) {
                        try {
                            // MINTA APP MEMBUATKAN CONTROLLER (Bukan pakai 'new')
                            // Ini kuncinya agar Service otomatis masuk ke Controller
                            $controller = App::resolve($controllerName);
                            
                            call_user_func_array([$controller, $actionName], $params);
                            return;
                        } catch (\Exception $e) {
                            // Tangani jika gagal membuat controller (misal lupa bind Service)
                            $this->sendError(500, 'Dependency Injection Error: ' . $e->getMessage());
                            return;
                        }
                    }
                }
                
                // Fallback untuk Closure / Function biasa
                if (is_callable($r['handler'])) {
                    call_user_func_array($r['handler'], $params);
                    return;
                }
                // ---------------------------------------------------------
            }
        }

        $this->sendError(404, 'Route not found', ['debug_uri' => $uri]);
    }

    private function toRegex(string $p): string
    {
        // Ubah parameter :id menjadi regex
        $pattern = preg_replace('#/:([a-zA-Z_][a-zA-Z0-9_-]*)#', '/([0-9a-zA-Z_-]+)', $p);
        return '#^' . $pattern . '$#';
    }

    private function sendError(int $code, string $message, array $extra = []): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => false, 'message' => $message], $extra));
    }
}