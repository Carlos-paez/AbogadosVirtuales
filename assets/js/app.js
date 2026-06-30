;(function() {
    'use strict';

    var basePath = (document.querySelector('base') || {}).href || '/';

    /* ── Nav toggle ── */
    var navToggle = document.getElementById('navToggle');
    var navMenu = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('show');
            }
        });
    }

    /* ── Navigation active state ── */
    var currentPath = location.pathname.replace(/\/+$/, '') || '/';
    document.querySelectorAll('.nav-menu a').forEach(function(a) {
        var href = a.getAttribute('href').replace(/\/+$/, '') || '/';
        if (href === currentPath) a.classList.add('active');
    });

    /* ── Static data ── */
    var estados = [
        'Amazonas', 'Anzoategui', 'Apure', 'Aragua', 'Barinas', 'Bolivar', 'Carabobo', 'Cojedes',
        'Delta Amacuro', 'Distrito Capital', 'Falcon', 'Guarico', 'Lara', 'Merida', 'Miranda',
        'Monagas', 'Nueva Esparta', 'Portuguesa', 'Sucre', 'Tachira', 'Trujillo', 'La Guaira', 'Yaracuy', 'Zulia'
    ];
    var jurisdicciones = [
        'Penal', 'Civil', 'Laboral', 'Administrativo', 'Constitucional',
        'Migratorio', 'Familia', 'Mercantil', 'Tributario', 'Derechos Humanos'
    ];

    function populateSelect(selId, values) {
        var sel = document.getElementById(selId);
        if (!sel) return;
        values.forEach(function(v) {
            var o = document.createElement('option');
            o.value = v;
            o.textContent = v;
            sel.appendChild(o);
        });
    }

    document.querySelectorAll('select[id="estado"]').forEach(function(sel) {
        estados.forEach(function(e) {
            var o = document.createElement('option');
            o.value = e;
            o.textContent = e;
            sel.appendChild(o);
        });
    });
    document.querySelectorAll('select[id="jurisdiccion"]').forEach(function(sel) {
        jurisdicciones.forEach(function(j) {
            var o = document.createElement('option');
            o.value = j;
            o.textContent = j;
            sel.appendChild(o);
        });
    });

    /* ── Toast system ── */
    var toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);

    function showToast(text, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = text;
        toastContainer.appendChild(t);
        setTimeout(function() {
            t.classList.add('removing');
            setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 300);
        }, 4000);
    }

    /* ── Live validation ── */
    var validationRules = {
        required: function(v) { return v.trim() !== ''; },
        email: function(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); },
        phone: function(v) { return v === '' || /^[\d\s()+\-]{7,20}$/.test(v.trim()); }
    };
    var errorMessages = {
        required: 'Este campo es obligatorio.',
        email: 'Ingrese un correo electronico valido.',
        phone: 'Ingrese un numero de telefono valido (7-20 digitos).'
    };

    function validateField(field) {
        var rules = (field.getAttribute('data-validate') || '').split(/\s+/);
        var value = field.value;
        var errSpan = field.closest('.form-group').querySelector('.error-msg');
        if (!errSpan) return true;
        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            if (!rule) continue;
            var fn = validationRules[rule];
            if (fn && !fn(value)) {
                errSpan.textContent = errorMessages[rule] || 'Valor invalido.';
                field.classList.remove('validation-success');
                field.classList.add('validation-error');
                return false;
            }
        }
        errSpan.textContent = '';
        field.classList.remove('validation-error');
        field.classList.add('validation-success');
        return true;
    }

    function attachValidation(form) {
        form.querySelectorAll('[data-validate]').forEach(function(field) {
            field.addEventListener('blur', function() { validateField(field); });
            field.addEventListener('input', function() {
                var errSpan = field.closest('.form-group').querySelector('.error-msg');
                if (errSpan) errSpan.textContent = '';
                field.classList.remove('validation-error', 'validation-success');
            });
        });
    }

    /* ── Form valid on submit (basic required check) ── */
    document.querySelectorAll('.app-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var required = form.querySelectorAll('[required]');
            var valid = true;
            required.forEach(function(field) {
                var error = field.closest('.form-group').querySelector('.error-msg');
                if (!field.value.trim()) {
                    if (error) error.textContent = 'Este campo es obligatorio.';
                    field.style.borderColor = '#ef4444';
                    valid = false;
                } else {
                    if (error) error.textContent = '';
                    field.style.borderColor = '';
                }
                if (field.type === 'email' && field.value.trim()) {
                    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(field.value.trim())) {
                        if (error) error.textContent = 'Ingrese un email valido.';
                        field.style.borderColor = '#ef4444';
                        valid = false;
                    }
                }
            });
            if (!valid) e.preventDefault();
        });
        form.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.addEventListener('input', function() {
                var error = field.closest('.form-group').querySelector('.error-msg');
                if (error) error.textContent = '';
                field.style.borderColor = '';
            });
        });
    });

    /* ── Spinner helpers ── */
    function showLoading(btn) {
        var text = btn.querySelector('.btn-text');
        var spinner = btn.querySelector('.spinner');
        if (text) text.style.opacity = '0';
        if (spinner) spinner.style.display = 'inline-block';
        btn.disabled = true;
    }
    function hideLoading(btn) {
        var text = btn.querySelector('.btn-text');
        var spinner = btn.querySelector('.spinner');
        if (text) text.style.opacity = '1';
        if (spinner) spinner.style.display = 'none';
        btn.disabled = false;
    }

    /* ── Form message helper ── */
    function showFormMsg(el, text, type) {
        el.style.display = 'block';
        el.className = 'form-message ' + (type || 'info');
        el.textContent = text;
        if (type === 'success') {
            setTimeout(function() { el.style.display = 'none'; }, 5000);
        }
    }

    /* ── CSV export ── */
    function downloadCSV(data, filename) {
        if (!data || !data.length) return;
        var keys = Object.keys(data[0]);
        var rows = [keys.join(',')];
        data.forEach(function(row) {
            var vals = keys.map(function(k) {
                var v = (row[k] || '').toString();
                if (v.includes(',') || v.includes('"') || v.includes('\n')) {
                    return '"' + v.replace(/"/g, '""') + '"';
                }
                return v;
            });
            rows.push(vals.join(','));
        });
        var bom = '\uFEFF';
        var csvContent = bom + rows.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /* ── Animated counter ── */
    function animateCounter(el, target, duration) {
        if (!el) return;
        var start = 0;
        var steps = 30;
        var increment = target / steps;
        var stepTime = (duration || 600) / steps;
        function tick() {
            start += increment;
            if (start >= target) { el.textContent = target; return; }
            el.textContent = Math.round(start);
            setTimeout(tick, stepTime);
        }
        el.textContent = '0';
        tick();
    }

    /* ── API helpers (fetch) ── */
    function api(method, url, body, cb) {
        var opts = {
            method: method,
            headers: { 'Accept': 'application/json' }
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json;charset=UTF-8';
            opts.body = JSON.stringify(body);
        }
        fetch(url, opts)
            .then(function(r) { return r.json().catch(function() { return null; }).then(function(d) { return { data: d, resp: r }; }); })
            .then(function(o) {
                if (cb) cb(null, o.data, o.resp);
            })
            .catch(function(err) {
                if (cb) cb(err, null, null);
            });
    }
    function apiGet(url, cb) { api('GET', url, null, cb); }

    /* ── Skeleton loader ── */
    function skeletonCards(count) {
        var html = '';
        for (var i = 0; i < count; i++) {
            html += '<div class="skeleton skeleton-card"></div>';
        }
        return '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem;">' + html + '</div>';
    }

    function skeletonTable() {
        return '<div class="skeleton" style="height:200px;border-radius:8px;"></div>';
    }

    /* ================================================================
       REGISTRO PAGE
       ================================================================ */
    (function() {
        var form = document.getElementById('formRegistro');
        if (!form) return;
        attachValidation(form);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var valid = true;
            form.querySelectorAll('[data-validate]').forEach(function(f) { if (!validateField(f)) valid = false; });
            form.querySelectorAll('[required]').forEach(function(f) {
                if (!f.hasAttribute('data-validate')) {
                    var err = f.closest('.form-group').querySelector('.error-msg');
                    if (!f.value.trim()) {
                        if (err) err.textContent = 'Este campo es obligatorio.';
                        f.style.borderColor = '#ef4444';
                        valid = false;
                    }
                }
            });
            if (!valid) return;

            var btn = document.getElementById('btnRegistro');
            var msg = document.getElementById('formMessage');
            showLoading(btn);
            msg.style.display = 'none';

            var data = {
                nombre: document.getElementById('nombre').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                tipo_documento: document.getElementById('tipo_documento').value,
                numero_documento: document.getElementById('numero_documento').value,
                anios_experiencia: parseInt(document.getElementById('anios_experiencia').value, 10) || 0,
                estado: document.getElementById('estado').value,
                ciudad: document.getElementById('ciudad').value,
                jurisdiccion: document.getElementById('jurisdiccion').value,
                especialidad: document.getElementById('especialidad').value
            };

            api('POST', basePath + 'api/registro-abogado', data, function(err, res) {
                hideLoading(btn);
                if (res && res.success) {
                    showFormMsg(msg, res.message || 'Registro exitoso.', 'success');
                    showToast(res.message || 'Registro exitoso.', 'success');
                    form.reset();
                    form.querySelectorAll('.validation-success').forEach(function(f) { f.classList.remove('validation-success'); });
                } else {
                    var m = res ? res.message : 'Error de conexion.';
                    showFormMsg(msg, m, 'error');
                    showToast(m, 'error');
                }
            });
        });
    })();

    /* ================================================================
       SOLICITUDES PAGE
       ================================================================ */
    (function() {
        var form = document.getElementById('formSolicitud');
        if (!form) return;
        attachValidation(form);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var valid = true;
            form.querySelectorAll('[data-validate]').forEach(function(f) { if (!validateField(f)) valid = false; });
            form.querySelectorAll('[required]').forEach(function(f) {
                if (!f.hasAttribute('data-validate')) {
                    var err = f.closest('.form-group').querySelector('.error-msg');
                    if (!f.value.trim()) {
                        if (err) err.textContent = 'Este campo es obligatorio.';
                        f.style.borderColor = '#ef4444';
                        valid = false;
                    }
                }
            });
            if (!valid) return;

            var btn = document.getElementById('btnSolicitud');
            var msg = document.getElementById('formMessage');
            showLoading(btn);
            msg.style.display = 'none';

            var tipoAyuda = [];
            document.querySelectorAll('#tipoAyudaGroup input[name="tipo_ayuda"]:checked').forEach(function(cb) {
                tipoAyuda.push(cb.value);
            });

            var data = {
                nombre: document.getElementById('nombre').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                estado: document.getElementById('estado').value,
                ciudad: document.getElementById('ciudad').value,
                prioridad: document.getElementById('prioridad').value,
                tipo_ayuda: tipoAyuda,
                descripcion: document.getElementById('descripcion').value
            };

            api('POST', basePath + 'api/registro-afectado', data, function(err, res) {
                hideLoading(btn);
                if (res && res.success) {
                    showFormMsg(msg, res.message || 'Solicitud enviada.', 'success');
                    showToast(res.message || 'Solicitud enviada.', 'success');
                    form.reset();
                    form.querySelectorAll('.validation-success').forEach(function(f) { f.classList.remove('validation-success'); });
                } else {
                    var m = res ? res.message : 'Error de conexion.';
                    showFormMsg(msg, m, 'error');
                    showToast(m, 'error');
                }
            });
        });
    })();

    /* ================================================================
       REPORTES PAGE
       ================================================================ */
    (function() {
        var container = document.getElementById('reportResults');
        if (!container) return;

        var searchInput = document.getElementById('searchText');
        var filterEstado = document.getElementById('filterEstado');
        var filterJurisdiccion = document.getElementById('filterJurisdiccion');
        var btnFiltrar = document.getElementById('btnFiltrar');
        var btnLimpiar = document.getElementById('btnLimpiar');
        var btnExport = document.getElementById('btnExportCSV');
        var summary = document.getElementById('reportSummary');

        populateSelect('filterEstado', estados);
        populateSelect('filterJurisdiccion', jurisdicciones);

        function buildQuery() {
            var params = [];
            var q = searchInput ? searchInput.value.trim() : '';
            if (q) params.push('q=' + encodeURIComponent(q));
            var est = filterEstado ? filterEstado.value : '';
            if (est) params.push('estado=' + encodeURIComponent(est));
            var jur = filterJurisdiccion ? filterJurisdiccion.value : '';
            if (jur) params.push('jurisdiccion=' + encodeURIComponent(jur));
            return params.length ? '?' + params.join('&') : '';
        }

        function renderAbogados(data) {
            if (!data || !data.length) {
                container.innerHTML = '<div class="empty-state"><div class="empty-icon">&#128269;</div><p>No se encontraron abogados.</p></div>';
                if (summary) summary.style.display = 'none';
                return;
            }

            var total = data.length;
            if (summary) {
                summary.style.display = 'block';
                summary.textContent = 'Se encontraron ' + total + ' abogado(s).';
            }

            var grouped = {};
            data.forEach(function(a) {
                var key = a.estado || 'Sin estado';
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(a);
            });

            var html = '';
            Object.keys(grouped).sort().forEach(function(est) {
                html += '<div class="report-group"><h2>' + esc(est) + '</h2><div class="lawyer-cards">';
                grouped[est].forEach(function(a) {
                    html += '<div class="lawyer-card">' +
                        '<h3>' + esc(a.nombre) + '</h3>' +
                        '<p><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:text-bottom;margin-right:4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>' + esc(a.email) + '</p>' +
                        (a.telefono ? '<p><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:text-bottom;margin-right:4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>' + esc(a.telefono) + '</p>' : '') +
                        (a.especialidad ? '<p><strong>Especialidad:</strong> ' + esc(a.especialidad) + '</p>' : '') +
                        '<p style="margin-top:0.5rem;"><span class="badge">' + esc(a.jurisdiccion) + '</span>' +
                        (a.anios_experiencia > 0 ? ' <span class="badge badge-info">' + a.anios_experiencia + ' años</span>' : '') + '</p>' +
                        '</div>';
                });
                html += '</div></div>';
            });
            container.innerHTML = html;
        }

        function loadData() {
            container.innerHTML = skeletonCards(4);
            apiGet(basePath + 'api/obtener-abogados' + buildQuery(), function(err, res) {
                if (res && res.success) {
                    renderAbogados(res.data || []);
                } else {
                    container.innerHTML = '<div class="empty-state"><div class="empty-icon">&#9888;</div><p>Error al cargar datos.</p></div>';
                }
            });
        }

        if (btnFiltrar) btnFiltrar.addEventListener('click', loadData);
        if (btnLimpiar) btnLimpiar.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (filterEstado) filterEstado.value = '';
            if (filterJurisdiccion) filterJurisdiccion.value = '';
            loadData();
        });
        if (btnExport) btnExport.addEventListener('click', function() {
            showToast('Exportando CSV...', 'info');
            apiGet(basePath + 'api/exportar-abogados' + buildQuery(), function(err, res) {
                if (res && res.success && res.data) {
                    downloadCSV(res.data, 'abogados');
                    showToast('CSV exportado exitosamente.', 'success');
                } else {
                    showToast('Error al exportar CSV.', 'error');
                }
            });
        });
        if (searchInput) {
            var debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadData, 350);
            });
        }

        loadData();

        function esc(s) { return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    })();

    /* ================================================================
       CRM PAGE
       ================================================================ */
    (function() {
        var crmPage = document.querySelector('.crm-page');
        if (!crmPage) return;

        var statAbogados = document.getElementById('statAbogados');
        var statPersonas = document.getElementById('statPersonas');
        var statAbiertos = document.getElementById('statAbiertos');
        var statCerrados = document.getElementById('statCerrados');
        var progressFill = document.getElementById('progressFill');
        var statBarContainer = document.getElementById('statBarContainer');
        var statPrioridad = document.getElementById('statPrioridad');
        var prioridadChart = document.getElementById('prioridadChart');
        var statTopAbogados = document.getElementById('statTopAbogados');
        var topAbogadosList = document.getElementById('topAbogadosList');

        var editAbogadoSelect = document.getElementById('editAbogado');

        /* Tabs */
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
                document.querySelectorAll('.tab-content').forEach(function(t) { t.classList.remove('active'); });
                btn.classList.add('active');
                var tabId = 'tab-' + btn.getAttribute('data-tab');
                var tab = document.getElementById(tabId);
                if (tab) tab.classList.add('active');
            });
        });

        /* ── Shared helper: load lawyer select ── */
        function populateLawyerSelect(sel, selectedId) {
            if (!sel) return;
            apiGet(basePath + 'api/obtener-abogados', function(err, res) {
                if (res && res.success) {
                    sel.innerHTML = '<option value="">Seleccionar abogado</option>';
                    (res.data || []).forEach(function(a) {
                        var o = document.createElement('option');
                        o.value = a.id;
                        o.textContent = (a.nombre || '') + ' - ' + (a.jurisdiccion || '');
                        if (selectedId && parseInt(a.id, 10) === parseInt(selectedId, 10)) o.selected = true;
                        sel.appendChild(o);
                    });
                }
            });
        }

        /* ── Stats ── */
        var statCache = null;
        function loadStats() {
            apiGet(basePath + 'api/estadisticas', function(err, res) {
                if (res && res.success) {
                    var s = res.data || {};
                    statCache = s;
                    animateCounter(statAbogados, s.total_abogados || 0, 500);
                    animateCounter(statPersonas, s.total_personas || 0, 500);
                    animateCounter(statAbiertos, s.casos_abiertos || 0, 500);
                    animateCounter(statCerrados, s.casos_cerrados || 0, 500);

                    var total = (s.casos_abiertos || 0) + (s.casos_cerrados || 0);
                    if (statBarContainer && total > 0) {
                        statBarContainer.style.display = 'block';
                        var pct = Math.round((s.casos_cerrados / total) * 100);
                        if (progressFill) {
                            setTimeout(function() {
                                progressFill.style.width = pct + '%';
                                progressFill.textContent = pct + '% Completado';
                            }, 200);
                        }
                    } else if (statBarContainer) {
                        statBarContainer.style.display = 'none';
                    }

                    renderPrioridadChart(s.por_prioridad);
                    renderTopAbogados(s.por_abogado);
                }
            });
        }

        function renderPrioridadChart(porPrioridad) {
            if (!prioridadChart || !porPrioridad || !porPrioridad.length) {
                if (statPrioridad) statPrioridad.style.display = 'none';
                return;
            }
            if (statPrioridad) statPrioridad.style.display = 'block';
            var maxVal = 1;
            porPrioridad.forEach(function(p) { if (p.total > maxVal) maxVal = parseInt(p.total, 10); });
            var colors = { urgente: '#ef4444', alta: '#f59e0b', media: '#3b82f6', baja: '#10b981' };
            var labels = { urgente: 'Urgente', alta: 'Alta', media: 'Media', baja: 'Baja' };
            var order = ['urgente', 'alta', 'media', 'baja'];
            var html = '';
            order.forEach(function(key) {
                var found = null;
                porPrioridad.forEach(function(p) { if (p.prioridad === key) found = p; });
                var val = found ? parseInt(found.total, 10) : 0;
                var pct = maxVal > 0 ? (val / maxVal) * 100 : 0;
                html += '<div class="chart-bar-item"><div class="chart-bar" style="height:60px;">' +
                    '<div class="chart-bar-fill" style="height:' + pct + '%;background:' + (colors[key] || '#94a3b8') + ';"></div></div>' +
                    '<div class="chart-bar-value">' + val + '</div>' +
                    '<div class="chart-bar-label">' + (labels[key] || key) + '</div></div>';
            });
            prioridadChart.innerHTML = html;
        }

        function renderTopAbogados(porAbogado) {
            if (!topAbogadosList || !porAbogado || !porAbogado.length) {
                if (statTopAbogados) statTopAbogados.style.display = 'none';
                return;
            }
            if (statTopAbogados) statTopAbogados.style.display = 'block';
            var maxVal = 1;
            porAbogado.forEach(function(a) { var t = parseInt(a.total, 10); if (t > maxVal) maxVal = t; });
            var html = '';
            porAbogado.forEach(function(a) {
                var total = parseInt(a.total, 10);
                var pct = maxVal > 0 ? (total / maxVal) * 100 : 0;
                html += '<div class="top-abogado-row"><span class="top-abogado-name">' + esc(a.nombre || 'Sin nombre') + '</span>' +
                    '<span class="top-abogado-count">' + total + '</span>' +
                    '<div class="top-abogado-bar"><div class="top-abogado-fill" style="width:' + pct + '%;"></div></div></div>';
            });
            topAbogadosList.innerHTML = html;
        }

        /* ── Assign form ── */
        var personSelect = document.getElementById('person_id');
        var lawyerSelect = document.getElementById('lawyer_id');

        function loadPersonSelect() {
            apiGet(basePath + 'api/obtener-personas', function(err, res) {
                if (res && res.success && personSelect) {
                    personSelect.innerHTML = '<option value="">Seleccione una persona</option>';
                    (res.data || []).forEach(function(p) {
                        var o = document.createElement('option');
                        o.value = p.id;
                        o.textContent = (p.nombre || '') + ' - ' + (p.ciudad || p.estado || '');
                        personSelect.appendChild(o);
                    });
                }
            });
        }

        function loadLawyerSelect() {
            populateLawyerSelect(lawyerSelect);
        }

        var formAsignar = document.getElementById('formAsignar');
        if (formAsignar) {
            formAsignar.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = formAsignar.querySelector('button[type="submit"]');
                var msg = document.getElementById('msgAsignar');
                showLoading(btn);
                msg.style.display = 'none';

                var data = {
                    person_id: parseInt(document.getElementById('person_id').value, 10),
                    lawyer_id: parseInt(document.getElementById('lawyer_id').value, 10),
                    titulo: document.getElementById('titulo').value,
                    prioridad: document.getElementById('prioridad_asignar').value,
                    descripcion: document.getElementById('descripcion_asignar').value
                };

                if (!data.person_id || !data.lawyer_id) {
                    showFormMsg(msg, 'Debe seleccionar una persona y un abogado.', 'error');
                    hideLoading(btn);
                    return;
                }

                api('POST', basePath + 'api/asignar-caso', data, function(err, res) {
                    hideLoading(btn);
                    if (res && res.success) {
                        showFormMsg(msg, res.message || 'Caso asignado exitosamente.', 'success');
                        showToast(res.message || 'Caso asignado.', 'success');
                        formAsignar.reset();
                        loadStats();
                        loadCasos();
                    } else {
                        var m = res ? res.message : 'Error de conexion.';
                        showFormMsg(msg, m, 'error');
                        showToast(m, 'error');
                    }
                });
            });
        }

        /* ── Cases list ── */
        var casosContainer = document.getElementById('casosActivos');
        var filterCasosSearch = document.getElementById('filterCasosSearch');
        var filterCasosEstado = document.getElementById('filterCasosEstado');
        var filterCasosPrioridad = document.getElementById('filterCasosPrioridad');
        var btnFiltrarCasos = document.getElementById('btnFiltrarCasos');
        var btnLimpiarCasos = document.getElementById('btnLimpiarCasos');
        var btnExportCasos = document.getElementById('btnExportCasos');

        function buildCasosQuery() {
            var params = [];
            var q = filterCasosSearch ? filterCasosSearch.value.trim() : '';
            if (q) params.push('q=' + encodeURIComponent(q));
            var est = filterCasosEstado ? filterCasosEstado.value : '';
            if (est) params.push('estado=' + encodeURIComponent(est));
            var pri = filterCasosPrioridad ? filterCasosPrioridad.value : '';
            if (pri) params.push('prioridad=' + encodeURIComponent(pri));
            return params.length ? '?' + params.join('&') : '';
        }

        function loadCasos() {
            if (!casosContainer) return;
            casosContainer.innerHTML = skeletonTable();
            apiGet(basePath + 'api/obtener-casos' + buildCasosQuery(), function(err, res) {
                if (!res || !res.success) {
                    casosContainer.innerHTML = '<div class="empty-state"><div class="empty-icon">&#9888;</div><p>Error al cargar casos.</p></div>';
                    return;
                }
                var data = res.data || [];
                if (!data.length) {
                    casosContainer.innerHTML = '<div class="empty-state"><div class="empty-icon">&#128203;</div><p>No hay casos registrados.</p></div>';
                    return;
                }
                var html = '<div style="overflow-x:auto;"><table class="crm-table"><thead><tr>' +
                    '<th>ID</th><th>Titulo</th><th>Persona</th><th>Abogado</th>' +
                    '<th>Prioridad</th><th>Estado</th><th>Creado</th><th>Acciones</th>' +
                    '</tr></thead><tbody>';
                data.forEach(function(c) {
                    var prioridadBadge = c.prioridad === 'urgente' ? 'badge-warning' : (c.prioridad === 'alta' ? 'badge-warning' : 'badge-info');
                    var estadoBadge = c.estado === 'cerrado' ? 'badge-success' : 'badge-info';
                    html += '<tr>' +
                        '<td><strong>' + c.id + '</strong></td>' +
                        '<td><a href="#" class="ver-detalle" data-id="' + c.id + '" data-tooltip="Ver detalle">' + esc(c.titulo || 'Sin titulo') + '</a></td>' +
                        '<td>' + esc(c.persona_nombre || '') + '</td>' +
                        '<td>' + esc(c.abogado_nombre || '') + '</td>' +
                        '<td><span class="badge ' + prioridadBadge + '">' + esc(c.prioridad || 'media') + '</span></td>' +
                        '<td><span class="badge ' + estadoBadge + '">' + esc(c.estado || 'abierto') + '</span></td>' +
                        '<td class="small">' + (c.created_at || '') + '</td>' +
                        '<td class="actions">' +
                        '<button class="btn btn-sm btn-primary btn-editar" data-id="' + c.id + '" data-tooltip="Editar caso">Editar</button> ';
                    if (c.estado !== 'cerrado') {
                        html += '<button class="btn btn-sm btn-success btn-cerrar" data-id="' + c.id + '" data-tooltip="Cerrar caso">Cerrar</button> ';
                    } else {
                        html += '<button class="btn btn-sm btn-secondary btn-reabrir" data-id="' + c.id + '" data-tooltip="Reabrir caso">Reabrir</button> ';
                    }
                    html += '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' + c.id + '" data-tooltip="Eliminar caso">Eliminar</button>';
                    html += '</td></tr>';
                });
                html += '</tbody></table></div>';
                casosContainer.innerHTML = html;

                casosContainer.querySelectorAll('.ver-detalle').forEach(function(a) {
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        verDetalle(this.getAttribute('data-id'));
                    });
                });

                casosContainer.querySelectorAll('.btn-editar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        abrirEdicion(this.getAttribute('data-id'));
                    });
                });

                casosContainer.querySelectorAll('.btn-cerrar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.getElementById('closeCaseId').value = this.getAttribute('data-id');
                        document.getElementById('modalCloseCase').style.display = 'flex';
                    });
                });

                casosContainer.querySelectorAll('.btn-reabrir').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var caseId = this.getAttribute('data-id');
                        if (!confirm('Reabrir este caso?')) return;
                        api('POST', basePath + 'api/reabrir-caso', { id: parseInt(caseId, 10) }, function(err, res) {
                            if (res && res.success) {
                                showToast(res.message || 'Caso reabierto.', 'success');
                                loadCasos();
                                loadStats();
                            } else {
                                showToast(res ? res.message : 'Error al reabrir.', 'error');
                            }
                        });
                    });
                });

                casosContainer.querySelectorAll('.btn-eliminar').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var caseId = this.getAttribute('data-id');
                        if (!confirm('Esta seguro de eliminar este caso?')) return;
                        api('POST', basePath + 'api/eliminar-caso', { id: parseInt(caseId, 10) }, function(err, res) {
                            if (res && res.success) {
                                showToast('Caso eliminado.', 'success');
                                loadCasos();
                                loadStats();
                            } else {
                                showToast(res ? res.message : 'Error al eliminar.', 'error');
                            }
                        });
                    });
                });
            });
        }

        /* ── Case detail ── */
        function verDetalle(caseId) {
            var modal = document.getElementById('modalDetailCase');
            var content = document.getElementById('detailCaseContent');
            content.innerHTML = '<p>Cargando...</p>';
            modal.style.display = 'flex';
            apiGet(basePath + 'api/obtener-caso?id=' + caseId, function(err, res) {
                if (!res || !res.success || !res.data) {
                    content.innerHTML = '<div class="empty-state"><div class="empty-icon">&#9888;</div><p>Error al cargar detalle.</p></div>';
                    return;
                }
                var c = res.data;
                var prioridadBadge = c.prioridad === 'urgente' ? 'badge-warning' : (c.prioridad === 'alta' ? 'badge-warning' : 'badge-info');
                var estadoBadge = c.estado === 'cerrado' ? 'badge-success' : 'badge-info';
                var html = '<h3>' + esc(c.titulo || 'Sin titulo') + '</h3>' +
                    '<p><span class="badge ' + prioridadBadge + '">' + esc(c.prioridad || 'media') + '</span> <span class="badge ' + estadoBadge + '">' + esc(c.estado || 'abierto') + '</span></p>' +
                    '<div class="detail-grid">' +
                    '<div class="detail-field"><label>ID</label><p>#' + c.id + '</p></div>' +
                    '<div class="detail-field"><label>Fecha creación</label><p>' + (c.created_at || c.assigned_at || '-') + '</p></div>' +
                    (c.resolved_at ? '<div class="detail-field"><label>Fecha cierre</label><p>' + c.resolved_at + '</p></div>' : '') +
                    '<div class="detail-field"><label>Prioridad solicitante</label><p>' + esc(c.persona_prioridad || '-') + '</p></div>' +
                    '</div>' +
                    '<div class="detail-section"><h4>Abogado asignado</h4>' +
                    '<div class="detail-grid">' +
                    '<div class="detail-field"><label>Nombre</label><p>' + esc(c.abogado_nombre || 'Sin asignar') + '</p></div>' +
                    '<div class="detail-field"><label>Email</label><p>' + esc(c.abogado_email || '-') + '</p></div>' +
                    (c.abogado_telefono ? '<div class="detail-field"><label>Teléfono</label><p>' + esc(c.abogado_telefono) + '</p></div>' : '') +
                    '<div class="detail-field"><label>Jurisdicción</label><p>' + esc(c.jurisdiccion || '-') + '</p></div>' +
                    '</div></div>' +
                    '<div class="detail-section"><h4>Persona afectada</h4>' +
                    '<div class="detail-grid">' +
                    '<div class="detail-field"><label>Nombre</label><p>' + esc(c.persona_nombre || '') + '</p></div>' +
                    '<div class="detail-field"><label>Email</label><p>' + esc(c.persona_email || '-') + '</p></div>' +
                    (c.persona_telefono ? '<div class="detail-field"><label>Teléfono</label><p>' + esc(c.persona_telefono) + '</p></div>' : '') +
                    '<div class="detail-field"><label>Ubicación</label><p>' + esc(c.persona_ciudad || c.persona_estado || '-') + '</p></div>' +
                    (c.tipo_ayuda ? '<div class="detail-field full"><label>Tipo de ayuda solicitada</label><p>' + esc(c.tipo_ayuda) + '</p></div>' : '') +
                    (c.persona_descripcion ? '<div class="detail-field full"><label>Descripción del solicitante</label><p>' + esc(c.persona_descripcion) + '</p></div>' : '') +
                    '</div></div>' +
                    (c.descripcion ? '<div class="detail-section"><h4>Descripción del caso</h4><p>' + esc(c.descripcion) + '</p></div>' : '') +
                    (c.notas ? '<div class="detail-section"><h4>Notas internas</h4><p>' + esc(c.notas) + '</p></div>' : '') +
                    (c.observaciones ? '<div class="detail-section"><h4>Observaciones de cierre</h4><p>' + esc(c.observaciones) + '</p></div>' : '');
                content.innerHTML = html;
            });
        }

        /* ── Edit case modal ── */
        function abrirEdicion(caseId) {
            var modal = document.getElementById('modalEditCase');
            modal.style.display = 'flex';
            document.getElementById('editCaseId').value = caseId;
            apiGet(basePath + 'api/obtener-caso?id=' + caseId, function(err, res) {
                if (!res || !res.success || !res.data) {
                    showToast('Error al cargar datos del caso.', 'error');
                    modal.style.display = 'none';
                    return;
                }
                var c = res.data;
                document.getElementById('editTitulo').value = c.titulo || '';
                document.getElementById('editPrioridad').value = c.prioridad || 'media';
                document.getElementById('editDescripcion').value = c.descripcion || '';
                document.getElementById('editNotas').value = c.notas || '';
                populateLawyerSelect(editAbogadoSelect, c.lawyer_id);
            });
        }

        /* ── Close case modal ── */
        var modalClose = document.getElementById('modalCloseCase');
        if (modalClose) {
            document.getElementById('modalCloseBtn').addEventListener('click', function() { modalClose.style.display = 'none'; });
            document.getElementById('btnCancelClose').addEventListener('click', function() { modalClose.style.display = 'none'; });
            modalClose.addEventListener('click', function(e) { if (e.target === modalClose) modalClose.style.display = 'none'; });
        }

        var formCloseCase = document.getElementById('formCloseCase');
        if (formCloseCase) {
            formCloseCase.addEventListener('submit', function(e) {
                e.preventDefault();
                var caseId = parseInt(document.getElementById('closeCaseId').value, 10);
                var observaciones = document.getElementById('closeObservaciones').value;
                api('POST', basePath + 'api/cerrar-caso', { id: caseId, observaciones: observaciones }, function(err, res) {
                    if (res && res.success) {
                        modalClose.style.display = 'none';
                        formCloseCase.reset();
                        showToast(res.message || 'Caso cerrado.', 'success');
                        loadCasos();
                        loadStats();
                    } else {
                        showToast(res ? res.message : 'Error al cerrar caso.', 'error');
                    }
                });
            });
        }

        /* ── Edit case modal ── */
        var modalEdit = document.getElementById('modalEditCase');
        if (modalEdit) {
            document.getElementById('modalEditCloseBtn').addEventListener('click', function() { modalEdit.style.display = 'none'; });
            document.getElementById('btnCancelEdit').addEventListener('click', function() { modalEdit.style.display = 'none'; });
            modalEdit.addEventListener('click', function(e) { if (e.target === modalEdit) modalEdit.style.display = 'none'; });
        }

        var formEditCase = document.getElementById('formEditCase');
        if (formEditCase) {
            formEditCase.addEventListener('submit', function(e) {
                e.preventDefault();
                var caseId = parseInt(document.getElementById('editCaseId').value, 10);
                var data = {
                    id: caseId,
                    titulo: document.getElementById('editTitulo').value,
                    prioridad: document.getElementById('editPrioridad').value,
                    descripcion: document.getElementById('editDescripcion').value,
                    notas: document.getElementById('editNotas').value
                };
                var lawyerVal = document.getElementById('editAbogado').value;
                if (lawyerVal) data.lawyer_id = parseInt(lawyerVal, 10);

                api('POST', basePath + 'api/actualizar-caso', data, function(err, res) {
                    if (res && res.success) {
                        modalEdit.style.display = 'none';
                        showToast(res.message || 'Caso actualizado.', 'success');
                        loadCasos();
                        loadStats();
                    } else {
                        showToast(res ? res.message : 'Error al actualizar.', 'error');
                    }
                });
            });
        }

        /* ── Detail modal ── */
        var modalDetail = document.getElementById('modalDetailCase');
        if (modalDetail) {
            document.getElementById('modalDetailCloseBtn').addEventListener('click', function() { modalDetail.style.display = 'none'; });
            modalDetail.addEventListener('click', function(e) { if (e.target === modalDetail) modalDetail.style.display = 'none'; });
        }

        /* ── Global Escape key for all modals ── */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                [modalClose, modalEdit, modalDetail].forEach(function(m) {
                    if (m && m.style.display !== 'none' && m.style.display !== '') m.style.display = 'none';
                });
            }
        });

        /* ── Filters ── */
        if (btnFiltrarCasos) btnFiltrarCasos.addEventListener('click', loadCasos);
        if (btnLimpiarCasos) btnLimpiarCasos.addEventListener('click', function() {
            if (filterCasosSearch) filterCasosSearch.value = '';
            if (filterCasosEstado) filterCasosEstado.value = '';
            if (filterCasosPrioridad) filterCasosPrioridad.value = '';
            loadCasos();
        });
        if (btnExportCasos) btnExportCasos.addEventListener('click', function() {
            showToast('Exportando CSV...', 'info');
            apiGet(basePath + 'api/exportar-casos' + buildCasosQuery(), function(err, res) {
                if (res && res.success && res.data) {
                    downloadCSV(res.data, 'casos');
                    showToast('CSV exportado.', 'success');
                } else {
                    showToast('Error al exportar.', 'error');
                }
            });
        });
        if (filterCasosSearch) {
            var debounceTimer;
            filterCasosSearch.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(loadCasos, 350);
            });
        }

        /* ── Report ── */
        var btnReporte = document.getElementById('btnGenerarReporte');
        var reporteContainer = document.getElementById('reporteGeneral');
        var reportFilterEstado = document.getElementById('reportFilterEstado');
        var reportFilterPrioridad = document.getElementById('reportFilterPrioridad');
        var btnExportReporte = document.getElementById('btnExportReporteCSV');

        function buildReportQuery() {
            var params = ['todos=1'];
            var est = reportFilterEstado ? reportFilterEstado.value : '';
            if (est) params.push('estado=' + encodeURIComponent(est));
            var pri = reportFilterPrioridad ? reportFilterPrioridad.value : '';
            if (pri) params.push('prioridad=' + encodeURIComponent(pri));
            return '?' + params.join('&');
        }

        function cargarReporte(cb) {
            showLoading(btnReporte);
            reporteContainer.innerHTML = '<div class="text-center" style="padding:2rem;"><div class="spinner" style="margin:0 auto;"></div><p style="margin-top:0.5rem;">Generando reporte...</p></div>';
            apiGet(basePath + 'api/obtener-casos' + buildReportQuery(), function(err, res) {
                hideLoading(btnReporte);
                if (!res || !res.success) {
                    reporteContainer.innerHTML = '<div class="empty-state"><div class="empty-icon">&#9888;</div><p>Error al generar reporte.</p></div>';
                    if (cb) cb([]);
                    return;
                }
                var data = res.data || [];
                if (!data.length) {
                    reporteContainer.innerHTML = '<div class="empty-state"><div class="empty-icon">&#128203;</div><p>No hay casos para reportar con los filtros seleccionados.</p></div>';
                    if (cb) cb([]);
                    return;
                }

                var html = '<div class="reporte-output">';

                var total = data.length;
                var abiertos = data.filter(function(c) { return c.estado === 'abierto'; }).length;
                var cerrados = data.filter(function(c) { return c.estado === 'cerrado'; }).length;
                var urgentes = data.filter(function(c) { return c.prioridad === 'urgente'; }).length;
                var altas = data.filter(function(c) { return c.prioridad === 'alta'; }).length;

                html += '<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">' +
                    '<div class="stat-card" style="flex:1;min-width:100px;"><h3>Total</h3><p>' + total + '</p></div>' +
                    '<div class="stat-card" style="flex:1;min-width:100px;"><h3>Abiertos</h3><p>' + abiertos + '</p></div>' +
                    '<div class="stat-card" style="flex:1;min-width:100px;"><h3>Cerrados</h3><p>' + cerrados + '</p></div>' +
                    '<div class="stat-card" style="flex:1;min-width:100px;border-left-color:var(--warning);"><h3>Urgentes</h3><p>' + urgentes + '</p></div>' +
                    '<div class="stat-card" style="flex:1;min-width:100px;border-left-color:var(--warning);"><h3>Alta prioridad</h3><p>' + altas + '</p></div>' +
                    '</div>';

                var grouped = {};
                data.forEach(function(c) {
                    var key = c.abogado_nombre || 'Sin asignar';
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(c);
                });

                Object.keys(grouped).sort().forEach(function(nombre) {
                    var casos = grouped[nombre];
                    var abs = casos.filter(function(c) { return c.estado === 'abierto'; }).length;
                    var cers = casos.filter(function(c) { return c.estado === 'cerrado'; }).length;
                    var urgs = casos.filter(function(c) { return c.prioridad === 'urgente'; }).length;
                    html += '<div class="reporte-abogado"><h4>' + esc(nombre) + '</h4>' +
                        '<p>Total: <strong>' + casos.length + '</strong> | Abiertos: ' + abs + ' | Cerrados: ' + cers + ' | Urgentes: ' + urgs + '</p>' +
                        '<div style="overflow-x:auto;"><table class="crm-table"><thead><tr><th>ID</th><th>Titulo</th><th>Persona</th><th>Prioridad</th><th>Estado</th><th>Creado</th></tr></thead><tbody>';
                    casos.forEach(function(c) {
                        var pb = c.prioridad === 'urgente' ? 'badge-warning' : (c.prioridad === 'alta' ? 'badge-warning' : 'badge-info');
                        var eb = c.estado === 'cerrado' ? 'badge-success' : 'badge-info';
                        html += '<tr><td><strong>' + c.id + '</strong></td><td>' + esc(c.titulo || '') + '</td><td>' + esc(c.persona_nombre || '') + '</td>' +
                            '<td><span class="badge ' + pb + '">' + esc(c.prioridad || 'media') + '</span></td>' +
                            '<td><span class="badge ' + eb + '">' + esc(c.estado || '') + '</span></td>' +
                            '<td class="small">' + (c.created_at || '') + '</td></tr>';
                    });
                    html += '</tbody></table></div></div>';
                });
                html += '</div>';
                reporteContainer.innerHTML = html;
                window.__reporteData = data;
                if (cb) cb(data);
            });
        }

        if (btnReporte && reporteContainer) {
            btnReporte.addEventListener('click', function() { cargarReporte(); });
        }

        if (btnExportReporte) {
            btnExportReporte.addEventListener('click', function() {
                if (window.__reporteData && window.__reporteData.length) {
                    downloadCSV(window.__reporteData, 'reporte-casos');
                    showToast('CSV exportado.', 'success');
                } else {
                    showToast('Genera el reporte primero.', 'info');
                }
            });
        }

        /* ── Init ── */
        loadStats();
        loadPersonSelect();
        loadLawyerSelect();
        loadCasos();

        function esc(s) { return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    })();
})();
