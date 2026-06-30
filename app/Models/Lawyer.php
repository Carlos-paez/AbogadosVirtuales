<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

class Lawyer extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO lawyers (nombre, email, telefono, estado, ciudad, jurisdiccion, especialidad) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'], $data['email'], $data['telefono'],
            $data['estado'], $data['ciudad'], $data['jurisdiccion'], $data['especialidad']
        ]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function all(?string $estado = null, ?string $jurisdiccion = null): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if ($estado) {
            $where[] = "estado = ?";
            $params[] = $estado;
        }
        if ($jurisdiccion) {
            $where[] = "jurisdiccion = ?";
            $params[] = $jurisdiccion;
        }

        $sql = "SELECT * FROM lawyers";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY estado, nombre";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM lawyers")->fetchColumn();
    }
}
