<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LegalCase;
use PDOException;

class CrmController extends Controller
{
    public function index(): void
    {
        $this->requireRole('administrador');
        $this->view('crm', ['title' => 'CRM - Gestión de Casos']);
    }

    public function apiAssign(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $person_id = (int)($input['person_id'] ?? 0);
        $lawyer_id = (int)($input['lawyer_id'] ?? 0);
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $prioridad = trim($input['prioridad'] ?? 'media');
        $areaId = (int)($input['area_id'] ?? 0);

        if (!$person_id || !$lawyer_id) {
            $this->json(['success' => false, 'error' => 'Debe seleccionar una persona y un abogado.'], 400);
            return;
        }

        try {
            $result = LegalCase::create([
                'lawyer_id' => $lawyer_id, 'person_id' => $person_id,
                'titulo' => $titulo, 'descripcion' => $descripcion,
                'prioridad' => $prioridad, 'area_id' => $areaId ?: null,
                'usuario_creador_id' => \App\Core\Auth::user()['id'],
            ]);
            $this->json($result);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'FOREIGN KEY')) {
                $this->json(['success' => false, 'error' => 'La persona o el abogado seleccionado no existe.'], 400);
            } else {
                $this->json(['success' => false, 'error' => 'Error al asignar el caso.'], 500);
            }
        }
    }

    public function apiGet(): void
    {
        $this->requireRole('administrador');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID no válido.'], 400);
            return;
        }
        $case = LegalCase::get($id);
        if (!$case) {
            $this->json(['success' => false, 'error' => 'Caso no encontrado.'], 404);
            return;
        }
        $case['actividades'] = LegalCase::getActivities($id);
        $this->json(['success' => true, 'data' => $case]);
    }

    public function apiUpdate(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID no válido.'], 400);
            return;
        }

        $update = [];
        if (isset($input['titulo'])) $update['titulo'] = trim($input['titulo']);
        if (isset($input['descripcion'])) $update['descripcion'] = trim($input['descripcion']);
        if (isset($input['prioridad'])) $update['prioridad'] = trim($input['prioridad']);
        if (isset($input['lawyer_id'])) $update['lawyer_id'] = (int)$input['lawyer_id'];
        if (isset($input['notas'])) $update['notas'] = trim($input['notas']);

        if (empty($update)) {
            $this->json(['success' => false, 'error' => 'No hay campos para actualizar.'], 400);
            return;
        }

        LegalCase::update($id, $update);
        $this->json(['success' => true, 'message' => 'Caso actualizado.']);
    }

    public function apiChangeStatus(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);
        $newStatus = trim($input['estado'] ?? '');
        $observacion = trim($input['observacion'] ?? '');

        if (!$id || !$newStatus) {
            $this->json(['success' => false, 'error' => 'ID y estado requeridos.'], 400);
            return;
        }

        $ok = LegalCase::updateStatus($id, $newStatus, $observacion ?: null);
        if ($ok) {
            $this->json(['success' => true, 'message' => "Estado cambiado a '$newStatus'."]);
        } else {
            $this->json(['success' => false, 'error' => 'No se pudo cambiar el estado. Verifique que el nuevo estado sea válido.'], 400);
        }
    }

    public function apiAddComment(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);
        $comment = trim($input['comentario'] ?? '');

        if (!$id || !$comment) {
            $this->json(['success' => false, 'error' => 'ID y comentario requeridos.'], 400);
            return;
        }

        LegalCase::addComment($id, $comment);
        $this->json(['success' => true, 'message' => 'Comentario agregado.']);
    }

    public function apiActivities(): void
    {
        $this->requireRole('administrador');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID no válido.'], 400);
            return;
        }
        $this->json(['success' => true, 'data' => LegalCase::getActivities($id)]);
    }

    public function apiClose(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);
        $observaciones = trim($input['observaciones'] ?? '');

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de caso no válido.'], 400);
            return;
        }

        LegalCase::close($id, $observaciones ?: null);
        $this->json(['success' => true, 'message' => 'Caso cerrado exitosamente.']);
    }

    public function apiReopen(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de caso no válido.'], 400);
            return;
        }

        $ok = LegalCase::reopen($id);
        if ($ok) {
            $this->json(['success' => true, 'message' => 'Caso reabierto exitosamente.']);
        } else {
            $this->json(['success' => false, 'error' => 'No se pudo reabrir. El caso no existe o ya está abierto.'], 400);
        }
    }

    public function apiDelete(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de caso no válido.'], 400);
            return;
        }

        LegalCase::delete($id);
        $this->json(['success' => true, 'message' => 'Caso eliminado.']);
    }

    public function apiList(): void
    {
        $this->requireRole('administrador');
        $estado = $_GET['estado'] ?? null;
        $search = $_GET['q'] ?? null;
        $prioridad = $_GET['prioridad'] ?? null;
        $this->json(['success' => true, 'data' => LegalCase::all($estado, $search, $prioridad)]);
    }

    public function apiStats(): void
    {
        $this->requireRole('administrador');
        $this->json(['success' => true, 'data' => LegalCase::stats()]);
    }

    public function apiExport(): void
    {
        $this->requireRole('administrador');
        $estado = $_GET['estado'] ?? null;
        $prioridad = $_GET['prioridad'] ?? null;
        $csv = LegalCase::exportCsv($estado, $prioridad);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="casos.csv"');
        echo "\xEF\xBB\xBF" . $csv;
    }
}
