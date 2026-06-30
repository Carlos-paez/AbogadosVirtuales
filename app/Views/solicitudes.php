<div class="form-page">
    <h1>Solicitar Apoyo Legal</h1>
    <p class="lead">Si eres una persona afectada por la situacion en Venezuela y necesitas asistencia legal, completa este formulario y un abogado voluntario te contactara.</p>

    <form id="formSolicitud" class="app-form" novalidate>
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
                <label for="estado">Estado donde resides *</label>
                <select id="estado" name="estado" required>
                    <option value="">Seleccione un estado</option>
                </select>
                <span class="error-msg"></span>
            </div>
        </div>
        <div class="form-group">
            <label for="ciudad">Ciudad</label>
            <input type="text" id="ciudad" name="ciudad">
        </div>
        <div class="form-group">
            <label for="descripcion">Describe tu situacion legal *</label>
            <textarea id="descripcion" name="descripcion" rows="5" required placeholder="Describe brevemente tu caso o situacion legal..."></textarea>
            <span class="error-msg"></span>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Enviar Solicitud</button>
        </div>
        <div id="formMessage" class="form-message" style="display:none;"></div>
    </form>
</div>
<script>
document.getElementById("formSolicitud")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    const form = e.target;
    const msg = document.getElementById("formMessage");
    const btn = form.querySelector("button[type=submit]");
    btn.disabled = true; btn.textContent = "Enviando...";
    try {
        const resp = await fetch("/api/registro-afectado", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        });
        const data = await resp.json();
        if (data.success) {
            msg.className = "form-message success";
            msg.textContent = "Solicitud enviada con exito! Un abogado te contactara pronto.";
            msg.style.display = "block";
            form.reset();
        } else {
            msg.className = "form-message error";
            msg.textContent = data.error || "Error al enviar la solicitud.";
            msg.style.display = "block";
        }
    } catch (err) {
        msg.className = "form-message error";
        msg.textContent = "Error de conexion.";
        msg.style.display = "block";
    }
    btn.disabled = false; btn.textContent = "Enviar Solicitud";
});
</script>
