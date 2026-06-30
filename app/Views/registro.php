<div class="form-page">
    <h1>Registro de Abogados Voluntarios</h1>
    <p class="lead">Unete a nuestra red de profesionales del derecho para apoyar a quienes mas lo necesitan.</p>

    <form id="formRegistro" class="app-form" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required>
                <span class="error-msg"></span>
            </div>
            <div class="form-group">
                <label for="email">Correo electronico *</label>
                <input type="email" id="email" name="email" required>
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telefono">Telefono</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="estado">Estado de Venezuela *</label>
                <select id="estado" name="estado" required>
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
                <label for="jurisdiccion">Jurisdiccion *</label>
                <select id="jurisdiccion" name="jurisdiccion" required>
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
            <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
        </div>
        <div id="formMessage" class="form-message" style="display:none;"></div>
    </form>
</div>
<script>
document.getElementById("formRegistro")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    const form = e.target;
    const msg = document.getElementById("formMessage");
    const btn = form.querySelector("button[type=submit]");
    btn.disabled = true; btn.textContent = "Registrando...";
    try {
        const resp = await fetch(this.getAttribute("action") || "/api/registro-abogado", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        });
        const data = await resp.json();
        if (data.success) {
            msg.className = "form-message success";
            msg.textContent = "Registro exitoso! Gracias por unirte a la causa.";
            msg.style.display = "block";
            form.reset();
        } else {
            msg.className = "form-message error";
            msg.textContent = data.error || "Error al registrar.";
            msg.style.display = "block";
        }
    } catch (err) {
        msg.className = "form-message error";
        msg.textContent = "Error de conexion.";
        msg.style.display = "block";
    }
    btn.disabled = false; btn.textContent = "Registrarse";
});
</script>
