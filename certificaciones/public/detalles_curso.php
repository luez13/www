<head>
    <meta charset="UTF-8">
    <title>Detalles del Curso</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Tus estilos personalizados para ajustar el contenido del iframe */
        body {
            background-color: #ffffff !important;
            color: #000000;
            padding: 20px;
        }
    </style>
</head>

<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Incluir el archivo init.php en views
include '../controllers/init.php';

echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('fondo-negro');
    });
</script>";

// Incluir el archivo curso.php en models
include '../models/curso.php';

// Crear una instancia de la clase DB
$db = new DB();

// Crear una instancia de la clase Curso
$curso = new Curso($db);

// Obtener el id del curso del parámetro de la URL
$id_curso = $_GET['id'];

// Obtener el id del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener el rol del usuario de la sesión
$user_role = $_SESSION['id_rol'];

// Obtener el id del promotor del curso
$curso_info = $curso->obtener_curso($id_curso);
$promotor_id = $curso_info['promotor'];

// Verificar si el usuario tiene permiso para ver la página
if (!in_array($user_role, [3, 4]) && $user_id != $promotor_id) {
    echo '<p>No tienes permiso para ver esta página.</p>';
    include '../views/footer.php';
    exit;
}

echo '<div class="main-content container-fluid mt-3 pb-5">';
// Validar el id del curso
if (is_numeric($id_curso) && $id_curso > 0) {
    // Obtener los usuarios inscritos en el curso usando el método de la clase Curso
    $usuarios = $curso->obtener_estudiantes($id_curso);

    // Verificar que la variable $curso_info no sea nula
    if (isset($curso_info)) {
        $cupos_disponibles = $curso_info['limite_inscripciones'] - count($usuarios);
        ?>
        <!-- Encabezado del Curso -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h2 class="text-primary fw-bold mb-2"><?= htmlspecialchars($curso_info['nombre_curso']) ?></h2>
                <p class="text-secondary fs-5 mb-3"><?= htmlspecialchars($curso_info['descripcion']) ?></p>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info text-dark fs-6 px-3 py-2 rounded-pill"><i class="fas fa-users me-2"></i>Cupos
                        disponibles: <?= $cupos_disponibles ?></span>
                    <button id="mostrar-bd-info" class="btn btn-outline-secondary btn-sm"><i class="fas fa-database me-1"></i>
                        Mostrar Info de DB</button>
                </div>
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h4 class="m-0 fw-bold text-dark"><i class="fas fa-list me-2"></i> Usuarios inscritos</h4>
                <div>
                    <button id="marcar-todas" class="btn btn-outline-primary btn-sm me-2 fw-bold"><i
                            class="fas fa-check-double me-1"></i> Invertir Selección</button>
                    <button id="actualizar-tomos-folios" class="btn btn-success btn-sm fw-bold"><i class="fas fa-save me-1"></i>
                        Actualizar Tomos y Folios</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Participante</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th class="text-center">Nota</th>
                                <th class="text-center">Completado</th>
                                <?php if (in_array($user_role, [3, 4])): ?>
                                    <th class="text-center">Pagado</th>
                                    <th class="text-center" style="width: 220px;">Tomo / Folio</th>
                                <?php endif; ?>
                                <th class="text-end" style="width: 250px;">Calificar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario):
                                $nota = $curso->obtener_nota($id_curso, $usuario['id']);
                                $completado = $curso->obtener_completado($id_curso, $usuario['id']);
                                $pagado = $curso->obtener_pagado($id_curso, $usuario['id']);
                                $tomo = $curso->obtener_tomo($id_curso, $usuario['id']);
                                $folio = $curso->obtener_folio($id_curso, $usuario['id']);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?= htmlspecialchars($usuario['nombre']) . ' ' . htmlspecialchars($usuario['apellido']) ?>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-light text-dark border"><?= htmlspecialchars($usuario['cedula']) ?></span>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars($usuario['correo']) ?></small></td>
                                    <td class="text-center fw-bold text-primary fs-5 nota" data-id-usuario="<?= $usuario['id'] ?>">
                                        <?= $nota !== null && $nota !== '' ? htmlspecialchars($nota) : '-' ?></td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center m-0">
                                            <input type="checkbox" class="form-check-input completado"
                                                style="transform: scale(1.5); cursor: pointer; border: 2px solid #0d6efd; box-shadow: 0 0 5px rgba(13,110,253,0.5);" data-id-curso="<?= $id_curso ?>"
                                                data-id-usuario="<?= $usuario['id'] ?>" <?= $completado ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <?php if (in_array($user_role, [3, 4])): ?>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center m-0">
                                                <input type="checkbox" class="form-check-input pagado"
                                                    style="transform: scale(1.5); cursor: pointer; border: 2px solid #0d6efd; box-shadow: 0 0 5px rgba(13,110,253,0.5);" data-id-curso="<?= $id_curso ?>"
                                                    data-id-usuario="<?= $usuario['id'] ?>" <?= $pagado ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white text-muted">T</span>
                                                <input type="number" class="form-control text-center tomo"
                                                    data-id-curso="<?= $id_curso ?>" data-id-usuario="<?= $usuario['id'] ?>"
                                                    value="<?= htmlspecialchars($tomo) ?>" min="0" placeholder="Tomo">
                                                <span class="input-group-text bg-white text-muted">F</span>
                                                <input type="number" class="form-control text-center folio"
                                                    data-id-curso="<?= $id_curso ?>" data-id-usuario="<?= $usuario['id'] ?>"
                                                    value="<?= htmlspecialchars($folio) ?>" min="0" placeholder="Folio">
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-end">
                                        <form class="asignar-nota m-0 d-flex gap-2 justify-content-end"
                                            data-id-usuario="<?= $usuario['id'] ?>" action="../controllers/asignar_nota.php"
                                            method="post">
                                            <input type="hidden" name="action" value="asignar_nota">
                                            <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                            <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                                            <input type="number" class="form-control form-control-sm text-center"
                                                style="width: 70px;" id="nota_<?= $usuario['id'] ?>" name="nota" min="0" max="20"
                                                placeholder="0-20" title="La nota debe ser entre 0 y 20" required>
                                            <button type="submit" class="btn btn-primary btn-sm" title="Guardar calificación"><i
                                                    class="fas fa-check me-1"></i>Subir nota</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No hay usuarios inscritos en este curso aún.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="text-muted text-center small"><i class="fas fa-info-circle me-1"></i> Modificar el <strong>Tomo y
                Folio</strong> requiere pulsar el botón "Actualizar Tomos y Folios" superior. <br>Los checks de Completado y
            Pagado, así como el botón de calificar, se guardan automáticamente al modificarlos.</p>

        <?php
    } else {
        // Si el curso no existe o no fue creado por el usuario, mostrar un mensaje de error
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>El curso solicitado no existe o no lo has creado tú.</div>';
    }
} else {
    // Si el id del curso es inválido, mostrar un mensaje de error
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>El id del curso es inválido.</div>';
}
echo '</div>';
?>
<script>
    $(document).ready(function () {
        $('.completado').change(function () {
            var id_curso = $(this).data('id-curso');
            var id_usuario = $(this).data('id-usuario');
            var completado = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '../controllers/actualizar_estado.php',
                type: 'POST',
                data: {
                    id_curso: id_curso,
                    id_usuario: id_usuario,
                    completado: completado
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        $('.pagado').change(function () {
            var id_curso = $(this).data('id-curso');
            var id_usuario = $(this).data('id-usuario');
            var pagado = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '../controllers/actualizar_estado.php',
                type: 'POST',
                data: {
                    id_curso: id_curso,
                    id_usuario: id_usuario,
                    pagado: pagado
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        $('form.asignar-nota').submit(function (event) {
            event.preventDefault();
            var form = $(this);
            var id_usuario = form.data('id-usuario');
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function (response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);
                        $('td.nota[data-id-usuario="' + id_usuario + '"]').text(form.find('input[name="nota"]').val());
                    } else {
                        alert(data.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        $('#actualizar-tomos-folios').click(function () {
            $('.tomo, .folio').each(function () {
                var id_curso = $(this).data('id-curso');
                var id_usuario = $(this).data('id-usuario');
                var tomo = $('.tomo[data-id-usuario="' + id_usuario + '"]').val();
                var folio = $('.folio[data-id-usuario="' + id_usuario + '"]').val();

                tomo = tomo !== '' ? tomo : null;
                folio = folio !== '' ? folio : null;

                $.ajax({
                    url: '../controllers/actualizar_estado.php',
                    type: 'POST',
                    data: {
                        id_curso: id_curso,
                        id_usuario: id_usuario,
                        tomo: tomo,
                        folio: folio
                    },
                    success: function (response) {
                        console.log(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            });

            alert('Todos los tomos y folios han sido actualizados');
        });

        $('#marcar-todas').click(function () {
            var allChecked = $('.completado').length === $('.completado:checked').length;
            $('.completado, .pagado').prop('checked', !allChecked);

            $('.completado, .pagado').each(function () {
                var id_curso = $(this).data('id-curso');
                var id_usuario = $(this).data('id-usuario');
                var field = $(this).hasClass('completado') ? 'completado' : 'pagado';
                var value = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '../controllers/actualizar_estado.php',
                    type: 'POST',
                    data: {
                        id_curso: id_curso,
                        id_usuario: id_usuario,
                        [field]: value
                    },
                    success: function (response) {
                        console.log(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            });
        });

        $('#mostrar-bd-info').click(function () {
            $.ajax({
                url: '../controllers/mostrar_bd_info.php',
                type: 'GET',
                dataType: 'json', // Asegúrate de que el tipo de dato esperado es 'json'
                success: function (response) {
                    console.log('Información de la base de datos:');
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('Error:', textStatus, errorThrown);
                }
            });
        });
    });
</script>