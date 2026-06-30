<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('\\', '/', $basePath);
        $basePath = rtrim($basePath, '/');
        $path = str_replace($basePath, '', $path);
        $path = '/' . trim($path, '/');

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            $title = 'Página no encontrada';
            $content = '<div class="error-404"><h1>404</h1><p>La página que buscas no existe.</p><a href="' . $basePath . '/" class="btn btn-primary">Volver al inicio</a></div>';
            require __DIR__ . '/../Views/layout.php';
            return;
        }

        [$controllerClass, $action] = explode('@', $handler);
        $controllerClass = 'App\\Controllers\\' . $controllerClass;
        $controller = new $controllerClass();
        $controller->$action();
    }

    public function getBasePath(): string
    {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('\\', '/', $basePath);
        return rtrim($basePath, '/');
    }
}
