<div class="form-page">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <p class="lead">Accede al panel de gestión y reportes.</p>
        <form id="formLogin" class="app-form" novalidate>
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required data-validate="required">
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required data-validate="required">
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
        <p class="login-info">Usuario por defecto: <strong>admin</strong> — Contraseña: <strong>admin</strong></p>
    </div>
</div>
