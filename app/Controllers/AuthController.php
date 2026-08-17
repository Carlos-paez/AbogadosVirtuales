<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Area;
use PDOException;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::isLoggedIn()) {
            $router = $GLOBALS['router'];
            $user = Auth::user();
            $redirect = ($user['rol'] ?? '') === 'administrador' ? '/crm' : '/panel';
            header('Location: ' . $router->getBasePath() . $redirect);
            exit;
        }
        $this->view('login', ['title' => 'Iniciar Sesión']);
    }

    public function login(): void
    {
        $input = $this->getJsonInput();
        $credencial = trim($input['credencial'] ?? '');
        $password = $input['password'] ?? '';
        $csrfToken = $input['_csrf'] ?? '';

        if (!Auth::validateCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'error' => 'Solicitud inválida. Recargue la página e intente de nuevo.'], 403);
            return;
        }

        if (!$credencial || !$password) {
            $this->json(['success' => false, 'error' => 'Credencial y contraseña requeridos.'], 400);
            return;
        }

        if (User::count() === 0) {
            $cred = User::generateCredential();
            $tempPassword = bin2hex(random_bytes(12));
            User::create([
                'username' => 'admin',
                'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
                'nombre' => 'Administrador',
                'credencial' => $cred,
                'rol' => 'administrador',
            ]);
            $this->json([
                'success' => true,
                'message' => 'Administrador creado.',
                'admin_created' => [
                    'credencial' => $cred,
                    'password' => $tempPassword,
                ],
            ]);
            return;
        }

        if (Auth::isLockedOut() || Auth::isIpLockedOut()) {
            $this->json(['success' => false, 'error' => "Demasiados intentos. Espere antes de intentar de nuevo."], 429);
            return;
        }

        if (Auth::login($credencial, $password)) {
            $user = Auth::user();
            $redirect = ($user['rol'] ?? '') === 'administrador' ? '/crm' : '/panel';
            $this->json(['success' => true, 'message' => 'Inicio de sesión exitoso.', 'redirect' => $redirect]);
        } else {
            $remaining = Auth::getRemainingAttempts();
            $msg = 'Credencial o contraseña incorrectos.';
            if ($remaining > 0) {
                $msg .= " Intentos restantes: $remaining.";
            }
            $this->json(['success' => false, 'error' => $msg], 401);
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

        if (strlen($newPassword) < 6) {
            $this->json(['success' => false, 'error' => 'La nueva contraseña debe tener al menos 6 caracteres.'], 400);
            return;
        }

        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            $this->json(['success' => false, 'error' => 'La contraseña debe contener al menos una letra mayúscula y un número.'], 400);
            return;
        }

        $user = User::findById(Auth::user()['id']);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $this->json(['success' => false, 'error' => 'La contraseña actual es incorrecta.'], 401);
            return;
        }

        User::updatePassword($user['id'], password_hash($newPassword, PASSWORD_BCRYPT));
        $this->json(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }

    public function registerForm(): void
    {
        if (Auth::isLoggedIn()) {
            $router = $GLOBALS['router'];
            header('Location: ' . $router->getBasePath() . '/panel');
            exit;
        }
        $areas = Area::active();
        $this->view('registro', ['title' => 'Registro', 'areas' => $areas]);
    }

    public function apiRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $nombre = trim($input['nombre'] ?? '');
        $email = trim($input['email'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $tipoDocumento = trim($input['tipo_documento'] ?? 'V');
        $numeroDocumento = trim($input['numero_documento'] ?? '');
        $pais = trim($input['pais'] ?? 'Venezuela');
        $estado = trim($input['estado'] ?? '');
        $ciudad = trim($input['ciudad'] ?? '');
        $jurisdiccion = trim($input['jurisdiccion'] ?? '');
        $especialidad = trim($input['especialidad'] ?? '');
        $aniosExperiencia = (int)($input['anios_experiencia'] ?? 0);
        $areaId = (int)($input['area_id'] ?? 0);
        $password = $input['password'] ?? '';
        $passwordConfirm = $input['password_confirm'] ?? '';

        $fieldErrors = [];
        if ($nombre === '') {
            $fieldErrors['nombre'] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > 200) {
            $fieldErrors['nombre'] = 'El nombre no puede exceder 200 caracteres.';
        } elseif (preg_match('/[<>]/', $nombre)) {
            $fieldErrors['nombre'] = 'El nombre contiene caracteres no permitidos.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fieldErrors['email'] = 'Email válido es obligatorio.';
        } elseif (mb_strlen($email) > 254) {
            $fieldErrors['email'] = 'El email no puede exceder 254 caracteres.';
        }

        if ($numeroDocumento === '') {
            $fieldErrors['numero_documento'] = 'El número de documento es obligatorio.';
        } elseif (mb_strlen($numeroDocumento) > 20) {
            $fieldErrors['numero_documento'] = 'El número de documento no puede exceder 20 caracteres.';
        } elseif (!preg_match('/^[A-Za-z0-9.\-]+$/', $numeroDocumento)) {
            $fieldErrors['numero_documento'] = 'El número de documento solo puede contener letras, números, puntos y guiones.';
        }

        if ($areaId === 0) {
            $fieldErrors['area_id'] = 'Debe seleccionar un área de desempeño.';
        }

        if ($jurisdiccion === '') {
            $fieldErrors['jurisdiccion'] = 'La jurisdicción es obligatoria.';
        } elseif (mb_strlen($jurisdiccion) > 200) {
            $fieldErrors['jurisdiccion'] = 'La jurisdicción no puede exceder 200 caracteres.';
        }

        if (mb_strlen($telefono) > 20) {
            $fieldErrors['telefono'] = 'El teléfono no puede exceder 20 caracteres.';
        }

        if (mb_strlen($ciudad) > 100) {
            $fieldErrors['ciudad'] = 'La ciudad no puede exceder 100 caracteres.';
        }

        if (mb_strlen($especialidad) > 200) {
            $fieldErrors['especialidad'] = 'La especialidad no puede exceder 200 caracteres.';
        }

        if ($password === '') {
            $fieldErrors['password'] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 6) {
            $fieldErrors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif (strlen($password) > 128) {
            $fieldErrors['password'] = 'La contraseña no puede exceder 128 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $fieldErrors['password'] = 'Debe contener al menos una mayúscula y un número.';
        }

        if ($passwordConfirm !== $password) {
            $fieldErrors['password_confirm'] = 'Las contraseñas no coinciden.';
        }

        if (!empty($fieldErrors)) {
            $this->json(['success' => false, 'fieldErrors' => $fieldErrors, 'error' => 'Corrige los campos marcados.'], 400);
            return;
        }

        $existingEmail = User::findByEmail($email);
        if ($existingEmail) {
            $this->json(['success' => false, 'fieldErrors' => ['email' => 'Ya existe un registro con ese email.'], 'error' => 'Email ya registrado.'], 409);
            return;
        }

        if (User::existsDocumento($numeroDocumento)) {
            $this->json(['success' => false, 'fieldErrors' => ['numero_documento' => 'Ya existe un registro con ese número de documento.'], 'error' => 'Documento ya registrado.'], 409);
            return;
        }

        $area = \App\Models\Area::findById($areaId);
        if (!$area) {
            $this->json(['success' => false, 'fieldErrors' => ['area_id' => 'El área seleccionada no es válida.'], 'error' => 'Área inválida.'], 400);
            return;
        }

        try {
            $credencial = User::generateCredential();
            $username = 'usr_' . strtolower(bin2hex(random_bytes(4)));

            $result = User::create([
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'nombre' => $nombre,
                'credencial' => $credencial,
                'email' => $email,
                'telefono' => $telefono,
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $numeroDocumento,
                'pais' => $pais,
                'estado' => $estado,
                'ciudad' => $ciudad,
                'jurisdiccion' => $jurisdiccion,
                'especialidad' => $especialidad,
                'anios_experiencia' => $aniosExperiencia,
                'area_id' => $areaId,
                'rol' => 'usuario',
            ]);

            $this->json([
                'success' => true,
                'message' => 'Registro exitoso.',
                'credencial' => $credencial,
                'area_nombre' => $area['nombre'],
            ]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'error' => 'Error interno del servidor.'], 500);
        }
    }

    public function apiGetAreas(): void
    {
        $this->json(['success' => true, 'data' => \App\Models\Area::active()]);
    }
}
