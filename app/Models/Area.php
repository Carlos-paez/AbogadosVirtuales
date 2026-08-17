<?php

namespace App\Models;

use App\Core\Model;

class Area extends Model
{
    public static function all(): array
    {
        return self::db()->query("SELECT * FROM areas ORDER BY nombre")->fetchAll();
    }

    public static function active(): array
    {
        return self::db()->query("SELECT * FROM areas WHERE activo = 1 ORDER BY nombre")->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM areas WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO areas (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$data['nombre'], $data['descripcion'] ?? null]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['nombre', 'descripcion', 'activo'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = self::db()->prepare("UPDATE areas SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM areas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM areas")->fetchColumn();
    }

    public static function countUsers(int $areaId): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM users WHERE area_id = ?");
        $stmt->execute([$areaId]);
        return (int)$stmt->fetchColumn();
    }
}
