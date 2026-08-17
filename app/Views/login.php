<div class="form-page">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <p class="lead">Accede al panel con tu credencial y contraseña.</p>
        <form id="formLogin" class="app-form" novalidate>
            <div class="form-group">
                <label for="credencial">Credencial</label>
                <input type="text" id="credencial" name="credencial" placeholder="LEG-XXXXXXXX" required data-validate="required" autocomplete="username">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required data-validate="required" autocomplete="current-password">
                <span class="error-msg"></span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="btnLogin">
                    <span class="btn-text">Ingresar</span>
                    <span class="spinner" style="display:none;"></span>
                </button>
            </div>
            <div id="formMessage" class="form-message" style="display:none;"></div>
        </form>
        <p class="login-info">Si no tiene una credencial, <a href="<?= $basePath ?>/registro">regístrese aquí</a>.</p>
    </div>
</div>
