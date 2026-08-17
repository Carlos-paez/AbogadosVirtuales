<div class="form-page">
    <h1>Registro de Usuarios</h1>
    <p class="lead">Regístrate para acceder al sistema de apoyo legal. Se generará una credencial única para tu cuenta.</p>

    <form id="formRegistro" class="app-form" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required data-validate="required">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" required data-validate="required email">
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" data-validate="phone">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="tipo_documento">Tipo de documento</label>
                <select id="tipo_documento" name="tipo_documento">
                    <option value="V">V - Venezolano</option>
                    <option value="E">E - Extranjero</option>
                    <option value="J">J - Jurídico</option>
                    <option value="Pasaporte">Pasaporte</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="numero_documento">Número de documento *</label>
                <input type="text" id="numero_documento" name="numero_documento" required data-validate="required">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="area_id">Área de desempeño *</label>
                <select id="area_id" name="area_id" required data-validate="required">
                    <option value="">Seleccione un área</option>
                    <?php foreach (($areas ?? []) as $area): ?>
                    <option value="<?= (int)$area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="pais">País *</label>
                <select id="pais" name="pais" required data-validate="required">
                    <option value="Venezuela">Venezuela</option>
                    <option value="Otro">Otro país</option>
                </select>
                <span class="error-msg"></span>
            </div>
            <div class="form-group" id="paisOtroGroup" style="display:none;">
                <label for="pais_otro">Nombre del país *</label>
                <input type="text" id="pais_otro" name="pais_otro" data-validate="required">
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" id="estadoGroup">
                <label for="estado">Estado / Provincia *</label>
                <select id="estado" name="estado" required data-validate="required">
                    <option value="">Seleccione un estado</option>
                </select>
                <input type="text" id="estadoInput" name="estadoInput" style="display:none;" data-validate="required" placeholder="Escriba su estado / provincia">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="jurisdiccion">Jurisdicción *</label>
                <select id="jurisdiccion" name="jurisdiccion" required data-validate="required">
                    <option value="">Seleccione jurisdicción</option>
                </select>
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="anios_experiencia">Años de experiencia</label>
                <input type="number" id="anios_experiencia" name="anios_experiencia" min="0" max="70" value="0">
            </div>
        </div>
        <div class="form-group">
            <label for="especialidad">Especialidad / Área de práctica</label>
            <textarea id="especialidad" name="especialidad" rows="2" placeholder="Describe brevemente tu área de especialización..."></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required data-validate="required" minlength="6" autocomplete="new-password">
                <span class="error-msg"></span>
                <small class="field-help">Mínimo 6 caracteres, una mayúscula y un número.</small>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar contraseña *</label>
                <input type="password" id="password_confirm" name="password_confirm" required data-validate="required" autocomplete="new-password">
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg" id="btnRegistro">
                <span class="btn-text">Registrarse</span>
                <span class="spinner" style="display:none;"></span>
            </button>
        </div>
        <div id="formMessage" class="form-message" style="display:none;"></div>
    </form>

    <div id="credentialModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="modal-close" id="credentialModalClose">&times;</span>
            <div style="text-align:center;padding:1rem 0;">
                <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
                <h2 style="color:var(--secondary);margin-bottom:0.5rem;">Registro Exitoso</h2>
                <p style="margin-bottom:1.5rem;">Tu cuenta ha sido creada correctamente.</p>
                <div style="background:#f0fdf4;border:2px solid var(--secondary);border-radius:var(--radius-sm);padding:1.5rem;margin:1rem 0;">
                    <p style="margin-bottom:0.5rem;font-weight:600;color:var(--text);">Tu credencial de acceso es:</p>
                    <p id="credentialValue" style="font-size:1.8rem;font-weight:800;color:var(--primary);letter-spacing:0.1em;margin:0.5rem 0;font-family:monospace;"></p>
                    <p id="credentialArea" style="font-size:0.9rem;color:var(--text-secondary);margin:0;"></p>
                </div>
                <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:var(--radius-sm);padding:1rem;margin:1rem 0;">
                    <p style="color:#92400e;font-weight:600;margin-bottom:0.3rem;">⚠️ Importante</p>
                    <p style="color:#92400e;font-size:0.85rem;margin:0;">Guarda esta credencial en un lugar seguro. La necesitarás para iniciar sesión. No podrás recuperarla si la pierdes.</p>
                </div>
                <a href="<?= $basePath ?>/login" class="btn btn-primary btn-lg" style="margin-top:1rem;">Ir a Iniciar Sesión</a>
            </div>
        </div>
    </div>
</div>
