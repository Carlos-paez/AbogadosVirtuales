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
        $rawAyuda = $input['tipo_ayuda'] ?? '';
        $tipoAyuda = is_array($rawAyuda) ? implode(', ', $rawAyuda) : trim($rawAyuda);
        $prioridad = trim($input['prioridad'] ?? 'media');
        $descripcion = trim($input['descripcion'] ?? '');

        $fieldErrors = [];
        if ($nombre === '') $fieldErrors['nombre'] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $fieldErrors['email'] = 'Email válido es obligatorio.';
        if ($estado === '') $fieldErrors['estado'] = 'El estado es obligatorio.';
        if ($descripcion === '') $fieldErrors['descripcion'] = 'La descripción es obligatoria.';

        if (!empty($fieldErrors)) {
            $this->json(['success' => false, 'fieldErrors' => $fieldErrors, 'error' => 'Corrige los campos marcados.'], 400);
            return;
        }

        $result = AffectedPerson::create([
            'nombre' => $nombre, 'email' => $email, 'telefono' => $telefono,
            'estado' => $estado, 'ciudad' => $ciudad,
            'tipo_ayuda' => $tipoAyuda, 'prioridad' => $prioridad,
            'descripcion' => $descripcion
        ]);
        $this->json($result);
    }

    public function apiList(): void
    {
        $this->json(['success' => true, 'data' => AffectedPerson::all()]);
    }

    public function apiSearch(): void
    {
        $query = trim($_GET['q'] ?? '');
        if ($query === '') {
            $this->json(['success' => true, 'data' => []]);
            return;
        }
        $this->json(['success' => true, 'data' => AffectedPerson::search($query)]);
    }
}
