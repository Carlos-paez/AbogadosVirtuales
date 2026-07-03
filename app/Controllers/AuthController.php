<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::isLoggedIn()) {
            $router = $GLOBALS['router'];
            header('Location: ' . $router->getBasePath() . '/crm');
            exit;
        }
        $this->view('login', ['title' => 'Iniciar Sesión']);
    }

    public function login(): void
    {
        $input = $this->getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            $this->json(['success' => false, 'error' => 'Usuario y contraseña requeridos.'], 400);
            return;
        }

        if (User::count() === 0) {
            User::create([
                'username' => 'admin',
                'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
                'nombre' => 'Administrador'
            ]);
        }

        if (Auth::login($username, $password)) {
            $this->json(['success' => true, 'message' => 'Inicio de sesión exitoso.']);
        } else {
            $this->json(['success' => false, 'error' => 'Usuario o contraseña incorrectos.'], 401);
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $router = $GLOBALS['router'];
        header('Location: ' . $router->getBasePath() . '/');
        exit;
    }
}
