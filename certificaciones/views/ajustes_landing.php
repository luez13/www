<?php
require_once __DIR__ . '/../controllers/init.php';
if ($_SESSION['id_rol'] != 4)
    die('<div class="alert alert-danger m-3">Acceso denegado.</div>');
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Ajustes de la Página Principal (Landing)</h1>

    <ul class="nav nav-tabs mb-4" id="landingTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="carrusel-tab" data-bs-toggle="tab" data-bs-target="#carrusel"
                type="button" role="tab">Imágenes del Carrusel</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cursos-tab" data-bs-toggle="tab" data-bs-target="#cursos" type="button"
                role="tab">Minituras de Cursos</button>
        </li>
    </ul>

    <div class="tab-content" id="landingTabContent">
        <!-- CARRUSEL -->
        <div class="tab-pane fade show active" id="carrusel" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Nueva Imagen de Carrusel</h6>
                </div>
                <div class="card-body">
                    <form id="formSubirCarrusel" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="subir_carrusel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="imagenCarrusel" class="form-label">Archivo de Imagen (Recomendado
                                    1920x1080)</label>
                                <input class="form-control" type="file" id="imagenCarrusel" name="imagen"
                                    accept="image/*" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tituloCarrusel" class="form-label">Título Opcional</label>
                                <input type="text" class="form-control" id="tituloCarrusel" name="titulo">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="descripcionCarrusel" class="form-label">Descripción Opcional</label>
                                <textarea class="form-control" id="descripcionCarrusel" name="descripcion"
                                    rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Subir Imagen</button>
                    </form>
                    <div id="feedback-carrusel" class="mt-2"></div>
                </div>
            </div>

            <div class="row" id="carrusel-grid">
                <!-- Tarjetas de carrusel cargadas por AJAX -->
            </div>
        </div>

        <!-- CURSOS -->
        <div class="tab-pane fade" id="cursos" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gestionar Portadas de Cursos</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="buscarCursoAdmin" class="form-control border-start-0 ps-0"
                                    placeholder="Buscar por nombre o tipo...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaPortadasCursos" width="100%"
                            cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Tipo</th>
                                    <th>Portada Actual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Llenado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="paginacionAdminCursos" class="d-flex justify-content-center mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Foto Curso -->
