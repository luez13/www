<?php
// views/historial.php

// Incluir configuraciones
include '../config/model.php';
require_once '../controllers/init.php';

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Acceso denegado. Por favor inicie sesión.</div>';
    exit;
}

$user_id = $_SESSION['user_id'];
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Determinar acción y título
$action = isset($_GET['action']) ? $_GET['action'] : '';
$titulo = ($action == 'finalizados') ? 'Historial Académico' : 'Mis Cursos Activos';
$clase_borde = ($action == 'finalizados') ? 'border-left-success' : 'border-left-primary';
$icono_titulo = ($action == 'finalizados') ? 'fa-award' : 'fa-book-reader';

?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas <?= $icono_titulo ?> mr-2"></i><?= $titulo ?>
        </h1>
    </div>

    <?php if ($action == 'inscritos'): ?>
        <?php
        try {
            // Consulta mejorada para traer datos relevantes
            $stmt = $db->prepare('SELECT c.id_curso, c.nombre_curso, c.descripcion, c.inicio_mes, c.horario_inicio, c.horario_fin 
                                  FROM cursos.cursos c 
                                  JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id 
                                  WHERE ce.id_usuario = :id_usuario AND ce.completado = false');
            $stmt->execute(['id_usuario' => $user_id]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            $cursos = [];
        }
        ?>

        <div class="row">
            <?php if (empty($cursos)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-lg mr-2"></i> No tienes cursos activos en este momento.
                        <br><a href="#" onclick="loadCategory('curso', true)" class="alert-link">¡Explora nuestra oferta académica!</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($cursos as $curso): ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            En Curso
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($curso['nombre_curso']) ?>
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">
                                            <?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...' ?>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="far fa-calendar-alt"></i> Inicio: <?= $curso['inicio_mes'] ?></small>
                                    <button class="btn btn-primary btn-sm" onclick="loadCourse(<?= $curso['id_curso'] ?>)">
                                        Continuar <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php elseif ($action == 'finalizados'): ?>
        <?php
        try {
            $stmt = $db->prepare('SELECT c.id_curso, c.nombre_curso, c.fecha_finalizacion, ce.nota, ce.valor_unico 
                                  FROM cursos.cursos c 
                                  JOIN cursos.certificaciones ce ON c.id_curso = ce.curso_id 
                                  WHERE ce.id_usuario = :id_usuario AND ce.completado = true');
            $stmt->execute(['id_usuario' => $user_id]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            $cursos = [];
        }
        ?>

        <div class="card bg-warning text-white shadow mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="col">
                        <h5 class="font-weight-bold">Aviso sobre Certificación</h5>
                        <p class="mb-0 small">La emisión digital es automática. Para la <strong>validación oficial (firmas y sellos)</strong>, es necesario consignar los aranceles administrativos.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (empty($cursos)): ?>
                <div class="col-12">
                    <div class="alert alert-secondary text-center">Aún no has finalizado ningún curso.</div>
                </div>
            <?php else: ?>
                <?php foreach ($cursos as $curso): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Finalizado el <?= date('d/m/Y', strtotime($curso['fecha_finalizacion'])) ?>
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= htmlspecialchars($curso['nombre_curso']) ?>
                                        </div>
                                        <?php if(isset($curso['nota'])): ?>
                                            <div class="mt-2 badge bg-success text-white">Nota: <?= $curso['nota'] ?> pts</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-certificate fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-end">
                                    <a href="#" onclick="loadCourse(<?= $curso['id_curso'] ?>)" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!$is_ajax) { include 'footer.php'; } ?>