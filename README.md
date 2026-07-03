# ⚖️ Red de Apoyo Legal

Aplicación web PHP para gestionar una red de abogados voluntarios que brindan orientación jurídica gratuita a personas afectadas por la crisis en Venezuela. Incluye registro de profesionales, solicitudes de ayuda, asignación de casos, CRM completo con trazabilidad, autenticación y generación de reportes.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| **Backend** | PHP 8.3+, SQLite, PDO |
| **Frontend** | HTML5, CSS3 (`style.css`), JavaScript (`app.js`) |
| **Documentación** | Manual de usuario integrado (`/manual`) + `MANUAL.md` |
| **Arquitectura** | MVC propio (Router → Controller → Model → View) |
| **Base de datos** | SQLite (`data/app.db`), creada y migrada automáticamente |
| **Autenticación** | Sesiones PHP con bcrypt, CSRF, rate limiting, HttpOnly/SameSite cookies |
| **Servidor** | PHP built-in (`php -S`) o Apache con `.htaccess` |

---

## Estructura

```
abogados/
├── index.php                    # Front controller (rutas + static bypass)
├── .htaccess                    # Rewrite rules para Apache
├── MANUAL.md                    # Manual de usuario detallado
├── data/
│   ├── app.db                   # Base de datos SQLite (auto-creada)
│   └── info.md                  # Contenido de la página informativa
├── app/
│   ├── autoload.php             # Autoloader PSR-4 (namespace App\)
│   ├── Core/
│   │   ├── Router.php           # Enrutador GET/POST con base path
│   │   ├── Controller.php       # Controlador base (view, json, getJsonInput, requireAuth)
│   │   └── Model.php            # Modelo base (PDO, schema, migraciones)
│   ├── Controllers/
│   │   ├── AuthController.php   # Login/logout vía API + formulario
│   │   ├── HomeController.php   # Página de información
│   │   ├── LawyerController.php # CRUD + búsqueda + exportación de abogados
│   │   ├── RequestController.php# Registro de personas afectadas
│   │   ├── ReportController.php # Página de reportes
│   │   └── CrmController.php    # CRM completo con trazabilidad
│   ├── Models/
│   │   ├── Lawyer.php           # Abogados (CRUD completo: crear, listar, buscar, actualizar, eliminar, exportar)
│   │   ├── AffectedPerson.php   # Personas afectadas (crear, listar, buscar)
│   │   ├── LegalCase.php        # Casos (CRUD, transiciones, stats, actividad, export CSV)
│   │   └── User.php             # Usuarios del sistema (login, verificación)
│   └── Views/
│       ├── layout.php           # Layout global (nav, footer, title, auth condicional)
│       ├── info.php             # Página informativa + tabla de contenidos
│       ├── manual.php           # Manual de usuario integrado al sistema
│       ├── registro.php         # Formulario de registro de abogados
│       ├── login.php            # Inicio de sesión
│       ├── reportes.php         # Reportes con búsqueda y filtros
│       ├── solicitudes.php      # Formulario de solicitud de ayuda
│       └── crm.php              # CRM: dashboard, tabs, modales, timeline, comentarios
└── assets/
    ├── css/style.css            # Todos los estilos (Inter, glassmorphism, animaciones)
    └── js/app.js                # Todo el JavaScript (fetch, toasts, modales, CRM, login)
```

---

## Funcionalidades

### Página informativa (`/`, `/info`)
- Contenido markdown renderizado como HTML
- Tabla de contenidos con IDs ancla y smooth scroll
- Banner informativo sobre la red de apoyo
- Enfoque en la crisis humanitaria de Venezuela, sin referencias políticas

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

### Autenticación (`/login`)
- Inicio de sesión con usuario y contraseña (bcrypt)
- Sesiones PHP seguras: cookies HttpOnly, SameSite=Strict, Secure si HTTPS
- Regeneración de ID de sesión (`session_regenerate_id`) después de login exitoso
- Protección CSRF mediante token de 32 bytes en todas las solicitudes de login
- Rate limiting: bloqueo de 15 minutos tras 5 intentos fallidos
- Usuario admin creado automáticamente en el primer intento de login
- Navegación condicional: muestra CRM y Reportes solo para usuarios autenticados
- Protección de rutas: las páginas y APIs del CRM redirigen con 401 si no hay sesión

