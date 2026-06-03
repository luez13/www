<?php
// views/admin_db.php
require_once '../config/model.php';
require_once '../controllers/init.php';

// Solo el Administrador Superior tiene acceso (Rol 4)
if (!tieneAcceso([4])) {
    die('<div class="alert alert-danger text-center mt-5"><b>Acceso denegado:</b> Solo los administradores de nivel superior pueden acceder a esta herramienta técnica.</div>');
}

$db = new DB();
$mensaje = '';
$resultado_query = null;
$columnas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query_sql'])) {
    $sql = trim($_POST['query_sql']);
    if (!empty($sql)) {
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            // Si es un SELECT, obtenemos los resultados
            if (stripos($sql, 'SELECT') === 0) {
                $resultado_query = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($resultado_query)) {
                    $columnas = array_keys($resultado_query[0]);
                }
                $mensaje = '<div class="alert alert-success mt-3">Consulta ejecutada correctamente. ' . count($resultado_query) . ' filas devueltas.</div>';
            } else {
                $filas_afectadas = $stmt->rowCount();
                $mensaje = '<div class="alert alert-success mt-3">Consulta ejecutada correctamente. Filas afectadas: ' . $filas_afectadas . '</div>';
            }
        } catch (PDOException $e) {
            $mensaje = '<div class="alert alert-danger mt-3">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Obtener roles actuales para visualización rápida
$stmt_roles = $db->prepare("SELECT * FROM cursos.roles ORDER BY id_rol ASC");
$stmt_roles->execute();
$roles_actuales = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-database me-2"></i> Herramientas de Base de Datos</h1>
    </div>

    <?= $mensaje ?>

    <div class="row">
        <!-- Visualizar Roles -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Esquema de Roles (cursos.roles)</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID Rol</th>
                                <th>Nombre del Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles_actuales as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['id_rol']) ?></td>
                                    <td class="font-weight-bold"><?= htmlspecialchars($r['nombre_rol']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ejecutar Consultas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Ejecutar Consulta SQL</h6>
                </div>
                <div class="card-body border-left-danger border-4">
                    <p class="text-muted small"><strong>Peligro:</strong> Esta consola tiene acceso directo a la base de datos de producción. Use comandos UPDATE, DELETE y DROP con extrema precaución.</p>
                    
                    <form method="POST" id="formQuery">
                        <div class="form-group">
                            <textarea name="query_sql" id="query_sql" class="form-control font-monospace" rows="4" placeholder="SELECT * FROM cursos.usuarios LIMIT 10;"><?= isset($_POST['query_sql']) ? htmlspecialchars($_POST['query_sql']) : '' ?></textarea>
                        </div>
                        <button type="button" class="btn btn-danger mt-2" onclick="ejecutarConsulta()">
                            <i class="fas fa-bolt me-1"></i> Ejecutar SQL
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <?php if ($resultado_query !== null): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Resultados de la Consulta</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="thead-light">
                            <tr>
                                <?php foreach ($columnas as $col): ?>
                                    <th><?= htmlspecialchars($col) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resultado_query)): ?>
                                <tr>
                                    <td colspan="<?= count($columnas) ?>" class="text-center text-muted py-3">No hay datos para mostrar</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($resultado_query as $fila): ?>
                                    <tr>
                                        <?php foreach ($columnas as $col): ?>
                                            <td><?= htmlspecialchars($fila[$col] !== null ? $fila[$col] : 'NULL') ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function ejecutarConsulta() {
    var sql = document.getElementById('query_sql').value.trim();
    if (!sql) {
        alert("Por favor, ingrese una consulta SQL.");
        return;
    }
    
    var isPeligroso = /^(DROP|TRUNCATE|DELETE|UPDATE)/i.test(sql);
    if (isPeligroso) {
        if (!confirm("⚠️ ATENCIÓN: Estás a punto de ejecutar una consulta que modificará o eliminará datos. ¿Estás absolutamente seguro de continuar?")) {
            return;
        }
    }
    
    // Al no usar renderizado de plantillas puro, para enviar con ajax simulamos envio directo
    // Pero si estamos en el entorno SPA de la plataforma (loadPage):
    $.post('../views/admin_db.php', { query_sql: sql }, function(response) {
        // Asumiendo que loadPage reemplaza '#page-content'
        $('#page-content').html(response);
    }).fail(function() {
        alert("Error al procesar la consulta. ¿Verificaste la sintaxis?");
    });
}
</script>
