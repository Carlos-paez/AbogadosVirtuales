<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Area;
use App\Models\LegalCase;
use App\Models\AffectedPerson;
use App\Models\CasoAsignacion;
use PDOException;

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole('administrador');
        $this->view('admin', ['title' => 'Panel Administrativo']);
    }

    public function apiUsuarios(): void
    {
        $this->requireRole('administrador');
        $rol = $_GET['rol'] ?? null;
        $search = $_GET['q'] ?? null;
        $users = User::all($rol, $search);
        foreach ($users as &$u) {
            unset($u['password_hash']);
        }
        $this->json(['success' => true, 'data' => $users]);
    }

    public function apiActualizarUsuario(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de usuario requerido.'], 400);
            return;
        }

        $user = User::findById($id);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado.'], 404);
            return;
        }

        $data = [];
        if (isset($input['nombre'])) $data['nombre'] = trim($input['nombre']);
        if (isset($input['email'])) $data['email'] = trim($input['email']);
        if (isset($input['telefono'])) $data['telefono'] = trim($input['telefono']);
        if (isset($input['rol']) && in_array($input['rol'], User::ROLES)) $data['rol'] = $input['rol'];
        if (isset($input['area_id'])) $data['area_id'] = (int)$input['area_id'] ?: null;
        if (isset($input['activo'])) $data['activo'] = (int)$input['activo'];

        if (isset($data['nombre']) && $data['nombre'] === '') {
            $this->json(['success' => false, 'error' => 'El nombre es obligatorio.'], 400);
            return;
        }
        if (isset($data['email']) && ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            $this->json(['success' => false, 'error' => 'Email válido es obligatorio.'], 400);
            return;
        }

        try {
            User::update($id, $data);
            $updated = User::findById($id);
            unset($updated['password_hash']);
            $this->json(['success' => true, 'data' => $updated]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $this->json(['success' => false, 'error' => 'Ya existe otro usuario con ese email.'], 409);
            } else {
                $this->json(['success' => false, 'error' => 'Error interno del servidor.'], 500);
            }
        }
    }

    public function apiEliminarUsuario(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de usuario requerido.'], 400);
            return;
        }
        if ($id === Auth::user()['id']) {
            $this->json(['success' => false, 'error' => 'No puede eliminarse a sí mismo.'], 400);
            return;
        }

        User::delete($id);
        $this->json(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
    }

    public function apiAreas(): void
    {
        $this->requireRole('administrador');
        $this->json(['success' => true, 'data' => Area::all()]);
    }

    public function apiCrearArea(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');

        if ($nombre === '') {
            $this->json(['success' => false, 'error' => 'El nombre del área es obligatorio.'], 400);
            return;
        }

        try {
            $result = Area::create(['nombre' => $nombre, 'descripcion' => $descripcion]);
            $this->json($result);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $this->json(['success' => false, 'error' => 'Ya existe un área con ese nombre.'], 409);
            } else {
                $this->json(['success' => false, 'error' => 'Error interno del servidor.'], 500);
            }
        }
    }

    public function apiActualizarArea(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de área requerido.'], 400);
            return;
        }

        $data = [];
        if (isset($input['nombre'])) $data['nombre'] = trim($input['nombre']);
        if (isset($input['descripcion'])) $data['descripcion'] = trim($input['descripcion']);
        if (isset($input['activo'])) $data['activo'] = (int)$input['activo'];

        try {
            Area::update($id, $data);
            $this->json(['success' => true, 'message' => 'Área actualizada.']);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $this->json(['success' => false, 'error' => 'Ya existe un área con ese nombre.'], 409);
            } else {
                $this->json(['success' => false, 'error' => 'Error interno del servidor.'], 500);
            }
        }
    }

    public function apiEliminarArea(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $id = (int)($input['id'] ?? 0);

        if (!$id) {
            $this->json(['success' => false, 'error' => 'ID de área requerido.'], 400);
            return;
        }

        $userCount = Area::countUsers($id);

        if ($userCount > 0) {
            $this->json(['success' => false, 'error' => "No se puede eliminar: $userCount usuario(s) asignado(s) a esta área."], 400);
            return;
        }

        Area::delete($id);
        $this->json(['success' => true, 'message' => 'Área eliminada.']);
    }

    public function apiCrearCaso(): void
    {
        $this->requireRole('administrador');
        $input = $this->getJsonInput();
        $personId = (int)($input['person_id'] ?? 0);
        $titulo = trim($input['titulo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $prioridad = trim($input['prioridad'] ?? 'media');
        $areaId = (int)($input['area_id'] ?? 0);

        $fieldErrors = [];
        if (!$personId) $fieldErrors['person_id'] = 'Debe seleccionar una persona.';
        if (!$areaId) $fieldErrors['area_id'] = 'Debe seleccionar un área.';
        if (!empty($fieldErrors)) {
            $this->json(['success' => false, 'fieldErrors' => $fieldErrors, 'error' => 'Corrige los campos marcados.'], 400);
            return;
        }

        try {
            $result = LegalCase::create([
                'person_id' => $personId,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'prioridad' => $prioridad,
                'area_id' => $areaId,
                'usuario_creador_id' => Auth::user()['id'],
            ]);
            $this->json($result);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'error' => 'Error al crear el caso.'], 500);
        }
    }

    public function apiAsignaciones(): void
    {
        $this->requireRole('administrador');
        $this->json(['success' => true, 'data' => CasoAsignacion::todas()]);
    }

    public function apiEstadisticas(): void
    {
        $this->requireRole('administrador');
        $this->json([
            'success' => true,
            'data' => [
                'total_usuarios' => User::count(),
                'total_areas' => Area::count(),
                'total_casos' => LegalCase::count(),
                'total_asignaciones' => CasoAsignacion::count(),
            ]
        ]);
    }
}
