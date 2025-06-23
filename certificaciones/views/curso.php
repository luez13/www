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
                    <div class="alert alert-info">Ya estás inscrito en este curso. Tu nota actual es: <strong><?php echo $inscripcion['nota'] ?? 'N/A'; ?></strong></div>
                    
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
                            <button id="btn-certificado" class="btn btn-success" type="button" onclick="obtenerCertificado('<?php echo $valor_unico; ?>')">
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
<script>
// 1. Función para detectar si es un dispositivo móvil
function isMobile() {
    return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

// 2. Función principal que decide qué hacer
function obtenerCertificado(valorUnico) {
    const urlCertificado = `../controllers/generar_certificado.php?valor_unico=${valorUnico}`;
    if (isMobile()) {
        // En móvil, abrimos la URL. La página del certificado se encargará de la descarga.
        window.open(urlCertificado, '_blank');
    } else {
        // En PC, abrimos en una nueva pestaña para visualización.
        window.open(urlCertificado, '_blank');
    }
}

document.addEventListener('DOMContentLoaded', (event) => {
    const botonCertificado = document.getElementById('btn-certificado');
    if (botonCertificado) {
        if (isMobile()) {
            botonCertificado.innerHTML = `<i class="bi bi-download me-2"></i> Descargar Certificado`;
        } else {
            botonCertificado.innerHTML = `<i class="bi bi-eye-fill me-2"></i> Ver Certificado`;
        }
    }
});

function obtenerCertificadoURL(valorUnico) {
    const url = `https://${window.location.host}/certifuptaisarec/controllers/generar_certificado.php?valor_unico=${valorUnico}`;
    document.getElementById('certificadoUrlContainer').innerHTML = `<a href="${url}" target="_blank">${url}</a>`;
}
</script>