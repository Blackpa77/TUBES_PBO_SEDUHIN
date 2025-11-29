<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, $handler): void { $this->add('POST', $path, $handler); }
    public function put(string $path, $handler): void { $this->add('PUT', $path, $handler); }
    public function delete(string $path, $handler): void { $this->add('DELETE', $path, $handler); }

    private function add(string $method, string $path, $handler): void { $this->routes[] = compact('method','path','handler'); }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // strip /api base if using
        // match route
        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            $pattern = $this->toRegex($r['path']);
            if (preg_match($pattern, $uri, $m)) {
                array_shift($m);
                call_user_func_array($r['handler'], array_map(function($v){ return is_numeric($v)?(int)$v:$v; }, $m));
                return;
            }
        }
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Not found']);
    }

    private function toRegex(string $p): string
    {
        $pattern = preg_replace('#/:([a-zA-Z_][a-zA-Z0-9_-]*)#', '/([0-9a-zA-Z_-]+)', $p);
        return '#^' . $pattern . '$#';
    }
}
