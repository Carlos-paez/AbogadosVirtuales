<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LegalCase;
use App\Models\Lawyer;
use App\Models\AffectedPerson;

class CrmController extends Controller
{
    public function index(): void
    {
        $this->view('crm', ['title' => 'CRM - Gestión de Casos']);
    }

    public function apiAssign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $person_id = (int)($input['person_id'] ?? 0);
        $lawyer_id = (int)($input['lawyer_id'] ?? 0);
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');

        if (!$person_id || !$lawyer_id) {
            $this->json(['success' => false, 'error' => 'Debe seleccionar una persona y un abogado.'], 400);
            return;
        }

        $result = LegalCase::create([
            'lawyer_id' => $lawyer_id, 'person_id' => $person_id,
            'titulo' => $titulo, 'descripcion' => $descripcion
        ]);
        $this->json($result);
    }

    public function apiClose(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de caso no válido.'], 400);
            return;
        }

        LegalCase::close($id);
        $this->json(['success' => true]);
    }

    public function apiDelete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de caso no válido.'], 400);
            return;
        }

        LegalCase::delete($id);
        $this->json(['success' => true]);
    }

    public function apiList(): void
    {
        $this->json(['success' => true, 'data' => LegalCase::all()]);
    }

    public function apiStats(): void
    {
        $this->json(['success' => true, 'data' => LegalCase::stats()]);
    }
}
