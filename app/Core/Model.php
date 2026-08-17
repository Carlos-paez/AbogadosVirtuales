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
                pais TEXT DEFAULT 'Venezuela',
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
                lawyer_id INTEGER,
                person_id INTEGER NOT NULL,
                titulo TEXT,
                descripcion TEXT,
                prioridad TEXT DEFAULT 'media',
                estado TEXT DEFAULT 'pendiente',
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                resolved_at DATETIME,
                notas TEXT,
                observaciones TEXT,
                FOREIGN KEY (lawyer_id) REFERENCES lawyers(id),
                FOREIGN KEY (person_id) REFERENCES affected_people(id)
            );

            CREATE TABLE IF NOT EXISTS case_activities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                case_id INTEGER NOT NULL,
                user_name TEXT,
                action TEXT NOT NULL,
                field TEXT,
                old_value TEXT,
                new_value TEXT,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id)
            );

            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                nombre TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS areas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL UNIQUE,
                descripcion TEXT,
                activo INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS caso_asignacion (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                caso_id INTEGER NOT NULL,
                usuario_id INTEGER NOT NULL,
                estado TEXT DEFAULT 'asignado',
                asignado_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                liberado_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (caso_id) REFERENCES cases(id),
                FOREIGN KEY (usuario_id) REFERENCES users(id)
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
        if (!in_array('pais', $existing)) {
            self::$db->exec("ALTER TABLE lawyers ADD COLUMN pais TEXT DEFAULT 'Venezuela'");
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
        if (!in_array('area_id', $existing)) {
            self::$db->exec("ALTER TABLE cases ADD COLUMN area_id INTEGER REFERENCES areas(id)");
        }
        if (!in_array('usuario_creador_id', $existing)) {
            self::$db->exec("ALTER TABLE cases ADD COLUMN usuario_creador_id INTEGER REFERENCES users(id)");
        }

        $existing = self::$db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('credencial', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN credencial TEXT");
        }
        if (!in_array('email', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN email TEXT");
        }
        if (!in_array('telefono', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN telefono TEXT");
        }
        if (!in_array('tipo_documento', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN tipo_documento TEXT DEFAULT 'V'");
        }
        if (!in_array('numero_documento', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN numero_documento TEXT");
        }
        if (!in_array('pais', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN pais TEXT DEFAULT 'Venezuela'");
        }
        if (!in_array('estado', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN estado TEXT");
        }
        if (!in_array('ciudad', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN ciudad TEXT");
        }
        if (!in_array('jurisdiccion', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN jurisdiccion TEXT");
        }
        if (!in_array('especialidad', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN especialidad TEXT");
        }
        if (!in_array('anios_experiencia', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN anios_experiencia INTEGER DEFAULT 0");
        }
        if (!in_array('area_id', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN area_id INTEGER REFERENCES areas(id)");
        }
        if (!in_array('rol', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN rol TEXT DEFAULT 'usuario'");
        }
        if (!in_array('activo', $existing)) {
            self::$db->exec("ALTER TABLE users ADD COLUMN activo INTEGER DEFAULT 1");
        }

        self::$db->exec("UPDATE cases SET estado = 'pendiente' WHERE estado = 'abierto'");

        // Recreate cases table if lawyer_id is NOT NULL (old schema)
        $caseCols = self::$db->query("PRAGMA table_info(cases)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (in_array('lawyer_id', $caseCols)) {
            $notNull = self::$db->query("PRAGMA table_info(cases)")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($notNull as $col) {
                if ($col['name'] === 'lawyer_id' && $col['notnull'] == 1) {
                    self::$db->exec("
                        CREATE TABLE cases_new (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            lawyer_id INTEGER,
                            person_id INTEGER NOT NULL,
                            titulo TEXT,
                            descripcion TEXT,
                            prioridad TEXT DEFAULT 'media',
                            estado TEXT DEFAULT 'pendiente',
                            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            resolved_at DATETIME,
                            notas TEXT,
                            observaciones TEXT,
                            area_id INTEGER REFERENCES areas(id),
                            usuario_creador_id INTEGER REFERENCES users(id),
                            FOREIGN KEY (person_id) REFERENCES affected_people(id)
                        )
                    ");
                    self::$db->exec("INSERT INTO cases_new SELECT * FROM cases");
                    self::$db->exec("DROP TABLE cases");
                    self::$db->exec("ALTER TABLE cases_new RENAME TO cases");
                    break;
                }
            }
        }

        // Recreate caso_asignacion without UNIQUE on caso_id if needed
        $createSql = self::$db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='caso_asignacion'")->fetchColumn();
        if ($createSql && stripos($createSql, 'UNIQUE') !== false && stripos($createSql, 'caso_id') !== false) {
            self::$db->exec("ALTER TABLE caso_asignacion RENAME TO caso_asignacion_old");
            self::$db->exec("
                CREATE TABLE caso_asignacion (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    caso_id INTEGER NOT NULL,
                    usuario_id INTEGER NOT NULL,
                    estado TEXT DEFAULT 'asignado',
                    asignado_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    liberado_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (caso_id) REFERENCES cases(id),
                    FOREIGN KEY (usuario_id) REFERENCES users(id)
                )
            ");
            self::$db->exec("INSERT INTO caso_asignacion SELECT * FROM caso_asignacion_old");
            self::$db->exec("DROP TABLE caso_asignacion_old");
        }

        self::seedAreas();
        self::createIndexes();
    }

    private static function seedAreas(): void
    {
        $count = (int)self::$db->query("SELECT COUNT(*) FROM areas")->fetchColumn();
        if ($count === 0) {
            $areas = [
                ['Derecho Penal', 'Asesoría y representación en materia penal'],
                ['Derecho Civil', 'Asuntos civiles, contratos, obligaciones'],
                ['Derecho Laboral', 'Relaciones laborales, despidos, beneficios'],
                ['Derecho de Familia', 'Divorcios, custodia, pensiones alimentarias'],
                ['Derecho Migratorio', 'Visas, residencia, nacionalidad'],
                ['Derechos Humanos', 'Violaciones a derechos fundamentales'],
                ['Derecho Administrativo', 'Actos administrativos, recursos'],
                ['Derecho Mercantil', 'Sociedades, comercio, quiebras'],
                ['Derecho Constitucional', 'Amparo, constitucionalidad'],
                ['Derecho Tributario', 'Impuestos, fiscalidad'],
            ];
            $stmt = self::$db->prepare("INSERT INTO areas (nombre, descripcion) VALUES (?, ?)");
            foreach ($areas as [$nombre, $desc]) {
                $stmt->execute([$nombre, $desc]);
            }
        }
    }

    private static function createIndexes(): void
    {
        $indexes = [
            'CREATE INDEX IF NOT EXISTS idx_users_credencial ON users(credencial)',
            'CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)',
            'CREATE INDEX IF NOT EXISTS idx_users_area_id ON users(area_id)',
            'CREATE INDEX IF NOT EXISTS idx_users_rol ON users(rol)',
            'CREATE INDEX IF NOT EXISTS idx_cases_area_id ON cases(area_id)',
            'CREATE INDEX IF NOT EXISTS idx_caso_asignacion_usuario ON caso_asignacion(usuario_id)',
            'CREATE INDEX IF NOT EXISTS idx_caso_asignacion_estado ON caso_asignacion(estado)',
        ];
        foreach ($indexes as $sql) {
            self::$db->exec($sql);
        }
    }
}
