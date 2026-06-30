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
                estado TEXT NOT NULL,
                ciudad TEXT,
                jurisdiccion TEXT NOT NULL,
                especialidad TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS affected_people (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL,
                telefono TEXT,
                estado TEXT NOT NULL,
                ciudad TEXT,
                descripcion TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS cases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                lawyer_id INTEGER NOT NULL,
                person_id INTEGER NOT NULL,
                titulo TEXT,
                descripcion TEXT,
                estado TEXT DEFAULT 'abierto',
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                resolved_at DATETIME,
                notas TEXT,
                FOREIGN KEY (lawyer_id) REFERENCES lawyers(id),
                FOREIGN KEY (person_id) REFERENCES affected_people(id)
            );
        ");
    }
}
