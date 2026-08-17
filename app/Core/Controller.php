<?php

namespace App\Core;

abstract class Controller
{
    protected function sendSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'");
    }

    protected function view(string $name, array $data = []): void
    {
        Auth::init();
        $this->sendSecurityHeaders();
        $data['isLoggedIn'] = Auth::isLoggedIn();
        $data['currentUser'] = Auth::user();
        $data['csrf_token'] = Auth::generateCsrfToken();

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
        $this->sendSecurityHeaders();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function getJsonInput(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function requireAuth(): void
    {
        Auth::init();
        if (Auth::checkSessionTimeout()) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (str_contains($uri, '/api/')) {
                $this->json(['success' => false, 'error' => 'Sesión expirada. Debe iniciar sesión nuevamente.'], 401);
            } else {
                $router = $GLOBALS['router'] ?? null;
                $basePath = $router ? $router->getBasePath() : '';
                header('Location: ' . $basePath . '/login');
            }
            exit;
        }
        Auth::refreshSessionUser();
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

    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        $user = Auth::user();
        if (($user['rol'] ?? '') !== $role) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (str_contains($uri, '/api/')) {
                $this->json(['success' => false, 'error' => 'No tiene permisos para acceder a este recurso.'], 403);
            } else {
                $router = $GLOBALS['router'] ?? null;
                $basePath = $router ? $router->getBasePath() : '';
                header('Location: ' . $basePath . '/panel');
            }
            exit;
        }
    }
}
