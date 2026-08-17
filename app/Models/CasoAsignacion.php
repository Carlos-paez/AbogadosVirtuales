<?php

namespace App\Models;

use App\Core\Model;

class CasoAsignacion extends Model
{
    public static function asignar(int $casoId, int $usuarioId): array
    {
        $db = self::db();
        try {
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare("SELECT estado FROM cases WHERE id = ?");
            $stmt->execute([$casoId]);
            $case = $stmt->fetch();
            if (!$case) {
                $db->exec('ROLLBACK');
                return ['success' => false, 'error' => 'El caso no existe.'];
            }

            $stmt = $db->prepare("SELECT id FROM caso_asignacion WHERE caso_id = ? AND estado != 'liberado'");
            $stmt->execute([$casoId]);
            $existing = $stmt->fetch();
            if ($existing) {
                $db->exec('ROLLBACK');
                return ['success' => false, 'error' => 'Este caso ya fue seleccionado por otro usuario.'];
            }

            $stmt = $db->prepare("INSERT INTO caso_asignacion (caso_id, usuario_id, estado) VALUES (?, ?, 'asignado')");
            $stmt->execute([$casoId, $usuarioId]);

            $db->exec('COMMIT');
            return ['success' => true, 'message' => 'Caso seleccionado exitosamente.'];
        } catch (\Exception $e) {
            $db->exec('ROLLBACK');
            return ['success' => false, 'error' => 'Error al asignar el caso.'];
        }
    }

    public static function liberar(int $casoId): array
    {
        $db = self::db();
        try {
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare("UPDATE caso_asignacion SET estado = 'liberado', liberado_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE caso_id = ? AND estado != 'liberado'");
            $stmt->execute([$casoId]);
            $affected = $stmt->rowCount();
            $db->exec('COMMIT');
            return ['success' => true, 'message' => $affected > 0 ? 'Caso liberado.' : 'No había asignación activa.'];
        } catch (\Exception $e) {
            $db->exec('ROLLBACK');
            return ['success' => false, 'error' => 'Error al liberar caso.'];
        }
    }

    public static function porUsuario(int $usuarioId): array
    {
        $stmt = self::db()->prepare("
            SELECT ca.*, c.titulo, c.descripcion, c.prioridad, c.estado AS caso_estado,
                   c.assigned_at AS caso_created_at, c.area_id,
                   p.nombre AS persona_nombre, p.email AS persona_email,
                   a.nombre AS area_nombre
            FROM caso_asignacion ca
            JOIN cases c ON ca.caso_id = c.id
            LEFT JOIN affected_people p ON c.person_id = p.id
            LEFT JOIN areas a ON c.area_id = a.id
            WHERE ca.usuario_id = ? AND ca.estado != 'liberado'
            ORDER BY ca.asignado_at DESC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    public static function existeAsignacion(int $casoId): ?array
    {
        $stmt = self::db()->prepare("
            SELECT ca.*, u.nombre AS usuario_nombre, u.credencial
            FROM caso_asignacion ca
            JOIN users u ON ca.usuario_id = u.id
            WHERE ca.caso_id = ? AND ca.estado != 'liberado'
        ");
        $stmt->execute([$casoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function todas(): array
    {
        return self::db()->query("
            SELECT ca.*, c.titulo, c.prioridad, c.estado AS caso_estado,
                   u.nombre AS usuario_nombre, u.credencial,
                   a.nombre AS area_nombre
            FROM caso_asignacion ca
            JOIN cases c ON ca.caso_id = c.id
            JOIN users u ON ca.usuario_id = u.id
            LEFT JOIN areas a ON c.area_id = a.id
            ORDER BY ca.asignado_at DESC
        ")->fetchAll();
    }

    public static function count(): int
    {
        return (int)self::db()->query("SELECT COUNT(*) FROM caso_asignacion WHERE estado != 'liberado'")->fetchColumn();
    }
}
