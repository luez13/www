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

// Al ser huérfano, le mostramos todas las activas para que escoja una y se vincule
$stmtExt = $conn->prepare('SELECT id_extension, nombre_extension FROM cursos.extensiones WHERE activa = TRUE');
$stmtExt->execute();
$extensiones = $stmtExt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../views/header.php';
?>

<div class="container mt-5" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-10 text-center">
            <h2 class="mb-4">Bienvenido(a) a la Plataforma</h2>
            <p class="text-muted mb-5">Para continuar, por favor selecciona el área o dependencia a la que deseas ingresar (esto configurará tu panel de control).</p>
            
            <?php if(isset($_SESSION['auth_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['auth_error']) ?>
                    <?php unset($_SESSION['auth_error']); ?>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center gap-3">
                <?php foreach ($extensiones as $ext): ?>
                <div class="col-md-5 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow border-primary" style="cursor:pointer; transition: 0.3s; border-width: 2px;" onclick="document.getElementById('form-ext-<?= $ext['id_extension'] ?>').submit();">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center py-5">
                            <i class="fas fa-building fa-4x mb-3 text-primary"></i>
                            <h4 class="card-title text-dark m-0"><?= htmlspecialchars($ext['nombre_extension']) ?></h4>
                        </div>
                    </div>
                    <form id="form-ext-<?= $ext['id_extension'] ?>" action="../controllers/autenticacion.php" method="POST" style="display:none;">
                        <input type="hidden" name="action" value="asignar_sede_inicial">
                        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
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
