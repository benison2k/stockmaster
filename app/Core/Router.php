<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => trim($path, '/'),
            'controller' => $controller,
            'action'     => $action
        ];
    }

    public function dispatch(string $url): void {
        $url = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $url) {
                $controllerClass = "App\\Controllers\\" . $route['controller'];
                $actionMethod = $route['action'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $actionMethod)) {
                        $controller->$actionMethod();
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        require_once ROOT_DIR . '/app/Views/home/landing.php';
    }
}