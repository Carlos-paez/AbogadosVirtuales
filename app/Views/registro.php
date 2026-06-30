<div class="form-page">
    <h1>Registro de Abogados Voluntarios</h1>
    <p class="lead">Unete a nuestra red de profesionales del derecho para apoyar a quienes mas lo necesitan.</p>

    <form id="formRegistro" class="app-form" novalidate>
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
                <input type="tel" id="telefono" name="telefono" data-validate="phone">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="tipo_documento">Tipo de documento</label>
                <select id="tipo_documento" name="tipo_documento">
                    <option value="V">V - Venezolano</option>
                    <option value="E">E - Extranjero</option>
                    <option value="P">P - Pasaporte</option>
                    <option value="J">J - Juridico</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="numero_documento">Numero de documento</label>
                <input type="text" id="numero_documento" name="numero_documento" data-validate="required">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="anios_experiencia">Años de experiencia</label>
                <input type="number" id="anios_experiencia" name="anios_experiencia" min="0" max="70" value="0">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="estado">Estado de Venezuela *</label>
                <select id="estado" name="estado" required data-validate="required">
                    <option value="">Seleccione un estado</option>
                </select>
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="jurisdiccion">Jurisdiccion *</label>
                <select id="jurisdiccion" name="jurisdiccion" required data-validate="required">
                    <option value="">Seleccione jurisdiccion</option>
                </select>
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-group">
            <label for="especialidad">Especialidad / Area de practica</label>
            <textarea id="especialidad" name="especialidad" rows="3" placeholder="Describe brevemente tu area de especializacion..."></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg" id="btnRegistro">
                <span class="btn-text">Registrarse</span>
                <span class="spinner" style="display:none;"></span>
            </button>
        </div>
        <div id="formMessage" class="form-message" style="display:none;"></div>
    </form>
</div>
