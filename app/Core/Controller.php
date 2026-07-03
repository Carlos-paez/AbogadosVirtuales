<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $name, array $data = []): void
    {
        Auth::init();
        $data['isLoggedIn'] = Auth::isLoggedIn();
        $data['currentUser'] = Auth::user();

        $router = $GLOBALS['router'] ?? null;
        $basePath = $router ? $router->getBasePath() : '';

        $title = $data['title'] ?? 'Red de Apoyo Legal';
        $content = '';

        $viewPath = __DIR__ . '/../Views/' . $name . '.php';
        if (file_exists($viewPath)) {
            ob_start();
            extract($data);
            require $viewPath;
            $content = ob_get_clean();
        }

        require __DIR__ . '/../Views/layout.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function getJsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function requireAuth(): void
    {
        Auth::init();
        if (!Auth::isLoggedIn()) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (str_contains($uri, '/api/')) {
                $this->json(['success' => false, 'error' => 'No autorizado. Debe iniciar sesión.'], 401);
            } else {
                $router = $GLOBALS['router'] ?? null;
                $basePath = $router ? $router->getBasePath() : '';
                header('Location: ' . $basePath . '/login');
            }
            exit;
        }
    }
}
