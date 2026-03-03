/**
 * main.js - Cerebro de la aplicación
 * Contiene toda la lógica de navegación, formularios y carga dinámica.
 */

// --- GESTIÓN DE ESTADO GLOBAL (NUEVO) ---
let historyStack = [];
let currentViewData = null;
let selectedUsers = new Set(); // Mantenemos tu lógica de selección de usuarios
let newModuloCounter = 0; // Para los módulos

// --- INICIALIZACIÓN ---
$(document).ready(function () {
    initApp();

    // --- AJAX SPINNER GLOBAL ---
    $(document).ajaxStart(function () {
        // Mostramos el spinner quitando d-none y agregando d-flex para centrarlo
        $('#global-spinner').removeClass('d-none').addClass('d-flex');
    }).ajaxStop(function () {
        // Ocultamos el spinner
        $('#global-spinner').removeClass('d-flex').addClass('d-none');
    });

    // Listeners Globales para Pestañas y Acordeones
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });

    $(document).on('shown.bs.collapse', '.collapse', function (e) {
        if (e.target.id) localStorage.setItem('activeAccordion', e.target.id);
    });
    $(document).on('hidden.bs.collapse', '.collapse', function (e) {
        localStorage.removeItem('activeAccordion');
    });
});

function initApp() {
    applySidebarToggle();
    reapplyEvents();
    // Estado inicial: estamos en perfil.php
    currentViewData = { page: 'perfil.php', params: {} };
}

// --- NAVEGACIÓN INTELIGENTE (MEJORADA) ---
function loadPage(page, params = {}, isGoingBack = false) {
    console.log('Navegando a:', page, 'Params:', params, 'Back:', isGoingBack);

    // 1. Guardar estado antes de salir (si no estamos volviendo)
    if (!isGoingBack && currentViewData) {
        historyStack.push({
            page: currentViewData.page,
            params: currentViewData.params,
            scrollY: window.scrollY || document.documentElement.scrollTop,
            searchState: $('#busquedaCursoGlobal').val() || '' // Guardamos búsqueda si existe
        });
    }

    // 2. Preparar URL
    let url = page;
    if (page.endsWith('.php') && !page.includes('/')) {
        url = (page === 'buscar.php') ? '../controllers/' + page : '../views/' + page;
    }

    // 3. Petición AJAX
    $.ajax({
        url: url,
        method: 'GET',
        data: params,
        success: function (data) {
            $('#page-content').html(data);

            currentViewData = { page: page, params: params };
            reapplyEvents();

            // 4. Restaurar Scroll y Estado Visual
            if (isGoingBack && params.savedScrollY !== undefined) {
                setTimeout(() => window.scrollTo(0, params.savedScrollY), 50);
            } else if (params.scrollTo) {
                setTimeout(() => {
                    const target = document.getElementById(params.scrollTo);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                window.scrollTo(0, 0);
            }
            restaurarEstadoVisual();
        },
        error: function (xhr, status, error) {
            console.error('Error loading page:', page, error);
            alert('Error al cargar la página.');
        }
    });
}

function goBack() {
    if (historyStack.length > 0) {
        const previousState = historyStack.pop();
        previousState.params.savedScrollY = previousState.scrollY;
        loadPage(previousState.page, previousState.params, true);
    } else {
        // Si no hay historial, recargamos el perfil "limpio"
        loadProfile();
    }
}

function restaurarEstadoVisual() {
    // Restaurar Pestaña Activa
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        const tabTrigger = $(`a[href="${activeTab}"]`);
        if (tabTrigger.length > 0) {
            // Intentamos con Bootstrap 5 o 4 según disponibilidad
            try { new bootstrap.Tab(tabTrigger[0]).show(); } catch (e) { tabTrigger.tab('show'); }
        }
    }
    // Restaurar Acordeón
    const activeAccordion = localStorage.getItem('activeAccordion');
    if (activeAccordion) {
        $('#' + activeAccordion).addClass('show');
    }
}

// --- FUNCIONES AUXILIARES (Tus funciones originales) ---

function applySidebarToggle() {
    $('#sidebarToggleTop').off('click').on('click', function () {
        $('#accordionSidebar').toggleClass('toggled');
    });
}

function reapplyEvents() {
    console.log('Reaplicando eventos...');

    $('.editarCursoForm').off('submit').on('submit', handleCursoEdition);
    $('#inscribir-usuarios-btn').off('click').on('click', handleInscripcionUsuarios);
    $('#busquedaForm').off('submit').on('submit', handleBusquedaForm);

    $(document).off('change', '.usuario-checkbox').on('change', '.usuario-checkbox', handleUsuarioCheckbox);

    // Paginación AJAX
    $(document).off('click', '.pagination-link').on('click', '.pagination-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        var busqueda = $('#busqueda-input').val();
        loadPage('../controllers/usuarios_controlador.php', { page: page, busqueda: busqueda });
    });

    $('form[id^="inscripcionForm"]').off('submit').on('submit', handleInscripcionForm);
    $('.editar-usuario-form').off('submit').on('submit', handleUsuarioEdition);
    $('#inscripcion-search-input').off('keyup').on('keyup', handleInscripcionSearch);

    applySidebarToggle();

    // Restaurar checkboxes seleccionados
    $('.usuario-checkbox').each(function () {
        var userId = $(this).data('id');
        if (selectedUsers.has(userId)) {
            $(this).prop('checked', true);
        }
    });
}

