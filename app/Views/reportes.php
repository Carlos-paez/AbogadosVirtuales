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

<div id="modalEditLawyer" class="modal" style="display:none;">
    <div class="modal-content modal-lg">
        <span class="modal-close" id="modalEditLawyerClose">&times;</span>
        <h3>Editar Abogado</h3>
        <form id="formEditLawyer">
            <input type="hidden" id="editLawyerId" value="">
            <div class="form-row">
                <div class="form-group">
                    <label for="editNombre">Nombre completo *</label>
                    <input type="text" id="editNombre" required>
                    <span class="error-msg"></span>
                </div>
                <div class="form-group">
                    <label for="editEmail">Correo electronico *</label>
                    <input type="email" id="editEmail" required>
                    <span class="error-msg"></span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editTelefono">Telefono</label>
                    <input type="tel" id="editTelefono">
                </div>
                <div class="form-group">
                    <label for="editTipoDoc">Tipo de documento</label>
                    <select id="editTipoDoc">
                        <option value="V">V - Venezolano</option>
                        <option value="E">E - Extranjero</option>
                        <option value="J">J - Juridico</option>
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editNumDoc">Numero de documento</label>
                    <input type="text" id="editNumDoc">
                </div>
                <div class="form-group">
                    <label for="editAniosExp">Años de experiencia</label>
                    <input type="number" id="editAniosExp" min="0" max="70" value="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editEstado">Estado / Provincia *</label>
                    <select id="editEstado" required>
                        <option value="">Seleccione un estado</option>
                    </select>
                    <span class="error-msg"></span>
                </div>
                <div class="form-group">
                    <label for="editCiudad">Ciudad</label>
                    <input type="text" id="editCiudad">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editJurisdiccion">Jurisdiccion *</label>
                    <select id="editJurisdiccion" required>
                        <option value="">Seleccione jurisdiccion</option>
                    </select>
                    <span class="error-msg"></span>
                </div>
            </div>
            <div class="form-group">
                <label for="editEspecialidad">Especialidad / Area de practica</label>
                <textarea id="editEspecialidad" rows="3" placeholder="Describe brevemente tu area de especializacion..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btnEditLawyer">
                    <span class="btn-text">Guardar Cambios</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelEditLawyer">Cancelar</button>
            </div>
            <div id="msgEditLawyer" class="form-message" style="display:none;"></div>
        </form>
    </div>
</div>

<div id="modalDeleteLawyer" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="modalDeleteLawyerClose">&times;</span>
        <h3>Eliminar Abogado</h3>
        <p id="deleteLawyerText">¿Esta seguro de eliminar este abogado?</p>
        <input type="hidden" id="deleteLawyerId" value="">
        <div class="form-actions">
            <button class="btn btn-danger" id="btnConfirmDeleteLawyer">
                <span class="btn-text">Eliminar</span>
                <span class="spinner" style="display:none;"></span>
            </button>
            <button class="btn btn-secondary" id="btnCancelDeleteLawyer">Cancelar</button>
        </div>
        <div id="msgDeleteLawyer" class="form-message" style="display:none;"></div>
    </div>
</div>
