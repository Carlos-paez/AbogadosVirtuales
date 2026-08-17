<div class="admin-page">
    <h1>Panel Administrativo</h1>
    <p class="lead">Gestiona usuarios, áreas de desempeño y casos del sistema.</p>

    <div class="crm-stats" id="adminStats">
        <div class="stat-card"><h3>Usuarios</h3><p id="statUsuarios">0</p></div>
        <div class="stat-card"><h3>Áreas</h3><p id="statAreasAdmin">0</p></div>
        <div class="stat-card"><h3>Casos</h3><p id="statCasosAdmin">0</p></div>
        <div class="stat-card"><h3>Asignaciones</h3><p id="statAsignaciones">0</p></div>
    </div>

    <div class="crm-tabs">
        <button class="tab-btn active" data-tab="admin-usuarios">Usuarios</button>
        <button class="tab-btn" data-tab="admin-areas">Áreas</button>
        <button class="tab-btn" data-tab="admin-casos">Crear Caso</button>
        <button class="tab-btn" data-tab="admin-asignaciones">Asignaciones</button>
    </div>

    <div class="tab-content active" id="tab-admin-usuarios">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
            <h2 style="margin:0;">Gestión de Usuarios</h2>
        </div>
        <div class="report-filters" style="margin-bottom:1rem;">
            <div class="form-group" style="flex:2;">
                <label for="filterAdminUsuariosSearch">Buscar</label>
                <input type="text" id="filterAdminUsuariosSearch" placeholder="Buscar por nombre, email o credencial...">
            </div>
            <div class="form-group">
                <label for="filterAdminUsuariosRol">Rol</label>
                <select id="filterAdminUsuariosRol">
                    <option value="">Todos</option>
                    <option value="usuario">Usuario</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button id="btnFiltrarAdminUsuarios" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
        <div id="adminUsuariosList"></div>
    </div>

    <div class="tab-content" id="tab-admin-areas">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
            <h2 style="margin:0;">Gestión de Áreas</h2>
            <button class="btn btn-primary btn-sm" id="btnCrearArea">+ Nueva Área</button>
        </div>
        <div id="adminAreasList"></div>

        <div id="formCrearArea" style="display:none;margin-top:1rem;">
            <div class="app-form" style="max-width:500px;">
                <h3 id="areaFormTitle">Nueva Área</h3>
                <input type="hidden" id="editAreaId" value="">
                <div class="form-group">
                    <label for="areaNombre">Nombre *</label>
                    <input type="text" id="areaNombre" required>
                    <span class="error-msg"></span>
                </div>
                <div class="form-group">
                    <label for="areaDescripcion">Descripción</label>
                    <textarea id="areaDescripcion" rows="2"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" id="btnGuardarArea">Guardar</button>
                    <button type="button" class="btn btn-secondary" id="btnCancelarArea">Cancelar</button>
                </div>
                <div id="msgArea" class="form-message" style="display:none;"></div>
            </div>
        </div>
    </div>

    <div class="tab-content" id="tab-admin-casos">
        <h2>Crear Nuevo Caso</h2>
        <div class="app-form" style="max-width:600px;">
            <div class="form-row">
                <div class="form-group">
                    <label for="adminCasoPersona">Persona Afectada *</label>
                    <select id="adminCasoPersona" required>
                        <option value="">Seleccione una persona</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminCasoArea">Área *</label>
                    <select id="adminCasoArea" required>
                        <option value="">Seleccione un área</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="adminCasoTitulo">Título</label>
                    <input type="text" id="adminCasoTitulo" placeholder="Ej: Asesoría migratoria">
                </div>
                <div class="form-group">
                    <label for="adminCasoPrioridad">Prioridad</label>
                    <select id="adminCasoPrioridad">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="adminCasoDescripcion">Descripción</label>
                <textarea id="adminCasoDescripcion" rows="3" placeholder="Detalles del caso..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="btnCrearCasoAdmin">
                    <span class="btn-text">Crear Caso</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
            </div>
            <div id="msgCrearCaso" class="form-message" style="display:none;"></div>
        </div>
    </div>

    <div class="tab-content" id="tab-admin-asignaciones">
        <h2>Asignaciones de Casos</h2>
        <div id="adminAsignacionesList"></div>
    </div>
</div>

<div id="modalEditUsuario" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalEditUsuarioClose">&times;</span>
        <h3>Editar Usuario</h3>
        <form id="formEditUsuario" class="app-form" style="box-shadow:none;padding:0;margin-top:1rem;">
            <input type="hidden" id="editUsuarioId" value="">
            <div class="form-group">
                <label for="editUsuarioNombre">Nombre *</label>
                <input type="text" id="editUsuarioNombre" required>
            </div>
            <div class="form-group">
                <label for="editUsuarioEmail">Email</label>
                <input type="email" id="editUsuarioEmail">
            </div>
            <div class="form-group">
                <label for="editUsuarioRol">Rol</label>
                <select id="editUsuarioRol">
                    <option value="usuario">Usuario</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editUsuarioArea">Área</label>
                <select id="editUsuarioArea">
                    <option value="">Sin área</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editUsuarioActivo">Estado</label>
                <select id="editUsuarioActivo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-text">Guardar</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelEditUsuario">Cancelar</button>
            </div>
            <div id="msgEditUsuario" class="form-message" style="display:none;"></div>
        </form>
    </div>
</div>
