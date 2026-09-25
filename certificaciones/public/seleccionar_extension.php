<?php
require_once __DIR__ . '/../config/model.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$db = new DB();
$conn = $db->getConn();
$stmtExt = $conn->prepare('
    SELECT e.id_extension, e.nombre_extension 
    FROM cursos.usuarios_extensiones ue 
    JOIN cursos.extensiones e ON ue.id_extension = e.id_extension 
    WHERE ue.id_usuario = :id_usuario AND ue.activo = TRUE AND e.activa = TRUE
');
$stmtExt->execute([':id_usuario' => $_SESSION['user_id']]);
$extensiones = $stmtExt->fetchAll(PDO::FETCH_ASSOC);

if (count($extensiones) == 0) {
    session_destroy();
    session_start();
    $_SESSION['auth_error'] = "Tu usuario no tiene asignada ninguna dependencia operativa.";
    header('Location: index.php');
    exit;
} elseif (count($extensiones) == 1 && !isset($_GET['force'])) {
    $_SESSION['id_extension'] = $extensiones[0]['id_extension'];
    header('Location: perfil.php');
    exit;
}

include __DIR__ . '/../views/header.php';
?>

<div class="container mt-5" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-10 text-center">
            <h2 class="mb-4">Selecciona tu Entorno de Trabajo</h2>
            <p class="text-muted mb-5">Tu usuario tiene acceso a múltiples dependencias. Por favor, selecciona a cuál deseas ingresar en esta sesión.</p>
            
            <div class="row justify-content-center gap-3">
                <?php foreach ($extensiones as $ext): ?>
                <div class="col-md-5 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow border-primary" style="cursor:pointer; transition: 0.3s; border-width: 2px;" onclick="document.getElementById('form-ext-<?= $ext['id_extension'] ?>').submit();">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center py-5">
                            <i class="fas fa-building fa-4x mb-3 text-primary"></i>
                            <h4 class="card-title text-dark m-0"><?= htmlspecialchars($ext['nombre_extension']) ?></h4>
                        </div>
                    </div>
                    <form id="form-ext-<?= $ext['id_extension'] ?>" action="../controllers/set_extension.php" method="POST" style="display:none;">
                        <input type="hidden" name="id_extension" value="<?= $ext['id_extension'] ?>">
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4">
                <a href="../controllers/logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    transform: translateY(-5px);
    background-color: #f8f9fa;
}
</style>

<?php include __DIR__ . '/../views/footer.php'; ?>
