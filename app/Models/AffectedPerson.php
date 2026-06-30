<?php

namespace App\Models;

use App\Core\Model;

class AffectedPerson extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO affected_people (nombre, email, telefono, estado, ciudad, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'], $data['email'], $data['telefono'],
            $data['estado'], $data['ciudad'], $data['descripcion']
        ]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function all(): array
    {
        return self::db()->query("SELECT * FROM affected_people ORDER BY created_at DESC")->fetchAll();
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM affected_people")->fetchColumn();
    }
}
