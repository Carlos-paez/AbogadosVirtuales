# Manual de Usuario — Red de Apoyo Legal

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Requisitos del Sistema](#2-requisitos-del-sistema)
3. [Instalación y Ejecución](#3-instalación-y-ejecución)
4. [Estructura del Proyecto](#4-estructura-del-proyecto)
5. [Páginas Públicas](#5-páginas-públicas)
   - 5.1 [Página de Información](#51-página-de-información)
   - 5.2 [Registro de Abogados](#52-registro-de-abogados)
   - 5.3 [Solicitar Ayuda](#53-solicitar-ayuda)
   - 5.4 [Inicio de Sesión](#54-inicio-de-sesión)
6. [Páginas Protegidas](#6-páginas-protegidas)
   - 6.1 [Reportes](#61-reportes)
   - 6.2 [CRM](#62-crm)
7. [API REST](#7-api-rest)
   - 7.1 [Endpoints Públicos](#71-endpoints-públicos)
   - 7.2 [Endpoints Protegidos](#72-endpoints-protegidos)
8. [Base de Datos](#8-base-de-datos)
9. [Arquitectura Técnica](#9-arquitectura-técnica)
10. [Personalización y Despliegue](#10-personalización-y-despliegue)
11. [Resolución de Problemas](#11-resolución-de-problemas)

---

## 1. Introducción

**Red de Apoyo Legal** es una aplicación web desarrollada en PHP puro (sin frameworks) que permite gestionar una red de abogados voluntarios que brindan asesoría legal gratuita a personas afectadas por la crisis en Venezuela.

### Propósito

- Conectar abogados voluntarios con personas que necesitan asistencia legal.
- Facilitar el registro, asignación y seguimiento de casos legales.
- Proveer estadísticas y reportes para la gestión de la red.

### Autor

Carlos Páez — Estudiante de la UPTAEB.

---

## 2. Requisitos del Sistema

- **PHP** 8.0 o superior
- Extensiones PHP requeridas:
  - `pdo_sqlite` — Para la base de datos SQLite
  - `mbstring` — Para manejo de cadenas UTF-8
- **Navegador web** moderno (Chrome, Firefox, Edge, Safari)
- **Servidor**: PHP built-in server (desarrollo) o Apache con `mod_rewrite` (producción)

No requiere:
- MySQL / MariaDB / PostgreSQL
- Composer / Node.js / NPM
- Framework PHP alguno

---

## 3. Instalación y Ejecución

### 3.1 Descargar el proyecto

Clona el repositorio o descarga los archivos en tu máquina:

```bash
git clone <url-del-repositorio> abogados-virtuales
cd abogados-virtuales
```

### 3.2 Ejecutar con PHP built-in server (desarrollo)

```bash
php -S localhost:8000 -t "ruta/al/proyecto" "ruta/al/proyecto/index.php"
```

Ejemplo práctico:

```bash
php -S localhost:8000 -t "C:\Users\cpaez\DEV\Desarrollos\AbogadosVirtuales" "C:\Users\cpaez\DEV\Desarrollos\AbogadosVirtuales\index.php"
```

Luego abre en tu navegador: [http://localhost:8000/](http://localhost:8000/)

### 3.3 Ejecutar con Apache (producción)

Coloca los archivos en el `DocumentRoot` de Apache. El archivo `.htaccess` incluido redirige todas las peticiones a `index.php` (front controller). Asegúrate de que `mod_rewrite` esté habilitado.

### 3.4 Primer inicio de sesión

1. Ve a [http://localhost:8000/login](http://localhost:8000/login)
2. Usa las credenciales por defecto: `admin` / `admin`
3. La base de datos y las tablas se crean automáticamente en `data/app.db`
4. Se recomienda cambiar la contraseña desde el CRM (ícono de engranaje ⚙️)

---

## 4. Estructura del Proyecto

```
├── .htaccess              # Reglas de reescritura Apache
├── index.php              # Controlador frontal (front controller)
├── README.md              # Documentación del proyecto
├── MANUAL.md              # Este manual
├── flayer.md              # Flyer promocional para reclutar abogados
│
├── app/
│   ├── autoload.php       # Autoloader PSR-4 (App\ -> app/)
│   │
│   ├── Core/
│   │   ├── Router.php     # Enrutador GET/POST
│   │   ├── Controller.php # Controlador base (view, json, auth)
│   │   ├── Model.php      # Modelo base (PDO, esquema, migración)
│   │   └── Auth.php       # Autenticación por sesión
│   │
│   ├── Controllers/
│   │   ├── AuthController.php      # Login, logout, cambio de clave
│   │   ├── HomeController.php      # Página de información
│   │   ├── LawyerController.php    # CRUD de abogados
│   │   ├── RequestController.php   # Solicitudes de ayuda
│   │   ├── ReportController.php    # Reportes y directorio
│   │   └── CrmController.php       # CRM completo
│   │
│   ├── Models/
│   │   ├── Lawyer.php              # Modelo de abogados
│   │   ├── AffectedPerson.php      # Modelo de personas afectadas
│   │   ├── LegalCase.php           # Modelo de casos legales
│   │   └── User.php                # Modelo de usuarios
│   │
│   └── Views/
│       ├── layout.php              # Layout global (nav, footer)
│       ├── info.php                # Página de información
│       ├── manual.php              # Manual de usuario
│       ├── registro.php            # Formulario de registro
│       ├── login.php               # Formulario de inicio de sesión
│       ├── solicitudes.php         # Formulario de solicitud
│       ├── reportes.php            # Reportes de abogados
│       └── crm.php                 # CRM
│
└── assets/
    ├── css/
    │   └── style.css               # Hoja de estilos completa
    └── js/
        └── app.js                  # Lógica del lado del cliente

```

---

## 5. Páginas Públicas

Estas secciones son accesibles sin necesidad de iniciar sesión.

### 5.1 Página de Información

**Ruta:** `/` o `/info`

Es la página principal de la aplicación. Explica el contexto de la crisis en Venezuela, quiénes son los voluntarios, cómo funciona la red, los estados del país y las jurisdicciones legales atendidas.

**Características:**
- Contenido renderizado desde `data/info.md` (Markdown).
- Barra lateral con tabla de contenidos generada automáticamente.
- Navegación por anclas con desplazamiento suave.

### 5.2 Registro de Abogados

**Ruta:** `/registro`

Formulario público para que abogados voluntarios se registren en la red.

**Campos del formulario:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| Nombre completo | Texto | Obligatorio |
| Correo electrónico | Email | Obligatorio, único en el sistema |
| Teléfono | Teléfono | Opcional |
| Tipo de documento | Select | V (Venezolano), E (Extranjero), J (Jurídico), Pasaporte |
| Número de documento | Texto | Opcional |
| Estado | Select | 24 estados de Venezuela |
| Ciudad | Texto | Opcional |
| Jurisdicción | Select | Penal, Civil, Laboral, etc. |
| Especialidad | Texto | Opcional |
| Años de experiencia | Número | Opcional |

**Validaciones:**
- El email debe tener formato válido.
- El email es único (no puede haber dos abogados con el mismo email).
- Todos los campos obligatorios son validados tanto en frontend como en backend.

### 5.3 Solicitar Ayuda

**Ruta:** `/solicitudes`

Formulario público para que personas afectadas por la crisis soliciten asistencia legal.

**Campos del formulario:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| Nombre completo | Texto | Obligatorio |
| Correo electrónico | Email | Obligatorio |
| Teléfono | Teléfono | Opcional |
| Estado | Select | Obligatorio |
| Ciudad | Texto | Opcional |
| Prioridad | Select | Baja, Media, Alta, Urgente |
| Tipo de ayuda | Checkboxes | Múltiple: Penal, Civil, Laboral, Migratorio, Familia, DDHH, etc. |
| Descripción | Textarea | Opcional |

### 5.4 Inicio de Sesión

**Ruta:** `/login`

Formulario para que administradores accedan al área protegida.

**Credenciales por defecto:** `admin` / `admin`

**Comportamiento:**
- Si no existe ningún usuario en la base de datos, se crea automáticamente el usuario `admin` con contraseña `admin` al primer intento de inicio de sesión.
- La contraseña se almacena usando bcrypt (`password_hash`).
- Sesiones basadas en cookies PHP (`session_start()`).

---

## 6. Páginas Protegidas

Requieren haber iniciado sesión. Si se intenta acceder sin autenticación, se redirige a `/login`.

### 6.1 Reportes

**Ruta:** `/reportes`

Directorio de abogados registrados, agrupados por estado.

**Características:**
- Búsqueda por texto en cualquier campo.
- Filtros por estado y jurisdicción.
- Vista en tarjetas con diseño de cristal (glassmorphism).
- Conteo total y por grupo.
- Exportación a archivo CSV compatible con Excel (codificación UTF-8 con BOM).

**Exportación CSV:**
Los archivos CSV incluyen: ID, nombre, email, teléfono, tipo y número de documento, estado, ciudad, jurisdicción, especialidad, años de experiencia y fecha de registro.

### 6.2 CRM

**Ruta:** `/crm`

Sistema de gestión de casos. Es el módulo principal y más completo de la aplicación.

#### 6.2.1 Dashboard / Estadísticas

Al acceder al CRM se muestra un panel con:

- **Totales**: Abogados registrados, solicitudes recibidas, casos abiertos, casos cerrados.
- **Barra de progreso**: Porcentaje de casos resueltos vs total.
- **Casos por estado**: Gráfico de barras mostrando la distribución geográfica de los casos.
- **Casos por prioridad**: Gráfico de barras con prioridades (baja, media, alta, urgente).
- **Top abogados**: Ranking de abogados con más casos asignados.
- **Casos antiguos**: Alertas visuales para casos sin resolver:
  - Más de 15 días: fondo amarillo (advertencia).
  - Más de 30 días: fondo rojo (urgente).
- **Actividad reciente**: Feed en tiempo real de las últimas acciones en el sistema.

#### 6.2.2 Asignar Caso

Pestaña para crear y asignar un nuevo caso:

1. Seleccionar persona afectada del menú desplegable.
2. Seleccionar abogado del menú desplegable.
3. Ingresar título, prioridad y descripción del caso.
4. Hacer clic en "Asignar Caso".

**Reglas de asignación:**
- La persona y el abogado deben estar previamente registrados en el sistema.
- Una persona puede tener múltiples casos.
- Un abogado puede tener múltiples casos asignados.

#### 6.2.3 Lista de Casos

Pestaña que muestra todos los casos en una tabla con las siguientes columnas:

| Columna | Descripción |
|---------|-------------|
| ID | Número único del caso |
| Título | Resumen del caso |
| Persona | Nombre de la persona afectada |
| Abogado | Nombre del abogado asignado |
| Prioridad | Con código de colores (rojo=urgente, amarillo=alta, etc.) |
| Estado | Pendiente, En Proceso, Derivado, Resuelto, Cerrado |
| Días | Días transcurridos desde la asignación |
| Acciones | Botones para editar, cambiar estado, cerrar/reabrir, eliminar |

**Filtros disponibles:**
- Búsqueda por texto (con debounce de 350ms).
- Filtro por estado del caso.
- Filtro por prioridad.

**Acciones por caso:**

| Tecla | Acción | Descripción |
|-------|--------|-------------|
| `E` | Editar | Abre modal para editar campos del caso |
| `S` | Estado | Cambia el estado del caso |
| `C` | Cerrar | Cierra el caso (solo si no está cerrado) |
| `R` | Reabrir | Reabre un caso cerrado |
| `X` | Eliminar | Elimina el caso permanentemente |

Los casos con prioridad "urgente" se resaltan con un borde rojo en el lado izquierdo.

#### 6.2.4 Detalle del Caso (Modal)

Al hacer clic en "Ver detalle" o presionar `E`, se abre un modal con:

- **Información del caso**: Título, prioridad, estado, fechas.
- **Datos de la persona**: Nombre, email, teléfono, ubicación.
- **Datos del abogado**: Nombre, email, teléfono, especialidad.
- **Línea de tiempo**: Historial completo de actividades (creación, cambios de estado, actualizaciones, comentarios).
- **Sección de comentarios**: Permite agregar comentarios que quedan registrados en la línea de tiempo.

#### 6.2.5 Reporte del CRM

Pestaña que genera un reporte agrupado por abogado con:

- Nombre del abogado y total de casos asignados.
- Casos por estado (pendientes, en proceso, resueltos, cerrados).
- Filtros por estado y prioridad.
- Exportación a CSV.

#### 6.2.6 Configuración (Cambio de Contraseña)

Accesible desde el ícono de engranaje (⚙️) en el CRM.

**Requisitos:**
- Debe ingresar la contraseña actual.
- La nueva contraseña debe tener al menos 6 caracteres.
- Debe confirmar la nueva contraseña.

---

## 7. API REST

La aplicación expone una API REST JSON para todas las operaciones. Los endpoints protegidos requieren una sesión activa.

### 7.1 Endpoints Públicos

| Método | Ruta | Descripción | Parámetros |
|--------|------|-------------|------------|
| `GET` | `/api/obtener-abogados` | Listar abogados | `?estado=&jurisdiccion=` (opcional) |
| `GET` | `/api/buscar-abogados` | Buscar abogados | `?q=` (texto de búsqueda) |
| `GET` | `/api/obtener-personas` | Listar personas afectadas | — |
| `GET` | `/api/buscar-personas` | Buscar personas | `?q=` (texto de búsqueda) |
| `POST` | `/api/registro-abogado` | Registrar abogado | JSON con campos del formulario |
| `POST` | `/api/registro-afectado` | Registrar persona afectada | JSON con campos del formulario |
| `POST` | `/api/login` | Iniciar sesión | JSON con `username` y `password` |

### 7.2 Endpoints Protegidos

| Método | Ruta | Descripción | Parámetros |
|--------|------|-------------|------------|
| `GET` | `/api/obtener-casos` | Listar casos | `?estado=&prioridad=&q=` |
| `GET` | `/api/obtener-caso` | Detalle de caso | `?id=N` |
| `GET` | `/api/estadisticas` | Estadísticas del dashboard | — |
| `GET` | `/api/actividades` | Actividades recientes | — |
| `GET` | `/api/exportar-abogados` | Exportar abogados a CSV | — |
| `GET` | `/api/exportar-casos` | Exportar casos a CSV | — |
| `POST` | `/api/asignar-caso` | Asignar caso | JSON con `lawyer_id`, `person_id`, `titulo`, `prioridad`, `descripcion` |
| `POST` | `/api/actualizar-caso` | Actualizar caso | JSON con `id` y campos a actualizar |
| `POST` | `/api/cambio-estado` | Cambiar estado | JSON con `id`, `estado`, `observaciones` |
| `POST` | `/api/agregar-comentario` | Agregar comentario | JSON con `case_id`, `comentario` |
| `POST` | `/api/cerrar-caso` | Cerrar caso | JSON con `id`, `notas` |
| `POST` | `/api/reabrir-caso` | Reabrir caso | JSON con `id` |
| `POST` | `/api/eliminar-caso` | Eliminar caso | JSON con `id` |
| `POST` | `/api/cambiar-password` | Cambiar contraseña | JSON con `current_password`, `new_password`, `confirm_password` |

**Formato de respuesta exitosa:**
```json
{
  "success": true,
  "data": { ... }
}
```

**Formato de error:**
```json
{
  "success": false,
  "error": "Mensaje descriptivo del error"
}
```

**Códigos de error HTTP:**
- `200` — Éxito
- `400` — Error de validación o solicitud incorrecta
- `401` — No autorizado (requiere inicio de sesión)
- `404` — Recurso no encontrado
- `409` — Conflicto (ej: email duplicado)
- `500` — Error interno del servidor

---

## 8. Base de Datos

La aplicación utiliza **SQLite** como motor de base de datos. El archivo se crea automáticamente en `data/app.db` la primera vez que se ejecuta la aplicación.

### 8.1 Esquema

#### Tabla: `lawyers`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | Identificador único |
| `nombre` | TEXT NOT NULL | Nombre completo del abogado |
| `email` | TEXT NOT NULL UNIQUE | Correo electrónico |
| `telefono` | TEXT | Número de teléfono |
| `tipo_documento` | TEXT DEFAULT 'V' | V, E, J, Pasaporte |
| `numero_documento` | TEXT | Número de identificación |
| `estado` | TEXT NOT NULL | Estado de Venezuela |
| `ciudad` | TEXT | Ciudad |
| `jurisdiccion` | TEXT NOT NULL | Penal, Civil, Laboral, etc. |
| `especialidad` | TEXT | Área de especialización |
| `anios_experiencia` | INTEGER DEFAULT 0 | Años de experiencia |
| `created_at` | DATETIME | Fecha de registro |

#### Tabla: `affected_people`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | Identificador único |
| `nombre` | TEXT NOT NULL | Nombre completo |
| `email` | TEXT NOT NULL | Correo electrónico |
| `telefono` | TEXT | Número de teléfono |
| `estado` | TEXT NOT NULL | Estado de Venezuela |
| `ciudad` | TEXT | Ciudad |
| `tipo_ayuda` | TEXT | Tipos de ayuda (separados por coma) |
| `prioridad` | TEXT DEFAULT 'media' | baja, media, alta, urgente |
| `descripcion` | TEXT | Descripción del caso |
| `created_at` | DATETIME | Fecha de registro |

#### Tabla: `cases`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | Identificador único |
| `lawyer_id` | INTEGER NOT NULL FK | Abogado asignado |
| `person_id` | INTEGER NOT NULL FK | Persona afectada |
| `titulo` | TEXT | Título del caso |
| `descripcion` | TEXT | Descripción detallada |
| `prioridad` | TEXT DEFAULT 'media' | baja, media, alta, urgente |
| `estado` | TEXT DEFAULT 'pendiente' | pendiente, en_proceso, derivado, resuelto, cerrado |
| `assigned_at` | DATETIME | Fecha de asignación |
| `resolved_at` | DATETIME | Fecha de resolución |
| `notas` | TEXT | Notas internas |
| `observaciones` | TEXT | Observaciones |

#### Tabla: `case_activities`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | Identificador único |
| `case_id` | INTEGER NOT NULL FK | Caso asociado |
| `user_name` | TEXT | Nombre del usuario que realizó la acción |
| `action` | TEXT NOT NULL | Tipo de acción (creado, actualizado, cambio_estado, comentario) |
| `field` | TEXT | Campo modificado |
| `old_value` | TEXT | Valor anterior |
| `new_value` | TEXT | Valor nuevo |
| `description` | TEXT | Descripción de la actividad |
| `created_at` | DATETIME | Fecha de la actividad |

#### Tabla: `users`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INTEGER PK | Identificador único |
| `username` | TEXT NOT NULL UNIQUE | Nombre de usuario |
| `password_hash` | TEXT NOT NULL | Hash bcrypt de la contraseña |
| `nombre` | TEXT NOT NULL | Nombre completo del usuario |
| `created_at` | DATETIME | Fecha de creación |

### 8.2 Migración Automática

El sistema migra el esquema automáticamente al iniciar:
- Las tablas se crean si no existen.
- Las columnas faltantes se agregan con `ALTER TABLE`.
- Los datos existentes nunca se pierden.
- Los valores antiguos de estado (`abierto`) se migran a `pendiente`.

### 8.3 Respaldos

Para respaldar la base de datos, solo copia el archivo `data/app.db`:

```bash
cp data/app.db data/app.db.backup
```

Para restaurar:
```bash
cp data/app.db.backup data/app.db
```

---

## 9. Arquitectura Técnica

### 9.1 Patrón MVC

La aplicación sigue el patrón **Modelo-Vista-Controlador (MVC)**:

1. **Front Controller** (`index.php`): Recibe todas las peticiones y las pasa al enrutador.
2. **Router** (`Router.php`): Determina qué controlador y método ejecutar según la ruta.
3. **Controller**: Procesa la petición, interactúa con los modelos y prepara los datos para la vista.
4. **Model**: Accede a la base de datos mediante PDO con consultas preparadas.
5. **View**: Renderiza HTML usando PHP puro (sin template engine).

### 9.2 Flujo de una Petición

```
Navegador → index.php (.htaccess) → Router → Controller → Model → DB
                                                          ↓
                                                      View → HTML
```

### 9.3 Seguridad

- **Contraseñas**: Hash bcrypt mediante `password_hash()`.
- **SQL Injection**: Todas las consultas usan prepared statements de PDO.
- **XSS**: Las salidas se escapan con `htmlspecialchars()`.
- **Autenticación**: Sesiones PHP con verificación en cada página protegida.
- **Validación**: Tanto en frontend (JavaScript) como en backend (PHP).

### 9.4 Tecnologías del Frontend

- **CSS**: 100% personalizado, diseño glassmorphism, variables CSS, animaciones.
- **JavaScript**: Vanilla JS puro, sin librerías externas.
- **Responsive**: Adaptable a móviles, tablets y escritorio.
- **Google Fonts**: Inter (tipografía principal).

### 9.5 Cero Dependencias Externas

La aplicación no utiliza ningún framework, librería o paquete externo. Todo el código es artesanal:

- Sin Composer / Packagist
- Sin Node.js / NPM
- Sin Laravel / Symfony / CodeIgniter
- Sin Bootstrap / Tailwind / jQuery

---

## 10. Personalización y Despliegue

### 10.1 Personalizar la página de información

Edita el archivo `data/info.md` con el contenido deseado usando sintaxis Markdown básica (títulos `##`, `###`, negritas `**`, listas `-`, párrafos).

### 10.2 Personalizar el flyer promocional

Edita el archivo `flayer.md` en la raíz del proyecto.

### 10.3 Cambiar estilos

Los estilos están en `assets/css/style.css`. La aplicación usa variables CSS para los colores principales:

```css
:root {
    --primary: #1a3a5c;
    --primary-light: #2563eb;
    --secondary: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    /* ... */
}
```

### 10.4 Despliegue en producción

1. Sube todos los archivos al servidor vía FTP o SSH.
2. Asegúrate de que PHP 8.0+ esté instalado con las extensiones `pdo_sqlite` y `mbstring`.
3. Verifica que el directorio `data/` tenga permisos de escritura para el servidor web.
4. Apache: asegúrate de que `mod_rewrite` esté habilitado y que `.htaccess` sea procesado.
5. Cambia las credenciales por defecto inmediatamente después del primer inicio de sesión.

### 10.5 Despliegue en subdirectorio

Si la aplicación se despliega en un subdirectorio (ej: `http://ejemplo.com/abogados/`), el Router maneja automáticamente el `basePath` usando `SCRIPT_NAME`. No requiere configuración adicional.

---

## 11. Resolución de Problemas

### 11.1 Error "500 Internal Server Error"

Causas posibles:
- PHP 8.0+ no está instalado.
- Falta la extensión `pdo_sqlite`.
- El directorio `data/` no tiene permisos de escritura.

Solución: Verifica los requisitos y permisos.

### 11.2 Error "404 Página no encontrada"

Causas posibles:
- La ruta no está registrada en `index.php`.
- Apache no está procesando el `.htaccess`.

Solución: Verifica que el servidor web esté configurado correctamente.

### 11.3 Error "Email ya registrado"

El sistema no permite duplicados de email en el registro de abogados. Usa un email diferente o contacta al administrador si necesitas actualizar un registro existente.

### 11.4 La base de datos no se crea

Verifica que el directorio `data/` tenga permisos de escritura. SQLite necesita poder crear y escribir archivos en ese directorio.

### 11.5 El CSV no se abre correctamente en Excel

El sistema exporta CSV con codificación UTF-8 y BOM (Byte Order Mark). Excel debería reconocerlo automáticamente. Si ves caracteres extraños, importa manualmente desde Excel usando: Datos → Desde Texto/CSV, selecciona codificación UTF-8.

### 11.6 Olvidé la contraseña

Elimina la base de datos (`data/app.db`) y vuelve a iniciar la aplicación. Se recreará con el usuario `admin` / `admin` por defecto.

**Advertencia:** Esto eliminará todos los datos registrados. Si necesitas preservar los datos, edita manualmente la base de datos con una herramienta SQLite.

### 11.7 El menú de navegación no se ve en móvil

La aplicación tiene un menú tipo "hamburguesa" en dispositivos móviles. Presiona el ícono ☰ para mostrar/ocultar el menú.

---

## Apéndice: Atajos de Teclado

| Tecla | Contexto | Acción |
|-------|----------|--------|
| `Esc` | Global | Cierra cualquier modal abierto |
| `E` | Lista de casos | Abre modal de edición del caso |
| `S` | Lista de casos | Cambia el estado del caso |
| `C` | Lista de casos | Cierra el caso seleccionado |
| `R` | Lista de casos | Reabre el caso seleccionado |
| `X` | Lista de casos | Elimina el caso seleccionado |

---

*Documentación generada el Julio 2026. Para soporte o consultas, contacta al administrador del sistema.*
