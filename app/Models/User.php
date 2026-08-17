<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    const ROLES = ['usuario', 'administrador'];
    const CREDENTIAL_PREFIX = 'LEG-';
    const CREDENTIAL_LENGTH = 8;
    const MAX_CREDENTIAL_RETRIES = 10;

    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, nombre, credencial, email, telefono, tipo_documento, numero_documento, pais, estado, ciudad, jurisdiccion, especialidad, anios_experiencia, area_id, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'], $data['password_hash'], $data['nombre'],
            $data['credencial'], $data['email'] ?? null, $data['telefono'] ?? null,
            $data['tipo_documento'] ?? 'V', $data['numero_documento'] ?? null,
            $data['pais'] ?? 'Venezuela', $data['estado'] ?? null,
            $data['ciudad'] ?? null, $data['jurisdiccion'] ?? null,
            $data['especialidad'] ?? null, $data['anios_experiencia'] ?? 0,
            $data['area_id'] ?? null, $data['rol'] ?? 'usuario'
        ]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByCredencial(string $credencial): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE credencial = ? AND activo = 1");
        $stmt->execute([$credencial]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = self::db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $id]);
        return true;
    }

    public static function generateCredential(): string
    {
        $db = self::db();
        for ($i = 0; $i < self::MAX_CREDENTIAL_RETRIES; $i++) {
            $random = bin2hex(random_bytes(self::CREDENTIAL_LENGTH / 2));
            $credencial = self::CREDENTIAL_PREFIX . strtoupper($random);
            $stmt = $db->prepare("SELECT id FROM users WHERE credencial = ?");
            $stmt->execute([$credencial]);
            if (!$stmt->fetch()) {
                return $credencial;
            }
        }
        throw new \RuntimeException("No se pudo generar una credencial única.");
    }

    public static function all(?string $rol = null, ?string $search = null): array
    {
        $db = self::db();
        $where = [];
        $params = [];

        if ($rol) {
            $where[] = "u.rol = ?";
            $params[] = $rol;
        }
        if ($search) {
            $q = "%$search%";
            $where[] = "(u.nombre LIKE ? OR u.email LIKE ? OR u.credencial LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql = "SELECT u.*, a.nombre AS area_nombre FROM users u LEFT JOIN areas a ON u.area_id = a.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['nombre', 'email', 'telefono', 'tipo_documento', 'numero_documento', 'estado', 'ciudad', 'jurisdiccion', 'especialidad', 'anios_experiencia', 'area_id', 'rol', 'activo', 'username'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = self::db()->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM users WHERE id = ? AND rol != 'administrador'");
        return $stmt->execute([$id]);
    }

    public static function countByRol(string $rol): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM users WHERE rol = ?");
        $stmt->execute([$rol]);
        return (int)$stmt->fetchColumn();
    }

    public static function existsDocumento(string $numeroDocumento): bool
    {
        $stmt = self::db()->prepare("SELECT id FROM users WHERE numero_documento = ? AND numero_documento != ''");
        $stmt->execute([$numeroDocumento]);
        return (bool)$stmt->fetch();
    }
}
