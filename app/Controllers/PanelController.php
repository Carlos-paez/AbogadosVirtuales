<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\LegalCase;
use App\Models\CasoAsignacion;
use App\Models\Area;

class PanelController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $areaId = $user['area_id'] ?? null;
        $area = $areaId ? Area::findById($areaId) : null;
        $this->view('panel', [
            'title' => 'Mi Panel',
            'area' => $area,
            'currentUser' => $user,
        ]);
    }

    public function apiCasosDisponibles(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        $areaId = $user['area_id'] ?? null;

        if (!$areaId) {
            $this->json(['success' => false, 'error' => 'No tiene un área de desempeño asignada.'], 400);
            return;
        }

        $search = $_GET['q'] ?? null;
        $casos = LegalCase::availableByArea($areaId, $search);
        $this->json(['success' => true, 'data' => $casos]);
    }

    public function apiMisCasos(): void
    {
        $this->requireAuth();
        $userId = Auth::user()['id'];
        $casos = CasoAsignacion::porUsuario($userId);
        $this->json(['success' => true, 'data' => $casos]);
    }

    public function apiSeleccionarCaso(): void
    {
        $this->requireAuth();
        $input = $this->getJsonInput();
        $casoId = (int)($input['caso_id'] ?? 0);
        $userId = Auth::user()['id'];
        $userAreaId = Auth::user()['area_id'] ?? null;

        if (!$casoId) {
            $this->json(['success' => false, 'error' => 'ID de caso requerido.'], 400);
            return;
        }

        if (!$userAreaId) {
            $this->json(['success' => false, 'error' => 'No tiene un área de desempeño asignada.'], 400);
            return;
        }

        $case = LegalCase::get($casoId);
        if (!$case) {
            $this->json(['success' => false, 'error' => 'El caso no existe.'], 404);
            return;
        }

        if (($case['area_id'] ?? null) != $userAreaId) {
            $this->json(['success' => false, 'error' => 'No está autorizado para seleccionar casos de otra área.'], 403);
            return;
        }

        if (in_array($case['estado'] ?? '', ['cerrado', 'resuelto'])) {
            $this->json(['success' => false, 'error' => 'Este caso ya está cerrado o resuelto.'], 400);
            return;
        }

        $existing = CasoAsignacion::existeAsignacion($casoId);
        if ($existing) {
            $this->json(['success' => false, 'error' => 'Este caso ya fue seleccionado por otro usuario.'], 409);
            return;
        }

        $result = CasoAsignacion::asignar($casoId, $userId);
        if ($result['success']) {
            $this->json(['success' => true, 'message' => 'Caso seleccionado exitosamente.']);
        } else {
            $this->json(['success' => false, 'error' => $result['error']], 409);
        }
    }

    public function apiPerfil(): void
    {
        $this->requireAuth();
        $userId = Auth::user()['id'];
        $user = \App\Models\User::findById($userId);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'Usuario no encontrado.'], 404);
            return;
        }
        unset($user['password_hash']);
        $user['area_nombre'] = $user['area_id'] ? (Area::findById($user['area_id'])['nombre'] ?? '') : '';
        $this->json(['success' => true, 'data' => $user]);
    }
}
