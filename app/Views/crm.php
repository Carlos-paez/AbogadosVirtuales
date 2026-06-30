<div class="crm-page">
    <h1>CRM - Gestion de Atencion al Cliente</h1>
    <p class="lead">Asigna, da seguimiento y genera reportes de los casos atendidos por abogados voluntarios.</p>

    <div class="crm-stats" id="crmStats">
        <div class="stat-card"><h3>Total Abogados</h3><p id="statAbogados">0</p></div>
        <div class="stat-card"><h3>Total Solicitudes</h3><p id="statPersonas">0</p></div>
        <div class="stat-card"><h3>Casos Abiertos</h3><p id="statAbiertos">0</p></div>
        <div class="stat-card"><h3>Casos Cerrados</h3><p id="statCerrados">0</p></div>
        <div class="stat-card stat-card-wide" id="statBarContainer" style="display:none;">
            <h3>Progreso General</h3>
            <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:0%;">0%</div></div>
        </div>
        <div class="stat-card stat-card-wide" id="statPrioridad" style="display:none;">
            <h3>Casos por Prioridad</h3>
            <div class="mini-chart" id="prioridadChart"></div>
        </div>
        <div class="stat-card stat-card-wide" id="statTopAbogados" style="display:none;">
            <h3>Top Abogados por Casos</h3>
            <div id="topAbogadosList"></div>
        </div>
    </div>

    <div class="crm-tabs">
        <button class="tab-btn active" data-tab="asignar">Asignar Caso</button>
        <button class="tab-btn" data-tab="casos">Casos</button>
        <button class="tab-btn" data-tab="reporte">Reporte General</button>
    </div>

    <div class="tab-content active" id="tab-asignar">
        <h2>Asignar un Caso</h2>
        <form id="formAsignar" class="app-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="person_id">Persona Afectada *</label>
                    <select id="person_id" name="person_id" required>
                        <option value="">Seleccione una persona</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lawyer_id">Abogado *</label>
                    <select id="lawyer_id" name="lawyer_id" required>
                        <option value="">Seleccione un abogado</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="titulo">Titulo del caso</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Ej: Asesoria migratoria">
                </div>
                <div class="form-group">
                    <label for="prioridad_asignar">Prioridad</label>
                    <select id="prioridad_asignar" name="prioridad">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="descripcion_asignar">Descripcion</label>
                <textarea id="descripcion_asignar" name="descripcion" rows="3" placeholder="Detalles del caso..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-text">Asignar Caso</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
            </div>
            <div id="msgAsignar" class="form-message" style="display:none;"></div>
        </form>
    </div>

    <div class="tab-content" id="tab-casos">
        <h2>Todos los Casos</h2>
        <div class="report-filters" style="margin-bottom:1rem;">
            <div class="form-group" style="flex:2;">
                <label for="filterCasosSearch">Buscar</label>
                <input type="text" id="filterCasosSearch" placeholder="Buscar por titulo, abogado o persona...">
            </div>
            <div class="form-group">
                <label for="filterCasosEstado">Estado</label>
                <select id="filterCasosEstado">
                    <option value="">Todos</option>
                    <option value="abierto">Abiertos</option>
                    <option value="cerrado">Cerrados</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filterCasosPrioridad">Prioridad</label>
                <select id="filterCasosPrioridad">
                    <option value="">Todas</option>
                    <option value="urgente">Urgente</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <button id="btnFiltrarCasos" class="btn btn-primary">Filtrar</button>
                    <button id="btnLimpiarCasos" class="btn btn-secondary">Limpiar</button>
                    <button id="btnExportCasos" class="btn btn-secondary">Exportar CSV</button>
                </div>
            </div>
        </div>
        <div id="casosActivos"><div class="text-center text-muted" style="padding:2rem;"><div class="spinner" style="margin:0 auto;"></div><p>Cargando...</p></div></div>
    </div>

    <div class="tab-content" id="tab-reporte">
        <h2>Reporte General de Casos</h2>
        <div class="report-filters" style="margin-bottom:1rem;">
            <div class="form-group">
                <label for="reportFilterEstado">Estado</label>
                <select id="reportFilterEstado">
                    <option value="">Todos</option>
                    <option value="abierto">Abiertos</option>
                    <option value="cerrado">Cerrados</option>
                </select>
            </div>
            <div class="form-group">
                <label for="reportFilterPrioridad">Prioridad</label>
                <select id="reportFilterPrioridad">
                    <option value="">Todas</option>
                    <option value="urgente">Urgente</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <button id="btnGenerarReporte" class="btn btn-primary">
                        <span class="btn-text">Generar Reporte</span>
                        <span class="spinner" style="display:none;"></span>
                    </button>
                    <button id="btnExportReporteCSV" class="btn btn-secondary">Exportar CSV</button>
                </div>
            </div>
        </div>
        <div id="reporteGeneral" style="margin-top:1rem;"><p class="text-muted">Usa los filtros y haz clic en Generar Reporte.</p></div>
    </div>
</div>

<div id="modalCloseCase" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalCloseBtn">&times;</span>
        <h3>Cerrar Caso</h3>
        <form id="formCloseCase">
            <input type="hidden" id="closeCaseId" value="">
            <div class="form-group">
                <label for="closeObservaciones">Observaciones / Notas de cierre</label>
                <textarea id="closeObservaciones" rows="4" placeholder="Describe el resultado del caso..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Confirmar Cierre</button>
                <button type="button" class="btn btn-secondary" id="btnCancelClose">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditCase" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalEditCloseBtn">&times;</span>
        <h3>Editar Caso</h3>
        <form id="formEditCase">
            <input type="hidden" id="editCaseId" value="">
            <div class="form-row">
                <div class="form-group">
                    <label for="editTitulo">Titulo</label>
                    <input type="text" id="editTitulo">
                </div>
                <div class="form-group">
                    <label for="editPrioridad">Prioridad</label>
                    <select id="editPrioridad">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="editAbogado">Abogado asignado</label>
                <select id="editAbogado"><option value="">Seleccionar abogado</option></select>
            </div>
            <div class="form-group">
                <label for="editDescripcion">Descripcion</label>
                <textarea id="editDescripcion" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="editNotas">Notas internas</label>
                <textarea id="editNotas" rows="2" placeholder="Notas visibles solo para administradores..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" id="btnCancelEdit">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetailCase" class="modal" style="display:none;">
    <div class="modal-content modal-lg">
        <span class="modal-close" id="modalDetailCloseBtn">&times;</span>
        <div id="detailCaseContent"><p>Cargando...</p></div>
    </div>
</div>
