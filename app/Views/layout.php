<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Red de Apoyo Legal') ?> - Red de Apoyo Legal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?= $basePath ?>/" class="nav-brand">Red de Apoyo Legal</a>
            <button class="nav-toggle" id="navToggle" aria-label="Menu">&#9776;</button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?= $basePath ?>/info">Información</a></li>
                <li><a href="<?= $basePath ?>/manual">Manual</a></li>
                <?php if (!empty($isLoggedIn)): ?>
                    <?php if (($currentUser['rol'] ?? '') === 'administrador'): ?>
                    <li><a href="<?= $basePath ?>/crm">CRM</a></li>
                    <li><a href="<?= $basePath ?>/reportes">Reportes</a></li>
                    <li><a href="<?= $basePath ?>/admin">Admin</a></li>
                    <?php else: ?>
                    <li><a href="<?= $basePath ?>/panel">Mi Panel</a></li>
                    <?php endif; ?>
                <?php else: ?>
                <li><a href="<?= $basePath ?>/registro">Registro</a></li>
                <li><a href="<?= $basePath ?>/solicitudes">Solicitar Ayuda</a></li>
                <?php endif; ?>
            </ul>
            <ul class="nav-auth">
                <?php if (!empty($isLoggedIn)): ?>
                <li><span class="nav-user"><?= htmlspecialchars($currentUser['nombre'] ?? '') ?></span></li>
                <?php if (!empty($currentUser['credencial'])): ?>
                <li><span class="nav-credencial"><?= htmlspecialchars($currentUser['credencial']) ?></span></li>
                <?php endif; ?>
                <li><a href="<?= $basePath ?>/logout" class="btn btn-sm btn-outline">Cerrar Sesión</a></li>
                <?php else: ?>
                <li><a href="<?= $basePath ?>/login" class="btn btn-sm btn-primary">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container main-content">
        <?= $content ?? '<p>Sin contenido</p>' ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Red de Apoyo Legal. Todos los derechos reservados.</p>
            <p class="credit">Creada por Carlos Páez — Estudiante de la UPTAEB — con el apoyo de herramientas de IA, con la intención de aportar ayuda en momentos difíciles.</p>
        </div>
    </footer>

    <script>var BASE_PATH = <?= json_encode($basePath) ?>;var CSRF_TOKEN = <?= json_encode($csrf_token ?? '') ?>;</script>
    <script src="<?= $basePath ?>/assets/js/app.js"></script>
</body>
</html>
