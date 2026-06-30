# ⚖️ Abogados por Venezuela — Red de Apoyo Legal

Aplicación web PHP para gestionar una red de abogados voluntarios que brindan orientación jurídica gratuita a personas afectadas por la crisis en Venezuela. Incluye registro de profesionales, solicitudes de ayuda, asignación de casos, CRM completo y generación de reportes.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| **Backend** | PHP 8.3, SQLite, PDO |
| **Frontend** | HTML5, CSS3 (`style.css`), JavaScript (`app.js`) |
| **Arquitectura** | MVC propio (Router → Controller → Model → View) |
| **Base de datos** | SQLite (`data/app.db`), creada y migrada automáticamente |
| **Servidor** | PHP built-in (`php -S`) o Apache con `.htaccess` |

---

## Estructura

```
abogados/
├── index.php                    # Front controller (rutas + static bypass)
├── .htaccess                    # Rewrite rules para Apache
├── data/
│   ├── app.db                   # Base de datos SQLite (auto-creada)
│   ├── info.md                  # Contenido de la página informativa
├── app/
│   ├── autoload.php             # Autoloader PSR-4 (namespace App\)
│   ├── Core/
│   │   ├── Router.php           # Enrutador GET/POST con base path
│   │   ├── Controller.php       # Controlador base (view, json, getJsonInput)
│   │   └── Model.php            # Modelo base (PDO, schema, migraciones)
│   ├── Controllers/
│   │   ├── HomeController.php   # Página de información
│   │   ├── LawyerController.php # CRUD + búsqueda + exportación de abogados
│   │   ├── RequestController.php# Registro de personas afectadas
│   │   ├── ReportController.php # Página de reportes
│   │   └── CrmController.php    # CRM: asignación, edición, cierre, reapertura
│   ├── Models/
│   │   ├── Lawyer.php           # Abogados (crear, listar, buscar, contar, exportar)
│   │   ├── AffectedPerson.php   # Personas afectadas (crear, listar, buscar)
│   │   └── LegalCase.php        # Casos (CRUD, stats, export CSV)
│   └── Views/
│       ├── layout.php           # Layout global (nav, footer, title)
│       ├── info.php             # Página informativa + tabla de contenidos
│       ├── registro.php         # Formulario de registro de abogados
│       ├── reportes.php         # Reportes con búsqueda y filtros
│       ├── solicitudes.php      # Formulario de solicitud de ayuda
│       └── crm.php              # CRM: tabs, stats, modales, reportes
└── assets/
    ├── css/style.css            # Todos los estilos (Inter, glassmorphism, animaciones)
    └── js/app.js                # Todo el JavaScript (fetch, toasts, modales, CRM)
```

---

## Funcionalidades

### Página informativa (`/`, `/info`)
- Contenido markdown renderizado como HTML
- Tabla de contenidos con IDs ancla y smooth scroll
- Banner informativo sobre la causa

### Registro de abogados (`/registro`)
- Formulario con validación en frontend y backend
- Campos: nombre, email, teléfono, tipo/número de documento, estado, ciudad, jurisdicción, especialidad, años de experiencia
- Captura de error UNIQUE en email (409 Conflict)
- Respuesta con errores por campo (`fieldErrors`)

### Solicitudes de ayuda (`/solicitudes`)
- Formulario con checkboxes para tipo de ayuda (Derechos humanos, Familia, Penal, etc.)
- Selector de prioridad (baja, media, alta, urgente)
- Validación: nombre, email, estado, descripción obligatorios
- `tipo_ayuda` se envía como array y se almacena como string separado por comas

### Reportes (`/reportes`)
- Tabla de abogados con búsqueda por texto
- Filtros por estado y jurisdicción
- Resumen: total de abogados, por jurisdicción, por estado
- Exportación a CSV con BOM (compatible con Excel)