### CRM (`/crm`) — protegido con autenticación

**Dashboard**
- Estadísticas: total abogados, solicitudes, casos abiertos/cerrados, barra de progreso
- Gráfico de casos por estado (chart-bar)
- Casos antiguos con alerta visual (>15 días atención, >30 días urgente)
- Actividad reciente (feed en vivo)
- Top abogados por casos abiertos/cerrados

**Asignar caso**
- Selects con personas afectadas y abogados disponibles
- Prioridad (baja, media, alta, urgente)
- Validación de claves foráneas

**Lista de casos**
- Filtros por texto, estado y prioridad
- Indicador visual de urgencia (borde rojo `row-urgente`)
- Contador de días desde la apertura
- Exportación a CSV con BOM

**Gestión de casos**
- **Ver detalle**: modal con información completa, abogado, persona afectada, historial
- **Editar**: modal para cambiar título, prioridad, abogado, descripción, notas internas
- **Cambiar estado**: flujo pendiente → en_proceso → derivado → resuelto → cerrado
- **Cerrar**: modal con observaciones de cierre
- **Reabrir**: disponible solo para casos cerrados
- **Eliminar**: con confirmación

**Trazabilidad**
- Cada acción (creación, cambio de estado, comentario, cierre, reapertura) se registra en `case_activities`
- Timeline visual en el detalle del caso con marcadores por tipo de acción
- Feed de actividad reciente en el dashboard

**Comentarios**
- Sección de comentarios en el detalle de cada caso
- Los comentarios quedan registrados en el historial con tipo `comment`

### Reportes / Gestión de Abogados (`/reportes`) — protegido con autenticación
- Directorio de abogados con búsqueda por texto
- Filtros por estado y jurisdicción
- Resultados agrupados por estado en tarjetas visuales
- **Editar abogado**: modal con formulario completo para modificar datos
- **Eliminar abogado**: confirmación con nombre del abogado
- Resumen: total de abogados encontrados
- Exportación a CSV con BOM (compatible con Excel)

### Manual de Usuario (`/manual`)
- Manual completo integrado a la interfaz de la aplicacion
- Explica al usuario final como usar cada seccion del sistema
- Incluye informacion sobre el proposito y creador de la aplicacion
- Tabla de contenidos lateral con navegacion por anclas
- Tambien disponible en formato Markdown (`MANUAL.md`)

### API REST

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| GET | `/api/obtener-abogados` | — | Listar abogados (filtros: estado, jurisdiccion) |
| GET | `/api/buscar-abogados?q=` | — | Buscar abogados por texto |
| GET | `/api/obtener-personas` | — | Listar personas afectadas |
| GET | `/api/buscar-personas?q=` | — | Buscar personas por texto |
| POST | `/api/registro-abogado` | — | Crear abogado |
| POST | `/api/registro-afectado` | — | Crear persona afectada |
| POST | `/api/login` | — | Iniciar sesión (email+password) |
| GET | `/api/obtener-casos` | Sí | Listar casos (filtros: estado, prioridad, q) |
| GET | `/api/obtener-caso?id=N` | Sí | Detalle de un caso con joins |
| GET | `/api/estadisticas` | Sí | Estadísticas completas del dashboard |
| GET | `/api/actividades-recientes` | Sí | Feed de actividad reciente |
| GET | `/api/actividades-caso?id=N` | Sí | Historial completo de un caso |
| POST | `/api/actualizar-abogado` | Sí | Actualizar datos de un abogado |
| POST | `/api/eliminar-abogado` | Sí | Eliminar un abogado |
| GET | `/api/exportar-abogados` | Sí | Exportar CSV de abogados |
| GET | `/api/exportar-casos` | Sí | Exportar CSV de casos |
| POST | `/api/asignar-caso` | Sí | Asignar caso (valida FK) |
| POST | `/api/actualizar-caso` | Sí | Editar caso (título, prioridad, abogado, notas) |
| POST | `/api/cambiar-estado` | Sí | Cambiar estado del caso |
| POST | `/api/agregar-comentario` | Sí | Agregar comentario a un caso |
| POST | `/api/cerrar-caso` | Sí | Cerrar caso |
| POST | `/api/reabrir-caso` | Sí | Reabrir caso |
| POST | `/api/eliminar-caso` | Sí | Eliminar caso |

