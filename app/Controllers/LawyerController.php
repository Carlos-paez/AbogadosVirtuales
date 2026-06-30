<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lawyer;
use PDOException;

class LawyerController extends Controller
{
    public function register(): void
    {
        $this->view('registro', ['title' => 'Registro de Abogados']);
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
        $estado = trim($input['estado'] ?? '');
        $ciudad = trim($input['ciudad'] ?? '');
        $jurisdiccion = trim($input['jurisdiccion'] ?? '');
        $especialidad = trim($input['especialidad'] ?? '');

        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email válido es obligatorio.';
        if ($estado === '') $errors[] = 'El estado es obligatorio.';
        if ($jurisdiccion === '') $errors[] = 'La jurisdicción es obligatoria.';

        if (!empty($errors)) {
            $this->json(['success' => false, 'error' => implode(' ', $errors)], 400);
            return;
        }

        try {
            $result = Lawyer::create([
                'nombre' => $nombre, 'email' => $email, 'telefono' => $telefono,
                'estado' => $estado, 'ciudad' => $ciudad,
                'jurisdiccion' => $jurisdiccion, 'especialidad' => $especialidad
            ]);
            $this->json($result);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $this->json(['success' => false, 'error' => 'Ya existe un registro con ese email.'], 409);
            } else {
                $this->json(['success' => false, 'error' => 'Error interno del servidor.'], 500);
            }
        }
    }

    public function apiList(): void
    {
        $estado = $_GET['estado'] ?? null;
        $jurisdiccion = $_GET['jurisdiccion'] ?? null;
        $this->json(['success' => true, 'data' => Lawyer::all($estado, $jurisdiccion)]);
    }
}
