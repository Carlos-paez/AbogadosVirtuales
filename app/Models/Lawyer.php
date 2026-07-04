<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

class Lawyer extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO lawyers (nombre, email, telefono, tipo_documento, numero_documento, pais, estado, ciudad, jurisdiccion, especialidad, anios_experiencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'], $data['email'], $data['telefono'],
            $data['tipo_documento'] ?? 'V', $data['numero_documento'] ?? '',
            $data['pais'] ?? 'Venezuela', $data['estado'], $data['ciudad'],
            $data['jurisdiccion'],
            $data['especialidad'], (int)($data['anios_experiencia'] ?? 0)
        ]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function all(?string $estado = null, ?string $jurisdiccion = null, ?string $pais = null): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if ($estado) {
            $where[] = "l.estado = ?";
            $params[] = $estado;
        }
        if ($jurisdiccion) {
            $where[] = "l.jurisdiccion = ?";
            $params[] = $jurisdiccion;
        }
        if ($pais) {
            $where[] = "l.pais LIKE ?";
            $params[] = $pais;
        }

        $sql = "SELECT l.* FROM lawyers l";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY l.pais, l.estado, l.nombre";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM lawyers WHERE id = ?");
        $stmt->execute([$id]);
        $lawyer = $stmt->fetch();
        return $lawyer ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $db = self::db();
        $fields = [];
        $params = [];
        foreach (['nombre', 'email', 'telefono', 'tipo_documento', 'numero_documento', 'estado', 'ciudad', 'jurisdiccion', 'especialidad', 'anios_experiencia'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) {
            return false;
        }
        $params[] = $id;
        $stmt = $db->prepare("UPDATE lawyers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM lawyers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function search(string $query): array
    {
        $db = self::db();
        $q = "%$query%";
        $stmt = $db->prepare("SELECT * FROM lawyers WHERE nombre LIKE ? OR email LIKE ? OR especialidad LIKE ? OR ciudad LIKE ? OR estado LIKE ? OR pais LIKE ? ORDER BY pais, estado, nombre LIMIT 200");
        $stmt->execute([$q, $q, $q, $q, $q, $q]);
        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM lawyers")->fetchColumn();
    }

    public static function exportCsv(?string $estado = null, ?string $jurisdiccion = null, ?string $pais = null): string
    {
        $data = self::all($estado, $jurisdiccion, $pais);
        $csv = "ID,Nombre,Email,Telefono,Tipo Doc,Num Doc,Pais,Estado,Ciudad,Jurisdiccion,Especialidad,Anios Exp,Registrado\n";
        foreach ($data as $r) {
            $csv .= implode(',', [
                $r['id'], '"' . str_replace('"', '""', $r['nombre']) . '"',
                $r['email'], $r['telefono'], $r['tipo_documento'], $r['numero_documento'],
                $r['pais'] ?? 'Venezuela', $r['estado'], $r['ciudad'], $r['jurisdiccion'],
                '"' . str_replace('"', '""', $r['especialidad'] ?? '') . '"',
                $r['anios_experiencia'] ?? 0, $r['created_at']
            ]) . "\n";
        }
        return $csv;
    }
}