---

## Base de datos

Cinco tablas SQLite con foreign keys y migración automática:

```sql
lawyers (id, nombre, email, telefono, tipo_documento, numero_documento,
         estado, ciudad, jurisdiccion, especialidad, anios_experiencia, created_at)

affected_people (id, nombre, email, telefono, estado, ciudad,
                 tipo_ayuda, prioridad, descripcion, created_at)

cases (id, lawyer_id FK, person_id FK, titulo, descripcion, prioridad,
       estado, assigned_at, resolved_at, notas, observaciones)

case_activities (id, case_id FK, user_name, action, field_name,
                 old_value, new_value, description, created_at)

users (id, email, password_hash, nombre, role, created_at)
```

El archivo `data/app.db` se crea solo si no existe. Si ya existe con columnas faltantes, el método `Model::migrate()` las agrega con `ALTER TABLE ADD COLUMN` usando `PRAGMA table_info()` — los datos existentes nunca se pierden.

---

## Instalación

```bash
# Requisito: PHP 8.0+ con extensiones pdo_sqlite, mbstring
php -S localhost:8000 -t /ruta/al/proyecto /ruta/al/proyecto/index.php
```

O con Apache: apuntar el DocumentRoot a la carpeta del proyecto y el `.htaccess` incluido se encarga de las rewrites.

### Primer uso

1. Iniciar el servidor
2. Visitar cualquier página pública (las tablas se crean automáticamente)
3. Ir a `/login` e iniciar sesión con las credenciales administrador (se crean automáticamente en el primer inicio de sesión)

---

## Convenciones de código

- **PSR-4**: `App\` → `app/`
- **Autoloader**: `spl_autoload_register` en `app/autoload.php`
- **Base path**: el router normaliza `SCRIPT_NAME` para funcionar desde subdirectorios
- **JSON input**: toda API POST lee `php://input` con `json_decode`
- **CSRF**: token de 32 bytes generado por `Auth::generateCsrfToken()`, validado en login
- **Rate limiting**: máximo 5 intentos de login, bloqueo de 15 minutos por sesión
- **Manejo de errores SQL**:
  - `Unique constraint` → 409 Conflict con `fieldErrors`
  - `Foreign key constraint` → 400 Bad Request
  - Otros → 500 Internal Server Error
- **CSV**: incluye BOM `\xEF\xBB\xBF` para compatibilidad con Excel
- **Auth**: sesiones PHP con bcrypt, `session_regenerate_id()` tras login, cookies con HttpOnly/SameSite=Strict; rutas protegidas con `requireAuth()`
- **Trazabilidad**: toda modificación de casos se registra en `case_activities` con usuario, acción y valores anteriores/nuevos

---

## Capturas de pantalla

| Página | Descripción |
|--------|-------------|
| `/` | Información sobre la red de apoyo (enfoque Venezuela) |
| `/registro` | Formulario de registro de abogados |
| `/solicitudes` | Solicitud de apoyo legal |
| `/login` | Inicio de sesión |
| `/manual` | Manual de usuario integrado |
| `/crm` | CRM completo con dashboard, estadísticas, timeline, comentarios |
| `/reportes` | Reportes de abogados con filtros y exportación |

---

Creada por Carlos Páez — Estudiante de la UPTAEB — con el apoyo de herramientas de IA, con la intención de aportar ayuda en momentos difíciles.
