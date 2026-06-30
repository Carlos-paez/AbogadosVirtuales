<div class="crm-page">
    <h1>CRM - Gestion de Atencion al Cliente</h1>
    <p class="lead">Asigna, da seguimiento y genera reportes de los casos atendidos por abogados voluntarios.</p>

    <div class="crm-stats" id="crmStats">
        <div class="stat-card"><h3>Total Abogados</h3><p id="statAbogados">0</p></div>
        <div class="stat-card"><h3>Total Solicitudes</h3><p id="statPersonas">0</p></div>
        <div class="stat-card"><h3>Casos Abiertos</h3><p id="statAbiertos">0</p></div>
        <div class="stat-card"><h3>Casos Cerrados</h3><p id="statCerrados">0</p></div>
    </div>

    <div class="crm-tabs">
        <button class="tab-btn active" data-tab="asignar">Asignar Caso</button>
        <button class="tab-btn" data-tab="casos">Casos Activos</button>
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
            <div class="form-group">
                <label for="titulo">Titulo del caso</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ej: Asesoria migratoria">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles del caso..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Asignar Caso</button>
            </div>
            <div id="msgAsignar" class="form-message" style="display:none;"></div>
        </form>
    </div>

    <div class="tab-content" id="tab-casos">
        <h2>Casos Activos</h2>
        <div id="casosActivos"><p class="text-muted">Cargando...</p></div>
    </div>

    <div class="tab-content" id="tab-reporte">
        <h2>Reporte General de Casos</h2>
        <button id="btnGenerarReporte" class="btn btn-primary">Generar Reporte Completo</button>
        <div id="reporteGeneral" style="margin-top:1rem;"><p class="text-muted">Haz clic en el boton para generar el reporte.</p></div>
    </div>
