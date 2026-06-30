;(function() {
    'use strict';

    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('show');
            }
        });
    }

    const estados = [
        'Amazonas', 'Anzoátegui', 'Apure', 'Aragua', 'Barinas', 'Bolívar', 'Carabobo', 'Cojedes',
        'Delta Amacuro', 'Distrito Capital', 'Falcón', 'Guárico', 'Lara', 'Mérida', 'Miranda',
        'Monagas', 'Nueva Esparta', 'Portuguesa', 'Sucre', 'Táchira', 'Trujillo', 'La Guaira', 'Yaracuy', 'Zulia'
    ];

    const jurisdicciones = [
        'Penal', 'Civil', 'Laboral', 'Administrativo', 'Constitucional',
        'Migratorio', 'Familia', 'Mercantil', 'Tributario', 'Derechos Humanos'
    ];

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

    document.querySelectorAll('.app-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var required = form.querySelectorAll('[required]');
            var valid = true;
            required.forEach(function(field) {
                var error = field.closest('.form-group').querySelector('.error-msg');
                if (!field.value.trim()) {
                    if (error) error.textContent = 'Este campo es obligatorio.';
                    field.style.borderColor = '#e74c3c';
                    valid = false;
                } else {
                    if (error) error.textContent = '';
                    field.style.borderColor = '';
                }
                if (field.type === 'email' && field.value.trim()) {
                    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(field.value.trim())) {
                        if (error) error.textContent = 'Ingrese un email válido.';
                        field.style.borderColor = '#e74c3c';
                        valid = false;
                    }
                }
            });
            if (!valid) {
                e.preventDefault();
            }
        });

        form.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.addEventListener('input', function() {
                var error = field.closest('.form-group').querySelector('.error-msg');
                if (error) error.textContent = '';
                field.style.borderColor = '';
            });
        });
    });
})();