// --- MANEJADORES DE FORMULARIOS ---

function submitFormWithFetch(form, successMessage, reloadOnSuccess = false, callback = null) {
    const formData = new FormData(form);
    const formUrl = form.getAttribute('action');

    fetch(formUrl, { method: form.method, body: formData })
        .then(response => {
            if (!response.ok) throw new Error('Error servidor: ' + response.statusText);
            return response.text();
        })
        .then(result => {
            if (result.includes(successMessage)) {
                alert('¡Éxito! ' + successMessage);
                if (reloadOnSuccess) window.location.reload();
                else if (typeof callback === 'function') callback(form);
            } else {
                // Intentar extraer mensaje de error limpio si viene en HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = result;
                const alertElement = tempDiv.querySelector('.alert');
                alert(alertElement ? alertElement.innerText.trim() : result);
            }
        })
        .catch(error => {
            console.error('Error fetch:', error);
            alert('Error de conexión al procesar la solicitud.');
        });
}

function handleCursoEdition(event) {
    event.preventDefault();
    submitFormWithFetch(this, 'El curso se ha editado correctamente', false, () => {
        // En lugar de recargar la página entera, recargamos la vista actual
        if (currentViewData && currentViewData.page) {
            loadPage(currentViewData.page, currentViewData.params);
        } else {
            loadPage('../public/editar_cursos.php');
        }
    });
}

function handleUsuarioEdition(event) {
    event.preventDefault();
    submitFormWithFetch(this, 'El usuario se ha editado correctamente', false, () => {
        const pageLink = document.querySelector('.page-item.active .page-link');
        loadPage('usuarios.php', { page: pageLink ? pageLink.dataset.page : 1 });
    });
}

function handleInscripcionForm(event) {
    event.preventDefault();
    submitFormWithFetch(this, 'Te has inscrito correctamente en el curso', false, (form) => {
        loadPage('buscar.php', { id_curso: form.querySelector('input[name="curso_id"]').value });
    });
}

// --- OTRAS UTILIDADES ---

function handleUsuarioCheckbox() {
    var userId = $(this).data('id');
    if ($(this).is(':checked')) selectedUsers.add(userId);
    else selectedUsers.delete(userId);
}

