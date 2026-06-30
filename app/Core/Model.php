<?php

namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected static ?PDO $db = null;

    protected static function db(): PDO
    {
        if (self::$db === null) {
            $dbPath = __DIR__ . '/../../data/app.db';
            self::$db = new PDO("sqlite:$dbPath");
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$db->exec('PRAGMA journal_mode=WAL');
            self::$db->exec('PRAGMA foreign_keys=ON');
            self::initTables();
        }
        return self::$db;
    }

    private static function initTables(): void
    {
        self::$db->exec("
            CREATE TABLE IF NOT EXISTS lawyers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                telefono TEXT,
                tipo_documento TEXT DEFAULT 'V',
                numero_documento TEXT,
                estado TEXT NOT NULL,
                ciudad TEXT,
                jurisdiccion TEXT NOT NULL,
                especialidad TEXT,
                anios_experiencia INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS affected_people (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL,
                telefono TEXT,
                estado TEXT NOT NULL,
                ciudad TEXT,
                tipo_ayuda TEXT,
                prioridad TEXT DEFAULT 'media',
                descripcion TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS cases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                lawyer_id INTEGER NOT NULL,
                person_id INTEGER NOT NULL,
                titulo TEXT,
                descripcion TEXT,
                prioridad TEXT DEFAULT 'media',
                estado TEXT DEFAULT 'abierto',
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                resolved_at DATETIME,
                notas TEXT,
                observaciones TEXT,
                FOREIGN KEY (lawyer_id) REFERENCES lawyers(id),
                FOREIGN KEY (person_id) REFERENCES affected_people(id)
            );
        ");

        self::migrate();
    }

    private static function migrate(): void
    {
        $existing = self::$db->query("PRAGMA table_info(lawyers)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('tipo_documento', $existing)) {
            self::$db->exec("ALTER TABLE lawyers ADD COLUMN tipo_documento TEXT DEFAULT 'V'");
        }
        if (!in_array('numero_documento', $existing)) {
            self::$db->exec("ALTER TABLE lawyers ADD COLUMN numero_documento TEXT");
        }
        if (!in_array('anios_experiencia', $existing)) {
            self::$db->exec("ALTER TABLE lawyers ADD COLUMN anios_experiencia INTEGER DEFAULT 0");
        }

        $existing = self::$db->query("PRAGMA table_info(affected_people)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('tipo_ayuda', $existing)) {
            self::$db->exec("ALTER TABLE affected_people ADD COLUMN tipo_ayuda TEXT");
        }
        if (!in_array('prioridad', $existing)) {
            self::$db->exec("ALTER TABLE affected_people ADD COLUMN prioridad TEXT DEFAULT 'media'");
        }

        $existing = self::$db->query("PRAGMA table_info(cases)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('prioridad', $existing)) {
            self::$db->exec("ALTER TABLE cases ADD COLUMN prioridad TEXT DEFAULT 'media'");
        }
        if (!in_array('observaciones', $existing)) {
            self::$db->exec("ALTER TABLE cases ADD COLUMN observaciones TEXT");
        }
    }
}