<div class="modal fade" id="modalFotoCurso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formSubirFotoCurso" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Subir Portada para Curso (<span id="spanNombreCurso"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="subir_imagen_curso">
                <input type="hidden" name="id_curso" id="foto_id_curso">
                <div class="mb-3">
                    <label class="form-label">Selecciona Imagen (Recomendado cuadrada o 4:3)</label>
                    <input class="form-control" type="file" name="imagen" accept="image/*" required>
                </div>
                <div id="feedback-foto-curso"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Imagen</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // --- CARRUSEL ---
        function cargarCarrusel() {
            $.getJSON('../controllers/LandingController.php', { action: 'listar_carrusel' }, function (response) {
                let html = '';
                if (response.success && response.data.length > 0) {
                    response.data.forEach(item => {
                        html += `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="${item.ruta_imagen}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${item.titulo || 'Sin Título'}</h5>
                                    <p class="card-text small text-muted">${item.descripcion || 'Sin descripción'}</p>
                                </div>
                                <div class="card-footer bg-white border-0 text-center">
                                    <button class="btn btn-sm btn-outline-danger btn-borrar-carrusel" data-id="${item.id_carrusel}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                } else {
                    html = '<div class="col-12"><div class="alert alert-info">No hay imágenes en el carrusel. ¡Sube la primera!</div></div>';
                }
                $('#carrusel-grid').html(html);
            });
        }

        cargarCarrusel();

        $('#formSubirCarrusel').on('submit', function (e) {
            e.preventDefault();
            let bt = $(this).find('button[type="submit"]');
            bt.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
            let fd = new FormData(this);
            $.ajax({
                url: '../controllers/LandingController.php',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function (resp) {
                    if (resp.success) {
                        $('#feedback-carrusel').html('<div class="alert alert-success">Imagen subida.</div>');
                        $('#formSubirCarrusel')[0].reset();
                        cargarCarrusel();
                    } else {
                        $('#feedback-carrusel').html('<div class="alert alert-danger">' + resp.message + '</div>');
                    }
                    setTimeout(() => $('#feedback-carrusel').empty(), 4000);
                },
                complete: function () {
                    bt.prop('disabled', false).text('Subir Imagen');
                }
            });
        });

        $(document).on('click', '.btn-borrar-carrusel', function () {
            if (!confirm('¿Seguro que deseas eliminar esta imagen del carrusel?')) return;
            let id = $(this).data('id');
            $.post('../controllers/LandingController.php', { action: 'eliminar_carrusel', id_carrusel: id }, function (resp) {
                if (resp.success) cargarCarrusel();
                else alert(resp.message);
            }, 'json');
        });

        // --- CURSOS ---
        let allCursosData = [];
        let curPageAdmin = 1;
        const rowsPerPageAdmin = 10;

        function renderAdminCursos(page) {
            const tbody = $('#tablaPortadasCursos tbody');
            tbody.empty();
            let term = $('#buscarCursoAdmin').val().toLowerCase().trim();

            // Filter
            let filtered = allCursosData.filter(c => {
                let nom = (c.nombre_curso || '').toLowerCase();
                let tip = (c.tipo_curso || '').toLowerCase();
                return nom.includes(term) || tip.includes(term);
            });

            // Paginate
            let totalPages = Math.ceil(filtered.length / rowsPerPageAdmin) || 1;
            if (page > totalPages) page = totalPages;
            curPageAdmin = page;

            let start = (page - 1) * rowsPerPageAdmin;
            let end = start + rowsPerPageAdmin;
            let toShow = filtered.slice(start, end);

            if (toShow.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center text-muted">No se encontraron cursos.</td></tr>');
            } else {
                toShow.forEach(c => {
                    let imgHtml = c.imagen_portada ? `<img src="${c.imagen_portada}" width="80" class="img-thumbnail" style="object-fit:cover; height: 60px;">` : `<span class="badge bg-secondary">Sin Portada</span>`;
                    let tr = `<tr>
                    <td>${c.nombre_curso}</td>
                    <td>${c.tipo_curso}</td>
                    <td class="text-center">${imgHtml}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary btn-subir-foto" data-id="${c.id_curso}" data-nombre="${c.nombre_curso}"><i class="fas fa-camera"></i> Subir Foto</button>
                        ${c.imagen_portada ? `<button class="btn btn-sm btn-outline-danger btn-quitar-foto" data-id="${c.id_curso}"><i class="fas fa-trash"></i></button>` : ''}
                    </td>
                </tr>`;
                    tbody.append(tr);
                });
            }

            // Build pagination HTML
            let pagHtml = '';
            if (totalPages > 1) {
                pagHtml += '<ul class="pagination pagination-sm shadow-sm">';
                for (let i = 1; i <= totalPages; i++) {
                    pagHtml += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link page-curso-admin" href="#" data-page="${i}">${i}</a></li>`;
                }
                pagHtml += '</ul>';
            }
            $('#paginacionAdminCursos').html(pagHtml);
        }

        function cargarCursosLanding() {
            $.getJSON('../controllers/LandingController.php', { action: 'listar_cursos_admin' }, function (response) {
                if (response.success) {
                    allCursosData = response.data;
                    renderAdminCursos(curPageAdmin);
                }
            });
        }

        $('#buscarCursoAdmin').on('input', function () {
            renderAdminCursos(1);
        });

        $(document).on('click', '.page-curso-admin', function (e) {
            e.preventDefault();
            renderAdminCursos(parseInt($(this).data('page')));
        });

        cargarCursosLanding();

        $(document).on('click', '.btn-subir-foto', function () {
            $('#spanNombreCurso').text($(this).data('nombre'));
            $('#foto_id_curso').val($(this).data('id'));
            $('#modalFotoCurso').modal('show');
        });

        $('#formSubirFotoCurso').on('submit', function (e) {
            e.preventDefault();
            let bt = $(this).find('button[type="submit"]');
            bt.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            let fd = new FormData(this);
            $.ajax({
                url: '../controllers/LandingController.php',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function (resp) {
                    if (resp.success) {
                        $('#modalFotoCurso').modal('hide');
                        $('#formSubirFotoCurso')[0].reset();
                        cargarCursosLanding();
                    } else {
                        $('#feedback-foto-curso').html('<div class="alert alert-danger mt-2">' + resp.message + '</div>');
                        setTimeout(() => $('#feedback-foto-curso').empty(), 4000);
                    }
                },
                complete: function () {
                    bt.prop('disabled', false).text('Guardar Imagen');
                }
            });
        });

        $(document).on('click', '.btn-quitar-foto', function () {
            if (!confirm('¿Seguro que deseas quitar la foto de este curso? (volverá a la imagen por defecto)')) return;
            let id = $(this).data('id');
            $.post('../controllers/LandingController.php', { action: 'quitar_imagen_curso', id_curso: id }, function (resp) {
                if (resp.success) cargarCursosLanding();
                else alert(resp.message);
            }, 'json');
        });

    });
</script>