<div class="form-page">
    <h1>Solicitar Apoyo Legal</h1>
    <p class="lead">Si eres una persona afectada por la situacion en Venezuela y necesitas asistencia legal, completa este formulario y un abogado voluntario te contactara.</p>

    <form id="formSolicitud" class="app-form" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required data-validate="required">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="email">Correo electronico *</label>
                <input type="email" id="email" name="email" required data-validate="required email">
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telefono">Telefono</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="estado">Estado donde resides *</label>
                <select id="estado" name="estado" required data-validate="required">
                    <option value="">Seleccione un estado</option>
                </select>
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad">
            </div>
            <div class="form-group">
                <label for="prioridad">Prioridad</label>
                <select id="prioridad" name="prioridad">
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Tipo de ayuda legal que necesitas</label>
            <div class="checkbox-group" id="tipoAyudaGroup">
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Asesoria migratoria"> Asesoria migratoria</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Derechos humanos"> Derechos humanos</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Derecho laboral"> Derecho laboral</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Derecho penal"> Derecho penal</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Derecho de familia"> Derecho de familia</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Derecho civil"> Derecho civil</label>
                <label class="checkbox-label"><input type="checkbox" name="tipo_ayuda" value="Otro"> Otro</label>
            </div>
        </div>
        <div class="form-group">
            <label for="descripcion">Describe tu situacion legal *</label>
            <textarea id="descripcion" name="descripcion" rows="5" required data-validate="required" placeholder="Describe brevemente tu caso o situacion legal..."></textarea>
            <span class="error-msg"></span>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg" id="btnSolicitud">
                <span class="btn-text">Enviar Solicitud</span>
                <span class="spinner" style="display:none;"></span>
            </button>
        </div>
        <div id="formMessage" class="form-message" style="display:none;"></div>
    </form>
</div>
