<?php
// views/curso.php

include '../config/model.php';
// Nota: init.php ya inicia sesión, no necesitamos repetirlo si ya estamos dentro, 
// pero por seguridad en carga directa lo mantenemos con require_once
require_once '../controllers/init.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado");
}

$db = new DB();
include '../models/curso.php';
$curso = new Curso($db);

$id_curso = isset($_GET['id']) ? $_GET['id'] : 0;
if ($id_curso == 0) {
    echo "<div class='alert alert-danger'>ID de curso no válido</div>";
    exit;
}

$curso_contenido = $curso->obtener_curso($id_curso);

// Validar si existe el curso
if (!$curso_contenido) {
    echo "<div class='alert alert-danger'>Curso no encontrado</div>";
    exit;
}

// Obtener Promotor
$stmt_promotor = $db->prepare('SELECT nombre, apellido FROM cursos.usuarios WHERE id = :id_promotor');
$stmt_promotor->execute(['id_promotor' => $curso_contenido['promotor']]);
$promotor = $stmt_promotor->fetch();
$nombre_promotor = $promotor ? $promotor['nombre'] . ' ' . $promotor['apellido'] : 'Desconocido';

// Cupos
$stmt_cupos = $db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt_cupos->execute(['curso_id' => $id_curso]);
$count = $stmt_cupos->fetchColumn();
$cupos_disponibles = $curso_contenido['limite_inscripciones'] - $count;

// Inscripción del usuario actual
$stmt_inscripcion = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
$stmt_inscripcion->execute(['curso_id' => $id_curso, 'id_usuario' => $_SESSION['user_id']]);
$inscripcion = $stmt_inscripcion->fetch();

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 text-gray-800">Detalles del Curso</h3>
        <button class="btn btn-secondary btn-sm" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?php echo htmlspecialchars($curso_contenido['nombre_curso']); ?></h3>
            <p class="mb-0 fst-italic text-white-50">Tipo:
                <?php echo htmlspecialchars($curso_contenido['tipo_curso']); ?>
            </p>
        </div>
        <div class="card-body">
            <p class="card-text lead"><?php echo htmlspecialchars($curso_contenido['descripcion']); ?></p>

            <ul class="list-group list-group-flush mt-3 mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-user-tie me-2 text-primary"></i>Facilitador</strong>
                    <span
                        class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($nombre_promotor); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-check-circle me-2 text-success"></i>Estado</strong>
                    <?php if ($curso_contenido['estado']): ?>
                        <span class="badge bg-success rounded-pill">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-danger rounded-pill">Inactivo</span>
                    <?php endif; ?>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-users me-2 text-info"></i>Cupos Disponibles</strong>
                    <span class="badge bg-info text-dark rounded-pill"><?php echo $cupos_disponibles; ?></span>
                </li>
                <li class="list-group-item">
                    <strong><i class="far fa-clock me-2 text-warning"></i>Horario</strong>
                    <br>
                    <span class="ms-4">
                        <?php echo htmlspecialchars($curso_contenido['dias_clase']); ?>
                        (<?php echo date("g:i a", strtotime($curso_contenido['horario_inicio'])); ?> -
                        <?php echo date("g:i a", strtotime($curso_contenido['horario_fin'])); ?>)
                    </span>
                </li>
                <li class="list-group-item">
                    <strong><i class="fas fa-book me-2 text-secondary"></i>Requisitos</strong>
                    <br>
                    <span class="ms-4"><?php echo htmlspecialchars($curso_contenido['conocimientos_previos']); ?></span>
                </li>
            </ul>

            <h4 class="mt-4 border-bottom pb-2">Módulos del Curso</h4>
            <div class="accordion" id="accordionModulos">
                <?php
                $stmt_modulos = $db->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero');
                $stmt_modulos->execute(['id_curso' => $id_curso]);
                $modulos = $stmt_modulos->fetchAll();
                if (empty($modulos)) {
                    echo '<p class="text-muted mt-2">No hay módulos registrados.</p>';
                }
                foreach ($modulos as $modulo):
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo $modulo['id_modulo']; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-<?php echo $modulo['id_modulo']; ?>" aria-expanded="false"
                                aria-controls="collapse-<?php echo $modulo['id_modulo']; ?>">
                                <strong>Módulo
                                    <?php echo $modulo['numero']; ?>:</strong>&nbsp;<?php echo htmlspecialchars($modulo['nombre_modulo']); ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo $modulo['id_modulo']; ?>" class="accordion-collapse collapse"
                            aria-labelledby="heading-<?php echo $modulo['id_modulo']; ?>"
                            data-bs-parent="#accordionModulos">
                            <div class="accordion-body">
                                <p><strong>Contenido:</strong> <?php echo htmlspecialchars($modulo['contenido']); ?></p>
                                <p><strong>Actividad:</strong> <?php echo htmlspecialchars($modulo['actividad']); ?></p>
                                <p><strong>Evaluación:</strong> <?php echo htmlspecialchars($modulo['instrumento']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-footer text-center bg-light p-3">
            <?php if (in_array($_SESSION['id_rol'], [1, 2, 3, 4])): // Permitir que todos los roles actúen como alumnos si lo desean ?>
                <?php if (!$inscripcion): ?>
                    <?php if ($cupos_disponibles > 0): ?>
                        <form id="formInscribirCurso" method="POST" action="../controllers/curso_acciones.php">
                            <input type="hidden" name="action" value="inscribirse">
                            <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
                            <input type="hidden" name="curso_id" value="<?php echo $id_curso; ?>">
                            <button type="submit" class="btn btn-primary btn-lg shadow">
                                <i class="fas fa-user-plus me-2"></i>Inscribirse al curso
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>Cupos Agotados</button>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-info">
                        Estado: <strong><?php echo $inscripcion['completado'] ? 'Finalizado' : 'En Curso'; ?></strong> |
                        Nota: <strong><?php echo isset($inscripcion['nota']) ? $inscripcion['nota'] : 'Pendiente'; ?></strong>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <?php if ($inscripcion['completado'] != 1): ?>
                            <form id="formCancelarCurso" method="POST" action="../controllers/curso_acciones.php">
                                <input type="hidden" name="action" value="cancelar_inscripcion">
                                <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
                                <input type="hidden" name="curso_id" value="<?php echo $id_curso; ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-times-circle me-2"></i>Cancelar
                                </button>
                            </form>

                            <a href="../controllers/generar_constancia.php?id_curso=<?php echo $id_curso; ?>" target="_blank"
                                class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Constancia
                            </a>
                        <?php endif; ?>

                        <?php if ($inscripcion['completado'] == 1): ?>
                            <?php if ($inscripcion['pago'] == 1):
                                $valor_unico = $inscripcion['valor_unico'];
                                ?>
                                <button id="btn-certificado-<?php echo $id_curso; ?>" class="btn btn-success" type="button">
                                    <i class="fas fa-award me-2"></i> Ver Certificado
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning" type="button" onclick="loadPage('../views/mis_pagos.php')">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Certificado Bloqueado - Ir a Mis Aranceles
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$is_ajax) {
    include '../views/footer.php';
} ?>

