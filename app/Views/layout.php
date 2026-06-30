<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Abogados por Venezuela') ?> - Red de Apoyo Legal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?= $basePath ?>/" class="nav-brand">Abogados por Venezuela</a>
            <button class="nav-toggle" id="navToggle" aria-label="Menu">&#9776;</button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?= $basePath ?>/info">Informacion</a></li>
                <li><a href="<?= $basePath ?>/registro">Registro Abogados</a></li>
                <li><a href="<?= $basePath ?>/reportes">Reportes</a></li>
                <li><a href="<?= $basePath ?>/solicitudes">Solicitar Ayuda</a></li>
                <li><a href="<?= $basePath ?>/crm">CRM</a></li>
            </ul>
        </div>
    </nav>

    <main class="container main-content">
        <?= $content ?? '<p>Sin contenido</p>' ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Red de Apoyo Legal - Abogados por Venezuela. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="<?= $basePath ?>/assets/js/app.js"></script>
</body>
</html>
