<?php

namespace App\Models;

use App\Core\Model;

class LegalCase extends Model
{
    const STATUSES = ['pendiente', 'en_proceso', 'derivado', 'resuelto', 'cerrado'];

    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO cases (lawyer_id, person_id, titulo, descripcion, prioridad, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $estado = $data['estado'] ?? 'pendiente';
        $stmt->execute([
            $data['lawyer_id'], $data['person_id'],
            $data['titulo'] ?? '', $data['descripcion'] ?? '',
            $data['prioridad'] ?? 'media', $estado
        ]);
        $id = $db->lastInsertId();
        self::addActivity($id, 'creado', "Caso creado con prioridad {$data['prioridad']}", null, null, $estado);
        return ['success' => true, 'id' => $id];
    }

    public static function get(int $id): ?array
    {
        $stmt = self::db()->prepare("
            SELECT c.*, l.nombre AS abogado_nombre, l.email AS abogado_email, l.telefono AS abogado_telefono,
                   l.jurisdiccion, l.estado AS abogado_estado,
                   p.nombre AS persona_nombre, p.email AS persona_email, p.telefono AS persona_telefono,
                   p.estado AS persona_estado, p.ciudad AS persona_ciudad, p.tipo_ayuda, p.prioridad AS persona_prioridad,
                   p.descripcion AS persona_descripcion,
                   CAST(julianday('now') - julianday(c.assigned_at) AS INTEGER) AS dias_abierto
            FROM cases c
            LEFT JOIN lawyers l ON c.lawyer_id = l.id
            LEFT JOIN affected_people p ON c.person_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(?string $estado = null, ?string $search = null, ?string $prioridad = null): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if ($estado) {
            $where[] = "c.estado = ?";
            $params[] = $estado;
        }
        if ($prioridad) {
            $where[] = "c.prioridad = ?";
            $params[] = $prioridad;
        }
        if ($search) {
            $q = "%$search%";
            $where[] = "(c.titulo LIKE ? OR l.nombre LIKE ? OR p.nombre LIKE ? OR c.descripcion LIKE ?)";
            $params = array_merge($params, [$q, $q, $q, $q]);
        }

        $sql = "SELECT c.*, l.nombre AS abogado_nombre, l.estado AS abogado_estado,
                       l.jurisdiccion, p.nombre AS persona_nombre, p.estado AS persona_estado,
                       CAST(julianday('now') - julianday(c.assigned_at) AS INTEGER) AS dias_abierto
                FROM cases c
                LEFT JOIN lawyers l ON c.lawyer_id = l.id
                LEFT JOIN affected_people p ON c.person_id = p.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY 
                    CASE c.prioridad WHEN 'urgente' THEN 0 WHEN 'alta' THEN 1 WHEN 'media' THEN 2 WHEN 'baja' THEN 3 END,
                    c.assigned_at ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $changes = [];

        foreach (['titulo', 'descripcion', 'prioridad', 'lawyer_id', 'notas'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
                $changes[] = $f;
            }
        }
        if (empty($fields)) return false;

        $params[] = $id;
        $stmt = self::db()->prepare("UPDATE cases SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($params);

        foreach ($changes as $f) {
            self::addActivity($id, 'actualizado', "Campo '$f' actualizado", $f, null, $data[$f]);
        }
        return true;
    }

    public static function updateStatus(int $id, string $newStatus, ?string $observacion = null): bool
    {
        $db = self::db();
        $current = $db->prepare("SELECT estado, prioridad, assigned_at FROM cases WHERE id = ?");
        $current->execute([$id]);
        $case = $current->fetch();
        if (!$case) return false;

        $oldStatus = $case['estado'];
        if ($oldStatus === $newStatus) return false;
        if (!in_array($newStatus, self::STATUSES)) return false;

        $resolved = null;
        if ($newStatus === 'cerrado' || $newStatus === 'resuelto') {
            $resolved = ', resolved_at = CURRENT_TIMESTAMP';
        } elseif ($oldStatus === 'cerrado' || $oldStatus === 'resuelto') {
            $resolved = ', resolved_at = NULL';
        }

        $stmt = $db->prepare("UPDATE cases SET estado = ?" . ($resolved ?? '') . " WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        $desc = "Estado cambiado de '$oldStatus' a '$newStatus'";
        if ($observacion) $desc .= ": $observacion";
        self::addActivity($id, 'cambio_estado', $desc, 'estado', $oldStatus, $newStatus, $observacion);

        return true;
    }

    public static function addComment(int $id, string $comment, ?string $userName = null): bool
    {
        self::addActivity($id, 'comentario', $comment, null, null, null, $comment, $userName);
        return true;
    }

    public static function addActivity(int $caseId, string $action, string $description,
                                        ?string $field = null, ?string $oldValue = null,
                                        ?string $newValue = null, ?string $observacion = null,
                                        ?string $userName = null): void
    {
        $userName = $userName ?? ($_SESSION['user']['nombre'] ?? 'Sistema');
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO case_activities (case_id, user_name, action, field, old_value, new_value, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$caseId, $userName, $action, $field, $oldValue, $newValue, $description]);
    }

    public static function getActivities(int $caseId): array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM case_activities 
            WHERE case_id = ? 
            ORDER BY created_at DESC 
            LIMIT 100
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }

    public static function close(int $id, ?string $observaciones = null): bool
    {
        return self::updateStatus($id, 'cerrado', $observaciones);
    }

    public static function reopen(int $id): bool
    {
        $stmt = self::db()->prepare("SELECT estado FROM cases WHERE id = ?");
        $stmt->execute([$id]);
        $case = $stmt->fetch();
        if (!$case || !in_array($case['estado'], ['cerrado', 'resuelto'])) return false;

        return self::updateStatus($id, 'en_proceso', 'Reabierto');
    }

    public static function delete(int $id): bool
    {
        $db = self::db();
        $db->prepare("DELETE FROM case_activities WHERE case_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM cases WHERE id = ?")->execute([$id]);
        return true;
    }

    public static function stats(): array
    {
        $db = self::db();
        $totalAbogados = (int)$db->query("SELECT COUNT(*) FROM lawyers")->fetchColumn();
        $totalPersonas = (int)$db->query("SELECT COUNT(*) FROM affected_people")->fetchColumn();
        $totalCasos = (int)$db->query("SELECT COUNT(*) FROM cases")->fetchColumn();

        $porEstado = $db->query("
            SELECT c.estado, COUNT(*) as total FROM cases c GROUP BY c.estado ORDER BY c.estado
        ")->fetchAll();

        $casosAbiertos = 0;
        $casosCerrados = 0;
        foreach ($porEstado as $e) {
            if (in_array($e['estado'], ['cerrado', 'resuelto'])) {
                $casosCerrados += (int)$e['total'];
            } else {
                $casosAbiertos += (int)$e['total'];
            }
        }

        $porPrioridad = $db->query("
            SELECT prioridad, COUNT(*) as total FROM cases GROUP BY prioridad ORDER BY 
                CASE prioridad WHEN 'urgente' THEN 0 WHEN 'alta' THEN 1 WHEN 'media' THEN 2 WHEN 'baja' THEN 3 END
        ")->fetchAll();

        $porAbogado = $db->query("
            SELECT l.nombre, l.id, COUNT(c.id) as total,
                   SUM(CASE WHEN c.estado IN ('cerrado','resuelto') THEN 1 ELSE 0 END) as cerrados,
                   SUM(CASE WHEN c.estado NOT IN ('cerrado','resuelto') THEN 1 ELSE 0 END) as abiertos
            FROM lawyers l
            LEFT JOIN cases c ON c.lawyer_id = l.id
            GROUP BY l.id
            ORDER BY total DESC
            LIMIT 10
        ")->fetchAll();

        $actividadReciente = $db->query("
            SELECT ca.*, c.titulo AS case_titulo
            FROM case_activities ca
            LEFT JOIN cases c ON ca.case_id = c.id
            ORDER BY ca.created_at DESC
            LIMIT 10
        ")->fetchAll();

        $casosAntiguos = $db->query("
            SELECT c.id, c.titulo, c.prioridad, c.estado,
                   l.nombre AS abogado_nombre, p.nombre AS persona_nombre,
                   CAST(julianday('now') - julianday(c.assigned_at) AS INTEGER) AS dias_abierto
            FROM cases c
            LEFT JOIN lawyers l ON c.lawyer_id = l.id
            LEFT JOIN affected_people p ON c.person_id = p.id
            WHERE c.estado NOT IN ('cerrado','resuelto')
            ORDER BY dias_abierto DESC
            LIMIT 5
        ")->fetchAll();

        return [
            'total_abogados'   => $totalAbogados,
            'total_personas'   => $totalPersonas,
            'casos_abiertos'   => $casosAbiertos,
            'casos_cerrados'   => $casosCerrados,
            'total_casos'      => $totalCasos,
            'por_prioridad'    => $porPrioridad,
            'por_abogado'      => $porAbogado,
            'por_estado'       => $porEstado,
            'actividad_reciente' => $actividadReciente,
            'casos_antiguos'   => $casosAntiguos,
        ];
    }

    public static function exportCsv(?string $estado = null, ?string $prioridad = null): string
    {
        $data = self::all($estado, null, $prioridad);
        $csv = "ID,Titulo,Abogado,Persona,Prioridad,Estado,Dias Abierto,Asignado,Resuelto,Observaciones\n";
        foreach ($data as $r) {
            $csv .= implode(',', [
                $r['id'],
                '"' . str_replace('"', '""', $r['titulo'] ?? '') . '"',
                '"' . str_replace('"', '""', $r['abogado_nombre'] ?? '') . '"',
                '"' . str_replace('"', '""', $r['persona_nombre'] ?? '') . '"',
                $r['prioridad'] ?? 'media',
                $r['estado'],
                $r['dias_abierto'] ?? 0,
                $r['assigned_at'],
                $r['resolved_at'] ?? '',
                '"' . str_replace('"', '""', $r['observaciones'] ?? '') . '"'
            ]) . "\n";
        }
        return $csv;
    }

    public static function getRecentActivities(int $limit = 15): array
    {
        $stmt = self::db()->query("
            SELECT ca.*, c.titulo AS case_titulo
            FROM case_activities ca
            LEFT JOIN cases c ON ca.case_id = c.id
            ORDER BY ca.created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    }
}
