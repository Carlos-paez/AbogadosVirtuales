<?php

namespace App\Models;

use App\Core\Model;

class LegalCase extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO cases (lawyer_id, person_id, titulo, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['lawyer_id'], $data['person_id'], $data['titulo'] ?? '', $data['descripcion'] ?? '']);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function all(): array
    {
        return self::db()->query("
            SELECT c.*, l.nombre AS abogado_nombre, l.estado AS abogado_estado, l.jurisdiccion,
                   p.nombre AS persona_nombre, p.estado AS persona_estado
            FROM cases c
            LEFT JOIN lawyers l ON c.lawyer_id = l.id
            LEFT JOIN affected_people p ON c.person_id = p.id
            ORDER BY c.assigned_at DESC
        ")->fetchAll();
    }

    public static function close(int $id): bool
    {
        $stmt = self::db()->prepare("UPDATE cases SET estado = 'cerrado', resolved_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }

    public static function delete(int $id): bool
    {
        self::db()->prepare("DELETE FROM cases WHERE id = ?")->execute([$id]);
        return true;
    }

    public static function stats(): array
    {
        $db = self::db();
        return [
            'total_abogados' => (int)$db->query("SELECT COUNT(*) FROM lawyers")->fetchColumn(),
            'total_personas' => (int)$db->query("SELECT COUNT(*) FROM affected_people")->fetchColumn(),
            'casos_abiertos' => (int)$db->query("SELECT COUNT(*) FROM cases WHERE estado = 'abierto'")->fetchColumn(),
            'casos_cerrados' => (int)$db->query("SELECT COUNT(*) FROM cases WHERE estado = 'cerrado'")->fetchColumn(),
            'total_casos'    => (int)$db->query("SELECT COUNT(*) FROM cases")->fetchColumn(),
        ];
    }
}
