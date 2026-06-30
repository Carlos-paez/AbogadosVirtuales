<div class="reports-page">
    <h1>Directorio de Abogados por Estado y Jurisdiccion</h1>
    <p class="lead">Explora los abogados registrados organizados por ubicacion y especialidad.</p>

    <div class="report-filters">
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
            <button id="btnFiltrar" class="btn btn-primary">Filtrar</button>
            <button id="btnLimpiar" class="btn btn-secondary">Limpiar</button>
        </div>
    </div>

    <div id="reportResults">
        <p class="text-center text-muted">Cargando datos...</p>
    </div>
</div>
<script>
    const estados = [
        "Amazonas","Anzoategui","Apure","Aragua","Barinas","Bolivar","Carabobo","Cojedes",
        "Delta Amacuro","Distrito Capital","Falcon","Guarico","Lara","Merida","Miranda",
        "Monagas","Nueva Esparta","Portuguesa","Sucre","Tachira","Trujillo","La Guaira","Yaracuy","Zulia"
    ];
    const jurisdicciones = ["Penal","Civil","Laboral","Administrativo","Constitucional","Migratorio","Familia","Mercantil","Tributario","Derechos Humanos"];

    function populateSelects() {
        const selE = document.getElementById("filterEstado");
        const selJ = document.getElementById("filterJurisdiccion");
        estados.forEach(e => { const o = document.createElement("option"); o.value = e; o.textContent = e; selE.appendChild(o); });
        jurisdicciones.forEach(j => { const o = document.createElement("option"); o.value = j; o.textContent = j; selJ.appendChild(o); });
    }
    populateSelects();

    async function loadReport(estado, jurisdiccion) {
        const params = new URLSearchParams();
        if (estado) params.set("estado", estado);
        if (jurisdiccion) params.set("jurisdiccion", jurisdiccion);
        const resp = await fetch("/api/obtener-abogados?" + params.toString());
        const data = await resp.json();
        const container = document.getElementById("reportResults");
        if (!data.success || data.data.length === 0) {
            container.innerHTML = "<p class='text-center text-muted'>No se encontraron abogados registrados.</p>";
            return;
        }
        const grouped = {};
        data.data.forEach(l => {
            const key = l.estado || "Sin estado";
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(l);
        });
        let html = "";
        for (const [estado, list] of Object.entries(grouped).sort()) {
            html += `<div class="report-group"><h2>${estado}</h2><div class="lawyer-cards">`;
            list.forEach(l => {
                html += `<div class="lawyer-card">
                    <h3>${l.nombre}</h3>
                    <p><strong>Email:</strong> ${l.email}</p>
                    <p><strong>Telefono:</strong> ${l.telefono || "No especificado"}</p>
                    <p><strong>Ciudad:</strong> ${l.ciudad || "No especificada"}</p>
                    <p><strong>Jurisdiccion:</strong> <span class="badge">${l.jurisdiccion}</span></p>
                    <p><strong>Especialidad:</strong> ${l.especialidad || "No especificada"}</p>
                    <p class="text-muted small">Registrado: ${l.created_at}</p>
                </div>`;
            });
            html += "</div></div>";
        }
        container.innerHTML = html;
    }

    document.getElementById("btnFiltrar").addEventListener("click", () => {
        loadReport(document.getElementById("filterEstado").value, document.getElementById("filterJurisdiccion").value);
    });
    document.getElementById("btnLimpiar").addEventListener("click", () => {
        document.getElementById("filterEstado").value = "";
        document.getElementById("filterJurisdiccion").value = "";
        loadReport("", "");
    });

    loadReport("", "");
</script>
