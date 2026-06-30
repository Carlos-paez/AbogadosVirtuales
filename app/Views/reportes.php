<div class="reports-page">
    <h1>Directorio de Abogados por Estado y Jurisdiccion</h1>
    <p class="lead">Explora los abogados registrados organizados por ubicacion y especialidad.</p>

    <div class="report-filters">
        <div class="form-group" style="flex:2;">
            <label for="searchText">Buscar por nombre, email o especialidad</label>
            <input type="text" id="searchText" placeholder="Escribe para buscar..." style="min-width:200px;">
        </div>
        <div class="form-group">
            <label for="filterEstado">Filtrar por Estado</label>
            <select id="filterEstado">
                <option value="">Todos los estados</option>
            </select>
        </div>
        <div class="form-group">
            <label for="filterJurisdiccion">Filtrar por Jurisdiccion</label>
            <select id="filterJurisdiccion">
                <option value="">Todas las jurisdicciones</option>
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <button id="btnFiltrar" class="btn btn-primary">Filtrar</button>
                <button id="btnLimpiar" class="btn btn-secondary">Limpiar</button>
                <button id="btnExportCSV" class="btn btn-secondary">Exportar CSV</button>
            </div>
        </div>
    </div>

    <div id="reportSummary" class="report-summary" style="display:none;"></div>

    <div id="reportResults">
        <div class="text-center text-muted" style="padding:2rem;">
            <div class="spinner" style="margin:0 auto;"></div>
            <p>Cargando datos...</p>
        </div>
    </div>
</div>
