<?php
// --- INICIO DE TU LÓGICA PHP (SIN CAMBIOS) ---
include '../config/model.php';
$db = new DB();
include '../models/curso.php';
$curso = new Curso($db);
$id_curso = $_GET['id'];
$curso_contenido = $curso->obtener_curso($id_curso);

$stmt_promotor = $db->prepare('SELECT nombre FROM cursos.usuarios WHERE id = :id_promotor');
$stmt_promotor->execute(['id_promotor' => $curso_contenido['promotor']]);
$promotor = $stmt_promotor->fetch();

$stmt_cupos = $db->prepare('SELECT COUNT(*) FROM cursos.certificaciones WHERE curso_id = :curso_id');
$stmt_cupos->execute(['curso_id' => $id_curso]);
$count = $stmt_cupos->fetchColumn();
$cupos_disponibles = $curso_contenido['limite_inscripciones'] - $count;

$stmt_inscripcion = $db->prepare('SELECT * FROM cursos.certificaciones WHERE curso_id = :curso_id AND id_usuario = :id_usuario');
$stmt_inscripcion->execute(['curso_id' => $id_curso, 'id_usuario' => $_SESSION['user_id']]);
$inscripcion = $stmt_inscripcion->fetch();

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$is_ajax) {
    include '../views/header.php'; // Asegúrate de que aquí se carga Bootstrap 5 CSS y el nuevo CDN de iconos.
}
// --- FIN DE TU LÓGICA PHP ---
?>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?php echo htmlspecialchars($curso_contenido['nombre_curso']); ?></h3>
            <p class="mb-0 fst-italic">Un curso de tipo: <?php echo htmlspecialchars($curso_contenido['tipo_curso']); ?></p>
        </div>
        <div class="card-body">
            <p class="card-text"><?php echo htmlspecialchars($curso_contenido['descripcion']); ?></p>
            
            <ul class="list-group list-group-flush mt-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-person-fill me-2"></i>Promotor</strong>
                    <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($promotor['nombre']); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-calendar-check me-2"></i>Estado</strong>
                    <?php if ($curso_contenido['estado']): ?>
                        <span class="badge bg-success rounded-pill">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-danger rounded-pill">Inactivo</span>
                    <?php endif; ?>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-people-fill me-2"></i>Cupos Disponibles</strong>
                    <span class="badge bg-info rounded-pill"><?php echo $cupos_disponibles; ?></span>
                </li>
                <li class="list-group-item">
                    <strong><i class="bi bi-clock-fill me-2"></i>Horario</strong>
                    <span><?php echo htmlspecialchars($curso_contenido['dias_clase']); ?> de <?php echo date("g:i a", strtotime($curso_contenido['horario_inicio'])); ?> a <?php echo date("g:i a", strtotime($curso_contenido['horario_fin'])); ?></span>
                </li>
                 <li class="list-group-item">
                    <strong><i class="bi bi-book-half me-2"></i>Requisitos Previos</strong>
                    <span><?php echo htmlspecialchars($curso_contenido['conocimientos_previos']); ?></span>
                </li>
            </ul>

            <h4 class="mt-4">Módulos del Curso</h4>
            <div class="accordion" id="accordionModulos">
                <?php
                $stmt_modulos = $db->prepare('SELECT * FROM cursos.modulos WHERE id_curso = :id_curso ORDER BY numero');
                $stmt_modulos->execute(['id_curso' => $id_curso]);
                $modulos = $stmt_modulos->fetchAll();
                foreach ($modulos as $modulo):
                ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo $modulo['id_modulo']; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $modulo['id_modulo']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $modulo['id_modulo']; ?>">
                                <strong>Módulo <?php echo $modulo['numero']; ?>:</strong>&nbsp;<?php echo htmlspecialchars($modulo['nombre_modulo']); ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo $modulo['id_modulo']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $modulo['id_modulo']; ?>" data-bs-parent="#accordionModulos">
                            <div class="accordion-body">
                                <p><strong>Contenido:</strong> <?php echo htmlspecialchars($modulo['contenido']); ?></p>
                                <p><strong>Actividad a Realizar:</strong> <?php echo htmlspecialchars($modulo['actividad']); ?></p>
                                <p><strong>Instrumento de Evaluación:</strong> <?php echo htmlspecialchars($modulo['instrumento']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card-footer text-center bg-light p-3">
            <?php if ($_SESSION['id_rol'] != 4): ?>
                <?php if (!$inscripcion): ?>
                    <form method="POST" action="../controllers/curso_acciones.php" onsubmit="return confirm('¿Estás seguro de que quieres inscribirte en este curso?')">
                        <input type="hidden" name="action" value="inscribirse">
                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="curso_id" value="<?php echo $id_curso; ?>">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-pencil-square me-2"></i>Inscribirse al curso</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">Ya estás inscrito en este curso. Tu nota actual es: <strong><?php echo isset($inscripcion['nota']) ? $inscripcion['nota'] : 'N/A'; ?></strong></div>
                                       
                    <div class="btn-group" role="group" aria-label="Acciones del curso">
                        <?php if ($inscripcion['completado'] != 1): ?>
                            <form class="d-inline" method="POST" action="../controllers/curso_acciones.php" onsubmit="return confirm('¿Estás seguro de que quieres cancelar tu inscripción?')">
                                <input type="hidden" name="action" value="cancelar_inscripcion">
                                <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
                                <input type="hidden" name="curso_id" value="<?php echo $id_curso; ?>">
                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-2"></i>Cancelar inscripción</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($inscripcion['completado'] == 1 && $inscripcion['pago'] == 1):
                            $valor_unico = $inscripcion['valor_unico'];
                        ?>
                            <div class="alert alert-light border border-secondary shadow-sm mb-3">
                                <h5 class="text-primary"><i class="bi bi-cash-coin me-2"></i>Legalización y Firmas</h5>
                                <p class="mb-1">Este certificado se genera digitalmente. Para obtener las firmas autorizadas y sellos institucionales:</p>
                                <ul class="mb-2 small">
                                    <li><strong>Arancel:</strong> 5 Euros (Calculados a la tasa del día BCV).</li>
                                    <li><strong>Procedimiento:</strong> Diríjase a la caja de la universidad con el comprobante de pago impreso.</li>
                                </ul>
                                <small class="text-muted fst-italic"><i class="bi bi-info-circle me-1"></i>Sin este proceso, el certificado se emitirá sin firmas.</small>
                            </div>
                            <button id="btn-certificado" class="btn btn-success" type="button" onclick="confirmarDescarga('<?php echo $valor_unico; ?>')">
                                <i class="bi bi-eye-fill me-2"></i> Ver Certificado
                            </button>
                            
                            <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#collapseURL" aria-expanded="false" aria-controls="collapseURL">
                                <i class="bi bi-link-45deg me-2"></i>Ver URL
                            </button>
                        <?php endif; ?>
                    </div>
                     <div class="collapse mt-3" id="collapseURL">
                        <div class="card card-body" id="certificadoUrlContainer">
                            </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
if (!$is_ajax) {
    include '../views/footer.php';
}
?>

<div class="modal fade" id="modalAranceles" tabindex="-1" aria-labelledby="modalArancelesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="modalArancelesLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Validación requerida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Estimado usuario, está a punto de visualizar su certificado.</p>
                
                <div class="alert alert-secondary">
                    <p class="mb-0"><strong>Recuerde:</strong> El documento se mostrará <u>sin firmas ni sellos</u> hasta que se valide el pago del arancel administrativo.</p>
                </div>

                <h6>Datos para el pago:</h6>
                <ul>
                    <li><strong>Monto:</strong> 5 EUR (Tasa BCV).</li>
                    <li><strong>Banco:</strong> [Nombre del Banco]</li>
                    <li><strong>Cuenta:</strong> 0000-0000-00-0000000000</li>
                    <li><strong>Beneficiario:</strong> UPTAIET</li>
                    <li><strong>RIF:</strong> G-20000000-0</li>
                </ul>
                <p class="small text-muted mb-0">Por favor consigne el comprobante en la caja de la universidad.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarContinuar">
                    <i class="bi bi-check-circle me-2"></i>Entendido, Ver Certificado
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variable global para guardar temporalmente el ID del certificado
let valorUnicoPendiente = null;
// Instancia del modal (se inicializa luego)
let modalAranceles = null;

function isMobile() {
    return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

// 1. Nueva función que INTERCEPTA el clic
function confirmarDescarga(valorUnico) {
    valorUnicoPendiente = valorUnico; // Guardamos el valor
    
    // Inicializamos el modal si no existe
    if (!modalAranceles) {
        modalAranceles = new bootstrap.Modal(document.getElementById('modalAranceles'));
    }
    
    // Mostramos el modal
    modalAranceles.show();
}

// 2. Función que ejecuta la acción real (llamada desde el botón del Modal)
function ejecutarObtencionCertificado() {
    if (!valorUnicoPendiente) return;

    // Ocultar modal
    if (modalAranceles) modalAranceles.hide();

    // Lógica original de abrir PDF
    const urlCertificado = `../controllers/generar_certificado.php?valor_unico=${valorUnicoPendiente}`;
    window.open(urlCertificado, '_blank');
}

// 3. Configuración inicial
(function() {
    const botonCertificado = document.getElementById('btn-certificado');
    const containerUrl = document.getElementById('certificadoUrlContainer');
    const btnConfirmarModal = document.getElementById('btnConfirmarContinuar');

    // Configurar el botón del modal para que ejecute la acción
    if (btnConfirmarModal) {
        btnConfirmarModal.addEventListener('click', ejecutarObtencionCertificado);
    }
    
    // Ajustar texto si es móvil (Estética)
    if (botonCertificado && isMobile()) {
        botonCertificado.innerHTML = `<i class="bi bi-download me-2"></i> Descargar Certificado`;
        botonCertificado.classList.remove('btn-success'); 
        botonCertificado.classList.add('btn-primary');
    }

    // Llenar la URL automáticamente
    if (containerUrl) {
        <?php if (isset($valor_unico)): ?>
            const valor = '<?php echo $valor_unico; ?>';
            const url = `${window.location.origin}/certifuptaisarec/controllers/generar_certificado.php?valor_unico=${valor}`;
            containerUrl.innerHTML = `<a href="${url}" target="_blank">${url}</a>`;
        <?php endif; ?>
    }
})();
</script>