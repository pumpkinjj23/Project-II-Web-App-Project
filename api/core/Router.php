<?php
/**
 * Router.php - RESTful API Router Engine
 * รองรับทั้ง Friendly URLs (mod_rewrite) และ Fallback Query String (?route=...)
 */

require_once __DIR__ . '/Response.php';

class Router {
    private $routes = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        // แปลง {id} เป็น regex named group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . trim($pattern, '/') . '$#i';

        $this->routes[] = [
            'method'   => strtoupper($method),
            'pattern'  => $pattern,
            'original' => $path,
            'handler'  => $handler
        ];
    }

    public function dispatch($method, $uri) {
        // จัดการกรณี CORS Preflight
        if ($method === 'OPTIONS') {
            Response::json(200, null, 'CORS Preflight OK', true);
            return;
        }

        // Method Spoofing รองรับการส่ง _method ใน POST
        if ($method === 'POST') {
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }

        $path = parse_url($uri, PHP_URL_PATH);

        // ตัด base path ออก
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName); // e.g. /CPE66/Project-II  Web App Project Full/api

        if ($baseDir !== '/' && $baseDir !== '\\' && strpos($path, $baseDir) === 0) {
            $path = substr($path, strlen($baseDir));
        }

        $path = trim($path, '/');

        // ตัด prefix index.php/ ถ้ามี
        if (strpos($path, 'index.php/') === 0) {
            $path = substr($path, 10);
        } elseif ($path === 'index.php') {
            $path = '';
        }

        // ตัด prefix 'v1/' หรือ 'api/v1/' ออกถ้ามี
        if (strpos($path, 'api/v1/') === 0) {
            $path = substr($path, 7);
        } elseif (strpos($path, 'api/') === 0) {
            $path = substr($path, 4);
        } elseif (strpos($path, 'v1/') === 0) {
            $path = substr($path, 3);
        }

        if (strpos($path, 'index.php/') === 0) {
            $path = substr($path, 10);
        } elseif ($path === 'index.php') {
            $path = '';
        }

        // Fallback: หากมีการส่งผ่าน ?route=... หรือ ?endpoint=...
        if (isset($_GET['route']) && !empty($_GET['route'])) {
            $path = trim($_GET['route'], '/');
            if (strpos($path, 'api/v1/') === 0) $path = substr($path, 7);
            elseif (strpos($path, 'api/') === 0) $path = substr($path, 4);
            elseif (strpos($path, 'v1/') === 0) $path = substr($path, 3);
        } elseif (isset($_GET['endpoint']) && !empty($_GET['endpoint'])) {
            $path = trim($_GET['endpoint'], '/');
        }

        // หาก path ว่าง ให้ default เป็น ping หรือ docs
        if ($path === '') {
            $path = 'ping';
        }

        // 1. ตรวจสอบ Route ที่ตรงทั้ง Method และ Pattern
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                if (preg_match($route['pattern'], $path, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $handler = $route['handler'];

                    if (is_callable($handler)) {
                        call_user_func_array($handler, array_values($params));
                        return;
                    } elseif (is_array($handler) && count($handler) === 2) {
                        list($controller, $action) = $handler;
                        if (is_string($controller)) {
                            $controller = new $controller();
                        }
                        call_user_func_array([$controller, $action], array_values($params));
                        return;
                    }
                }
            }
        }

        // 2. ตรวจสอบว่า Path มีอยู่แต่ใช้ HTTP Method ผิด
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path)) {
                Response::methodNotAllowed("HTTP Method '{$method}' is not allowed for endpoint '/{$path}'");
                return;
            }
        }

        Response::notFound("Endpoint '/{$path}' not found in REST API");
    }
}

