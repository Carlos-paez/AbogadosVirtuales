<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public static function create(array $data): array
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, nombre) VALUES (?, ?, ?)");
        $stmt->execute([$data['username'], $data['password_hash'], $data['nombre']]);
        return ['success' => true, 'id' => $db->lastInsertId()];
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
