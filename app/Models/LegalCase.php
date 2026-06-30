<?php

namespace App\Models;

use App\Core\Model;

class LegalCase extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO cases (lawyer_id, person_id, titulo, descripcion, prioridad) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['lawyer_id'], $data['person_id'],
            $data['titulo'] ?? '', $data['descripcion'] ?? '',
            $data['prioridad'] ?? 'media'
        ]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function get(int $id): ?array
    {
        $stmt = self::db()->prepare("
            SELECT c.*, l.nombre AS abogado_nombre, l.email AS abogado_email, l.telefono AS abogado_telefono,
                   l.jurisdiccion, l.estado AS abogado_estado,
                   p.nombre AS persona_nombre, p.email AS persona_email, p.telefono AS persona_telefono,
                   p.estado AS persona_estado, p.ciudad AS persona_ciudad, p.tipo_ayuda, p.prioridad AS persona_prioridad,
                   p.descripcion AS persona_descripcion
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
                       l.jurisdiccion, p.nombre AS persona_nombre, p.estado AS persona_estado
                FROM cases c
                LEFT JOIN lawyers l ON c.lawyer_id = l.id
                LEFT JOIN affected_people p ON c.person_id = p.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY c.assigned_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['titulo', 'descripcion', 'prioridad', 'lawyer_id', 'notas'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = self::db()->prepare("UPDATE cases SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($params);
        return true;
    }

    public static function close(int $id, ?string $observaciones = null): bool
    {
        if ($observaciones) {
            $stmt = self::db()->prepare("UPDATE cases SET estado = 'cerrado', resolved_at = CURRENT_TIMESTAMP, observaciones = ? WHERE id = ?");
            $stmt->execute([$observaciones, $id]);
        } else {
            $stmt = self::db()->prepare("UPDATE cases SET estado = 'cerrado', resolved_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$id]);
        }
        return true;
    }

    public static function reopen(int $id): bool
    {
        $stmt = self::db()->prepare("UPDATE cases SET estado = 'abierto', resolved_at = NULL WHERE id = ? AND estado = 'cerrado'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        self::db()->prepare("DELETE FROM cases WHERE id = ?")->execute([$id]);
        return true;
    }

    public static function stats(): array
    {
        $db = self::db();
        $totalAbogados = (int)$db->query("SELECT COUNT(*) FROM lawyers")->fetchColumn();
        $totalPersonas = (int)$db->query("SELECT COUNT(*) FROM affected_people")->fetchColumn();
        $casosAbiertos = (int)$db->query("SELECT COUNT(*) FROM cases WHERE estado = 'abierto'")->fetchColumn();
        $casosCerrados = (int)$db->query("SELECT COUNT(*) FROM cases WHERE estado = 'cerrado'")->fetchColumn();
        $totalCasos = $casosAbiertos + $casosCerrados;

        $porPrioridad = $db->query("
            SELECT prioridad, COUNT(*) as total FROM cases GROUP BY prioridad ORDER BY 
                CASE prioridad WHEN 'urgente' THEN 0 WHEN 'alta' THEN 1 WHEN 'media' THEN 2 WHEN 'baja' THEN 3 END
        ")->fetchAll();

        $porAbogado = $db->query("
            SELECT l.nombre, l.id, COUNT(c.id) as total,
                   SUM(CASE WHEN c.estado = 'abierto' THEN 1 ELSE 0 END) as abiertos,
                   SUM(CASE WHEN c.estado = 'cerrado' THEN 1 ELSE 0 END) as cerrados
            FROM lawyers l
            LEFT JOIN cases c ON c.lawyer_id = l.id
            GROUP BY l.id
            ORDER BY total DESC
            LIMIT 10
        ")->fetchAll();

        $porEstado = $db->query("
            SELECT c.estado, COUNT(*) as total FROM cases c GROUP BY c.estado
        ")->fetchAll();

        return [
            'total_abogados' => $totalAbogados,
            'total_personas' => $totalPersonas,
            'casos_abiertos' => $casosAbiertos,
            'casos_cerrados' => $casosCerrados,
            'total_casos'    => $totalCasos,
            'por_prioridad'  => $porPrioridad,
            'por_abogado'    => $porAbogado,
            'por_estado'     => $porEstado,
        ];
    }

    public static function exportCsv(?string $estado = null, ?string $prioridad = null): string
    {
        $data = self::all($estado, null, $prioridad);
        $csv = "ID,Titulo,Abogado,Persona,Prioridad,Estado,Asignado,Resuelto,Observaciones\n";
        foreach ($data as $r) {
            $csv .= implode(',', [
                $r['id'],
                '"' . str_replace('"', '""', $r['titulo'] ?? '') . '"',
                '"' . str_replace('"', '""', $r['abogado_nombre'] ?? '') . '"',
                '"' . str_replace('"', '""', $r['persona_nombre'] ?? '') . '"',
                $r['prioridad'] ?? 'media',
                $r['estado'],
                $r['assigned_at'],
                $r['resolved_at'] ?? '',
                '"' . str_replace('"', '""', $r['observaciones'] ?? '') . '"'
            ]) . "\n";
        }
        return $csv;
    }
}