<div class="modal fade" id="modalAranceles" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-exclamation-triangle me-2"></i>Validación requerida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estimado usuario, está a punto de visualizar su certificado.</p>
                <div class="alert alert-secondary border-left-warning">
                    <small><strong>Nota:</strong> El documento digital se emite automáticamente. Para obtener
                        <strong>firmas y sellos húmedos</strong>, debe validar el pago del arancel dirijase a la seccion
                        de facturación y pagos, mis aranceles, y pagar el arancel o realice el pago en caja de la
                        universidad.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarDescarga">
                    <i class="fas fa-check me-2"></i>Entendido, Ver
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // USAMOS UNA FUNCIÓN AUTO-EJECUTABLE PARA EVITAR CONFLICTOS DE VARIABLES GLOBALES EN AJAX
    (function () {
        const cursoId = "<?php echo $id_curso; ?>";
        const valorUnico = "<?php echo isset($valor_unico) ? $valor_unico : ''; ?>";

        // Referencias al DOM
        const btnCertificado = document.getElementById('btn-certificado-' + cursoId);
        const btnConfirmar = document.getElementById('btnConfirmarDescarga');
        const modalEl = document.getElementById('modalAranceles');

        let modalInstance = null;

        // Listener para abrir el modal
        if (btnCertificado) {
            btnCertificado.addEventListener('click', function () {
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.show();
            });
        }

        // Listener explícito para cerrar el modal (parche para vistas cargadas por AJAX)
        if (modalEl) {
            const closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
            closeBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    if (modalInstance) {
                        modalInstance.hide();

                        // Limpiar el backdrop si se queda pegado
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(b => b.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }
                });
            });
        }

        // Listener para confirmar descarga (SOLO UNA VEZ)
        // Usamos .onclick para reemplazar cualquier listener anterior si se recarga el HTML
        if (btnConfirmar) {
            btnConfirmar.onclick = function () {
                if (modalInstance) modalInstance.hide();

                if (valorUnico) {
                    const url = `../controllers/generar_certificado.php?valor_unico=${valorUnico}`;
                    window.open(url, '_blank');
                }
            };
        }

        // Manejo del formulario de inscripción via AJAX (Opcional, para no recargar todo)
        const formInscribir = document.getElementById('formInscribirCurso');
        if (formInscribir) {
            formInscribir.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('¿Confirmar inscripción?')) return;

                const formData = new FormData(this);
                formData.append('is_ajax', '1');
                // Usamos getAttribute para evitar DOM Clobbering con el input name="action"
                const actionUrl = this.getAttribute('action');
                
                fetch(actionUrl, { method: 'POST', body: formData })
                    .then(r => r.text())
                    .then(res => {
                        alert(res.includes('correctamente') ? 'Inscripción exitosa' : res);
                        if (typeof loadPage === 'function') {
                            loadPage('../views/curso.php', { id: cursoId });
                        } else {
                            window.location.reload();
                        }
           })
                    .catch(err => alert("Error procesando solicitud: " + err));
            });
        }

        // Manejo del formulario de cancelar inscripción
        const formCancelar = document.getElementById('formCancelarCurso');
        if (formCancelar) {
            formCancelar.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!confirm('¿Confirmar cancelación?')) return;

                const formData = new FormData(this);
                formData.append('is_ajax', '1');
                const actionUrl = this.getAttribute('action');

                fetch(actionUrl, { method: 'POST', body: formData })
                    .then(r => r.text())
                    .then(res => {
                        alert(res.includes('cancelado') ? 'Inscripción cancelada' : res);
                        if (typeof loadPage === 'function') {
                            loadPage('../views/curso.php', { id: cursoId });
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(err => alert("Error procesando solicitud: " + err));
            });
        }

    })();
</script>