function handleInscripcionUsuarios() {
    if (selectedUsers.size > 0) {
        $.ajax({
            url: '../controllers/usuarios_controlador.php',
            method: 'POST',
            data: {
                action: 'inscribir_usuarios',
                usuarios: Array.from(selectedUsers),
                curso_id: $('#curso-id').val()
            },
            success: function (response) {
                alert('Usuarios registrados correctamente.');
                location.reload();
            },
            error: function () { alert('Error al registrar usuarios.'); }
        });
    } else { alert('No hay usuarios seleccionados.'); }
}

function handleInscripcionSearch() {
    const input = $(this);
    const busqueda = input.val();
    const id_curso = input.data('id-curso');
    clearTimeout(window.inscripcionSearchTimeout);
    window.inscripcionSearchTimeout = setTimeout(function () {
        loadPage('buscar.php', { id_curso: id_curso, busqueda: busqueda, page: 1 });
    }, 300);
}

function handleBusquedaForm(event) {
    event.preventDefault();
    loadPage('buscar.php', {
        busqueda: $('#busqueda').val(),
        id_curso: $('#id_curso').val(),
        page: 1
    });
}

// --- CARGAS ESPECÍFICAS ---

function loadProfile() {
    // Recarga "limpia" del contenido del perfil
    $.ajax({
        url: '../public/perfil.php',
        method: 'GET',
        success: function (data) {
            // Extraemos solo el contenido para no duplicar headers
            $('#page-content').html($(data).find('#page-content').html());
            historyStack = []; // Limpiamos historial al volver al inicio
        }
    });
}

function loadCategory(tipo_curso, estado) {
    loadPage('../public/cursos.php', { tipo_curso: tipo_curso, estado: estado });
}

function inscribirUsuario(userId) {
    var form = document.getElementById('inscripcionForm-' + userId);
    var formData = new FormData(form);

    // Búsqueda de página actual para recargar en el mismo punto
    var currentPage = 1;
    // Intentamos buscar en la paginación si existe
    var activePage = document.querySelector('.page-item.active .page-link');
    if (activePage) currentPage = activePage.innerText;

    fetch(form.getAttribute('action'), { method: 'POST', body: formData })
        .then(response => response.text())
        .then(result => {
            if (result.includes('correctamente')) {
                alert('Operación exitosa.');
                loadPage('../controllers/buscar.php', {
                    id_curso: form.querySelector('input[name="curso_id"]').value,
                    page: currentPage
                });
            } else {
                alert('Mensaje del servidor: ' + result.replace(/<[^>]*>?/gm, '')); // Strip tags
            }
        })
        .catch(err => alert('Error: ' + err));
}

function subirFirmaDigital() {
    var formData = new FormData();
    var fileInput = document.getElementById('firma_digital');
    if (fileInput.files.length === 0) { alert("Seleccione un archivo"); return; }

    formData.append('firma_digital', fileInput.files[0]);

    fetch('../controllers/subir_firma.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) alert(data.message);
            else if (data.file_exists && confirm('El archivo existe. ¿Sobreescribir?')) {
                formData.append('overwrite', 'true');
                fetch('../controllers/subir_firma.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(d => alert(d.success ? 'Firma actualizada' : 'Error: ' + d.message));
            } else {
                alert('Error: ' + data.message);
            }
        });
}

function actualizarFechaUsuario(userId) {
    var form = document.getElementById('fechaForm-' + userId);
    if (!form) return;

    var formData = new FormData(form);
    fetch(form.getAttribute('action'), { method: 'POST', body: formData })
        .then(response => response.text())
        .then(result => {
            alert(result.includes('correctamente') ? 'Fecha actualizada' : 'Error al actualizar');
            // Recargamos la vista pero mantenemos posición
            loadPage('../controllers/buscar.php', {
                id_curso: form.querySelector('input[name="curso_id"]').value,
                page: form.querySelector('input[name="page"]').value,
                scrollTo: 'user-' + userId
            });
        });
}

function loadCourse(courseId) {
    // Usamos loadPage para aprovechar el historial y el scroll
    loadPage('../views/curso.php', { id: courseId });
}

function loadHistorial(action) {
    loadPage('../views/historial.php', { action: action });
}