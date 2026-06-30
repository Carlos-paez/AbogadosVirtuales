<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AffectedPerson;

class RequestController extends Controller
{
    public function form(): void
    {
        $this->view('solicitudes', ['title' => 'Solicitar Apoyo Legal']);
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
        $descripcion = trim($input['descripcion'] ?? '');

        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email válido es obligatorio.';
        if ($estado === '') $errors[] = 'El estado es obligatorio.';
        if ($descripcion === '') $errors[] = 'La descripción es obligatoria.';

        if (!empty($errors)) {
            $this->json(['success' => false, 'error' => implode(' ', $errors)], 400);
            return;
        }

        $result = AffectedPerson::create([
            'nombre' => $nombre, 'email' => $email, 'telefono' => $telefono,
            'estado' => $estado, 'ciudad' => $ciudad, 'descripcion' => $descripcion
        ]);
        $this->json($result);
    }

    public function apiList(): void
    {
        $this->json(['success' => true, 'data' => AffectedPerson::all()]);
    }
}
