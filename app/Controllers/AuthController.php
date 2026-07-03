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

    public function apiChangePassword(): void
    {
        $this->requireAuth();
        $input = $this->getJsonInput();
        $current = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (!$current || !$newPassword) {
            $this->json(['success' => false, 'error' => 'Ambas contraseñas son requeridas.'], 400);
            return;
        }

        if (strlen($newPassword) < 4) {
            $this->json(['success' => false, 'error' => 'La nueva contraseña debe tener al menos 4 caracteres.'], 400);
            return;
        }

        $user = \App\Models\User::findById(Auth::user()['id']);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $this->json(['success' => false, 'error' => 'La contraseña actual es incorrecta.'], 401);
            return;
        }

        \App\Models\User::updatePassword($user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        $this->json(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }
}
