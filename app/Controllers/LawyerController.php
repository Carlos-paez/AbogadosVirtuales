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
        $tipoDocumento = trim($input['tipo_documento'] ?? 'V');
        $numeroDocumento = trim($input['numero_documento'] ?? '');
        $estado = trim($input['estado'] ?? '');
        $ciudad = trim($input['ciudad'] ?? '');
        $jurisdiccion = trim($input['jurisdiccion'] ?? '');
        $especialidad = trim($input['especialidad'] ?? '');
        $aniosExperiencia = (int)($input['anios_experiencia'] ?? 0);

        $fieldErrors = [];
        if ($nombre === '') $fieldErrors['nombre'] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $fieldErrors['email'] = 'Email válido es obligatorio.';
        if ($estado === '') $fieldErrors['estado'] = 'El estado es obligatorio.';
        if ($jurisdiccion === '') $fieldErrors['jurisdiccion'] = 'La jurisdicción es obligatoria.';

        if (!empty($fieldErrors)) {
            $this->json(['success' => false, 'fieldErrors' => $fieldErrors, 'error' => 'Corrige los campos marcados.'], 400);
            return;
        }

        try {
            $result = Lawyer::create([
                'nombre' => $nombre, 'email' => $email, 'telefono' => $telefono,
                'tipo_documento' => $tipoDocumento, 'numero_documento' => $numeroDocumento,
                'estado' => $estado, 'ciudad' => $ciudad,
                'jurisdiccion' => $jurisdiccion, 'especialidad' => $especialidad,
                'anios_experiencia' => $aniosExperiencia
            ]);
            $this->json($result);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $this->json(['success' => false, 'fieldErrors' => ['email' => 'Ya existe un registro con ese email.'], 'error' => 'Email ya registrado.'], 409);
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

    public function apiSearch(): void
    {
        $query = trim($_GET['q'] ?? '');
        if ($query === '') {
            $this->json(['success' => true, 'data' => []]);
            return;
        }
        $this->json(['success' => true, 'data' => Lawyer::search($query)]);
    }

    public function apiExport(): void
    {
        $estado = $_GET['estado'] ?? null;
        $jurisdiccion = $_GET['jurisdiccion'] ?? null;
        $csv = Lawyer::exportCsv($estado, $jurisdiccion);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="abogados.csv"');
        echo "\xEF\xBB\xBF" . $csv;
    }
}