</div>
<script>
    const estados = [
        "Amazonas","Anzoategui","Apure","Aragua","Barinas","Bolivar","Carabobo","Cojedes",
        "Delta Amacuro","Distrito Capital","Falcon","Guarico","Lara","Merida","Miranda",
        "Monagas","Nueva Esparta","Portuguesa","Sucre","Tachira","Trujillo","La Guaira","Yaracuy","Zulia"
    ];
    const jurisdicciones = ["Penal","Civil","Laboral","Administrativo","Constitucional","Migratorio","Familia","Mercantil","Tributario","Derechos Humanos"];

    async function loadStats() {
        const r = await (await fetch("/api/estadisticas")).json();
        if (r.success) {
            document.getElementById("statAbogados").textContent = r.data.total_abogados;
            document.getElementById("statPersonas").textContent = r.data.total_personas;
            document.getElementById("statAbiertos").textContent = r.data.casos_abiertos;
            document.getElementById("statCerrados").textContent = r.data.casos_cerrados;
        }
    }

    async function loadSelects() {
        const rLaw = await (await fetch("/api/obtener-abogados")).json();
        const rPer = await (await fetch("/api/obtener-personas")).json();
        const selLaw = document.getElementById("lawyer_id");
        const selPer = document.getElementById("person_id");
        selLaw.innerHTML = "<option value=''>Seleccione un abogado</option>";
        selPer.innerHTML = "<option value=''>Seleccione una persona</option>";
        if (rLaw.success) rLaw.data.forEach(l => {
            const o = document.createElement("option"); o.value = l.id; o.textContent = l.nombre + " (" + l.estado + " - " + l.jurisdiccion + ")"; selLaw.appendChild(o);
        });
        if (rPer.success) rPer.data.forEach(p => {
            const o = document.createElement("option"); o.value = p.id; o.textContent = p.nombre + " (" + p.estado + ")"; selPer.appendChild(o);
        });
    }

    async function loadCasos() {
        const r = await (await fetch("/api/obtener-casos")).json();
        const container = document.getElementById("casosActivos");
        if (!r.success || r.data.length === 0) {
            container.innerHTML = "<p class='text-muted'>No hay casos registrados.</p>";
            return;
        }
        let html = "<table class='crm-table'><thead><tr><th>ID</th><th>Titulo</th><th>Abogado</th><th>Persona</th><th>Estado</th><th>Asignado</th><th>Accion</th></tr></thead><tbody>";
        r.data.forEach(c => {
            const statusClass = c.estado === "cerrado" ? "badge-success" : "badge-warning";
            html += `<tr>
                <td>${c.id}</td>
                <td>${c.titulo || "Sin titulo"}</td>
                <td>${c.abogado_nombre || "N/A"}</td>
                <td>${c.persona_nombre || "N/A"}</td>
                <td><span class="badge ${statusClass}">${c.estado}</span></td>
                <td class="small">${c.assigned_at}</td>
                <td class="actions">`;
            if (c.estado === "abierto") {
                html += `<button class="btn btn-sm btn-success" onclick="cerrarCaso(${c.id})">Cerrar</button> `;
            }
            html += `<button class="btn btn-sm btn-danger" onclick="eliminarCaso(${c.id})">Eliminar</button>`;
            html += `</td></tr>`;
        });
        html += "</tbody></table>";
        container.innerHTML = html;
    }

    async function cerrarCaso(id) {
        const r = await (await fetch("/api/cerrar-caso", { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify({id}) })).json();
        if (r.success) { loadCasos(); loadStats(); } else { alert(r.error || "Error al cerrar caso"); }
    }

    async function eliminarCaso(id) {
        if (!confirm("Eliminar este caso definitivamente?")) return;
        const r = await (await fetch("/api/eliminar-caso", { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify({id}) })).json();
        if (r.success) { loadCasos(); loadStats(); } else { alert(r.error || "Error al eliminar"); }
    }

    document.getElementById("formAsignar")?.addEventListener("submit", async function(e) {
        e.preventDefault();
        const msg = document.getElementById("msgAsignar");
        const btn = this.querySelector("button[type=submit]");
        btn.disabled = true; btn.textContent = "Asignando...";
        try {
            const r = await fetch("/api/asignar-caso", { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify(Object.fromEntries(new FormData(this))) });
            const d = await r.json();
            if (d.success) {
                msg.className = "form-message success"; msg.textContent = "Caso asignado con exito."; msg.style.display = "block";
                this.reset(); loadCasos(); loadStats(); loadSelects();
            } else {
                msg.className = "form-message error"; msg.textContent = d.error || "Error."; msg.style.display = "block";
            }
        } catch(err) {
            msg.className = "form-message error"; msg.textContent = "Error de conexion."; msg.style.display = "block";
        }
        btn.disabled = false; btn.textContent = "Asignar Caso";
    });

    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
            this.classList.add("active");
            document.getElementById("tab-" + this.dataset.tab).classList.add("active");
        });
    });

    document.getElementById("btnGenerarReporte")?.addEventListener("click", async function() {
        this.disabled = true; this.textContent = "Generando...";
        const r = await (await fetch("/api/estadisticas")).json();
        const casos = await (await fetch("/api/obtener-casos")).json();
        const container = document.getElementById("reporteGeneral");
        if (!r.success) { container.innerHTML = "<p>Error al generar reporte.</p>"; this.disabled = false; this.textContent = "Generar Reporte Completo"; return; }
        const s = r.data;
        let html = `<div class="reporte-output">
            <h3>Estadisticas Generales</h3>
            <table class="crm-table">
                <tr><td>Total de abogados registrados</td><td><strong>${s.total_abogados}</strong></td></tr>
                <tr><td>Total de personas solicitantes</td><td><strong>${s.total_personas}</strong></td></tr>
                <tr><td>Casos abiertos</td><td><strong>${s.casos_abiertos}</strong></td></tr>
                <tr><td>Casos cerrados</td><td><strong>${s.casos_cerrados}</strong></td></tr>
                <tr><td>Total de casos</td><td><strong>${s.total_casos}</strong></td></tr>
            </table>`;
        if (casos.success && casos.data.length > 0) {
            const porAbogado = {};
            casos.data.forEach(c => {
                const nom = c.abogado_nombre || "Sin asignar";
                if (!porAbogado[nom]) porAbogado[nom] = { abiertos:0, cerrados:0, total:0, casos:[] };
                porAbogado[nom].total++;
                if (c.estado === "cerrado") porAbogado[nom].cerrados++; else porAbogado[nom].abiertos++;
                porAbogado[nom].casos.push(c);
            });
            html += `<h3 style="margin-top:2rem;">Reporte por Abogado</h3>`;
            for (const [nom, data] of Object.entries(porAbogado)) {
                const tasa = data.total > 0 ? Math.round((data.cerrados / data.total) * 100) : 0;
                html += `<div class="reporte-abogado">
                    <h4>${nom}</h4>
                    <p>Casos totales: ${data.total} | Abiertos: ${data.abiertos} | Cerrados: ${data.cerrados} | Tasa de resolucion: ${tasa}%</p>
                    <table class="crm-table small">
                        <thead><tr><th>ID</th><th>Titulo</th><th>Persona</th><th>Estado</th><th>Asignado</th></tr></thead><tbody>`;
                data.casos.forEach(c => {
                    html += `<tr><td>${c.id}</td><td>${c.titulo || "Sin titulo"}</td><td>${c.persona_nombre || "N/A"}</td><td>${c.estado}</td><td>${c.assigned_at}</td></tr>`;
                });
                html += `</tbody></table></div>`;
            }
        }
        html += '</div>';
        container.innerHTML = html;
        this.disabled = false; this.textContent = "Generar Reporte Completo";
    });

    loadStats(); loadSelects(); loadCasos();
</script>