### CRM (`/crm`)
- **Dashboard con estadísticas**: total abogados, solicitudes, casos abiertos/cerrados, barra de progreso, gráfico de prioridades, top abogados
- **Asignar caso**: selects con personas y abogados, prioridad, validación FK
- **Lista de casos**: filtros por texto, estado, prioridad; export CSV
- **Detalle de caso**: modal con información completa del caso, abogado, persona
- **Editar caso**: modal para cambiar título, prioridad, abogado, descripción, notas
- **Cerrar caso**: modal con observaciones
- **Reabrir caso**: solo disponible para casos cerrados
- **Reporte general**: filtros estado/prioridad con tabla y export CSV

### API REST

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/obtener-abogados` | Listar abogados (filtros: estado, jurisdiccion) |
| GET | `/api/buscar-abogados?q=` | Buscar abogados por texto |
| GET | `/api/obtener-personas` | Listar personas afectadas |
| GET | `/api/buscar-personas?q=` | Buscar personas por texto |
| GET | `/api/obtener-casos` | Listar casos (filtros: estado, prioridad, q) |
| GET | `/api/obtener-caso?id=N` | Detalle de un caso con joins |
| GET | `/api/estadisticas` | Estadísticas completas |
| GET | `/api/exportar-abogados` | Exportar CSV de abogados |
| GET | `/api/exportar-casos` | Exportar CSV de casos |
| POST | `/api/registro-abogado` | Crear abogado |
| POST | `/api/registro-afectado` | Crear persona afectada |
| POST | `/api/asignar-caso` | Asignar caso (valida FK) |
| POST | `/api/actualizar-caso` | Editar caso (título, prioridad, abogado, notas) |
| POST | `/api/cerrar-caso` | Cerrar caso |
| POST | `/api/reabrir-caso` | Reabrir caso |
| POST | `/api/eliminar-caso` | Eliminar caso |

---

## Base de datos

Tres tablas SQLite con foreign keys y migración automática:

```sql
lawyers (id, nombre, email, telefono, tipo_documento, numero_documento,
         estado, ciudad, jurisdiccion, especialidad, anios_experiencia, created_at)

affected_people (id, nombre, email, telefono, estado, ciudad,
                 tipo_ayuda, prioridad, descripcion, created_at)

cases (id, lawyer_id FK, person_id FK, titulo, descripcion, prioridad,
       estado, assigned_at, resolved_at, notas, observaciones)
```

El archivo `data/app.db` se crea solo si no existe. Si ya existe con columnas faltantes, el método `Model::migrate()` las agrega con `ALTER TABLE ADD COLUMN` usando `PRAGMA table_info()` — los datos existentes nunca se pierden.

---

## Instalación

```bash
# Requisito: PHP 8.0+ con extensiones pdo_sqlite, mbstring
php -S localhost:8000 -t D:\DEV\abogados D:\DEV\abogados\index.php
```

O con Apache: apuntar el DocumentRoot a `D:\DEV\abogados` y el `.htaccess` incluido se encarga de las rewrites.

---

## Convenciones de código

- **PSR-4**: `App\` → `app/`
- **Autoloader**: `spl_autoload_register` en `app/autoload.php`
- **Base path**: el router normaliza `SCRIPT_NAME` para funcionar desde subdirectorios
- **JSON input**: toda API POST lee `php://input` con `json_decode`
- **CSRF**: no implementado (entorno controlado); agregar middleware si se despliega en producción
- **Manejo de errores SQL**:
  - `Unique constraint` → 409 Conflict con `fieldErrors`
  - `Foreign key constraint` → 400 Bad Request
  - Otros → 500 Internal Server Error
- **CSV**: incluye BOM `\xEF\xBB\xBF` para compatibilidad con Excel

---

## Capturas de pantalla

| Página | Descripción |
|--------|-------------|
| `/` | Información de la causa y contexto |
| `/registro` | Formulario de registro de abogados |
| `/solicitudes` | Solicitud de apoyo legal |
| `/reportes` | Reportes de abogados con filtros y exportación |
| `/crm` | CRM completo con estadísticas, tabs y modales |
