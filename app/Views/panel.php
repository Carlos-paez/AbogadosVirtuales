<div class="panel-page">
    <div class="panel-header">
        <div>
            <h1>Mi Panel</h1>
            <?php if (!empty($area)): ?>
            <p class="lead">Área de desempeño: <span class="badge badge-info"><?= htmlspecialchars($area['nombre']) ?></span></p>
            <?php else: ?>
            <p class="lead" style="color:var(--warning);">No tiene un área de desempeño asignada. Contacte al administrador.</p>
            <?php endif; ?>
        </div>
        <div class="panel-user-info">
            <span class="nav-credencial" style="font-size:1rem;"><?= htmlspecialchars($currentUser['credencial'] ?? '') ?></span>
        </div>
    </div>

    <div class="crm-stats" id="panelStats">
        <div class="stat-card"><h3>Casos Disponibles</h3><p id="statDisponibles">0</p></div>
        <div class="stat-card"><h3>Mis Casos Asignados</h3><p id="statAsignados">0</p></div>
        <div class="stat-card"><h3>Mi Área</h3><p style="font-size:1rem;"><?= htmlspecialchars($area['nombre'] ?? 'N/A') ?></p></div>
    </div>

    <div class="crm-tabs">
        <button class="tab-btn active" data-tab="disponibles">Casos Disponibles</button>
        <button class="tab-btn" data-tab="asignados">Mis Casos Asignados</button>
    </div>

    <div class="tab-content active" id="tab-disponibles">
        <h2>Casos Disponibles en Mi Área</h2>
        <div class="report-filters" style="margin-bottom:1rem;">
            <div class="form-group" style="flex:2;">
                <label for="filterDisponiblesSearch">Buscar</label>
                <input type="text" id="filterDisponiblesSearch" placeholder="Buscar por título o descripción...">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button id="btnFiltrarDisponibles" class="btn btn-primary">Buscar</button>
            </div>
        </div>
        <div id="casosDisponibles">
            <div class="text-center text-muted" style="padding:2rem;">
                <div class="spinner" style="margin:0 auto;"></div>
                <p>Cargando...</p>
            </div>
        </div>
    </div>

    <div class="tab-content" id="tab-asignados">
        <h2>Mis Casos Asignados</h2>
        <div id="misCasos">
            <div class="text-center text-muted" style="padding:2rem;">
                <div class="spinner" style="margin:0 auto;"></div>
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>
