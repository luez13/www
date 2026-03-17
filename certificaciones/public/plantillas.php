<?php
session_start();
// Validar que el usuario sea administrador (rol 4)
if (!isset($_SESSION['user_rol']) || (int)$_SESSION['user_rol'] !== 4) {
    header("Location: index.php");
    exit();
}

require_once '../config/model.php';
require_once '../models/Plantilla.php';

$db = new DB();
$plantillaModel = new Plantilla($db);

$plantillas = $plantillaModel->listarTodas();
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="h3 mb-2 text-gray-800"><i class="fas fa-certificate me-2 text-primary"></i>Gestión de Plantillas de Certificados</h3>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Listado de Plantillas</h5>
                        <p>Añade o modifica las plantillas que el sistema usará para generar la visualización de los certificados.</p>
                        
                        <?php if (isset($_SESSION['mensaje_plantilla'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($_SESSION['mensaje_plantilla']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['mensaje_plantilla']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_plantilla'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error_plantilla']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error_plantilla']); ?>
                        <?php endif; ?>

                        <!-- Botón Añadir Plantilla -->
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#agregarPlantillaModal">
                            <i class="bi bi-plus-circle"></i> Añadir Plantilla
                        </button>

                        <!-- Tabla con el listado -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mt-3 aling-middle" style="width: 100%;">
                                <thead style="background-color: #2b2b2b; color: white;">
                                    <tr>
                                        <th scope="col" style="color: white;">ID</th>
                                        <th scope="col" style="color: white;">Nombre Comercial</th>
                                        <th scope="col" style="color: white;">Archivo Físico (.php)</th>
                                        <th scope="col" style="color: white;">Estado</th>
                                        <th scope="col" style="color: white;text-align: center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($plantillas) > 0): ?>
                                        <?php foreach ($plantillas as $plantilla): ?>
                                            <tr>
                                                <th scope="row"><?php echo $plantilla['id']; ?></th>
                                                <td><?php echo htmlspecialchars($plantilla['nombre']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($plantilla['archivo_vista']); ?></span></td>
                                                <td>
                                                    <?php if ($plantilla['es_defecto']): ?>
                                                        <span class="badge bg-success">Por Defecto</span>
                                                    <?php else: ?>
                                                        <form method="POST" action="../controllers/PlantillaController.php" style="display:inline;">
                                                            <input type="hidden" name="action" value="hacer_defecto">
                                                            <input type="hidden" name="id" value="<?php echo $plantilla['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Hacer Defecto">Fijar por defecto</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <!-- Botón Editar Manda Datos al Modal  -->
                                                    <button type="button" class="btn btn-warning btn-sm btn-editar" 
                                                        data-id="<?php echo $plantilla['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($plantilla['nombre'], ENT_QUOTES); ?>"
                                                        data-archivo="<?php echo htmlspecialchars($plantilla['archivo_vista'], ENT_QUOTES); ?>"
                                                        data-defecto="<?php echo $plantilla['es_defecto'] ? '1' : '0'; ?>"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    
                                                    <!-- Borrar (Formulario) -->
                                                    <?php if (!$plantilla['es_defecto']): ?>
                                                    <form method="POST" action="../controllers/PlantillaController.php" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta plantilla? Los certificados viejos vinculados podrían dar error al consultarse si se borra el archivo.');">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo $plantilla['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i> Eliminar</button>
                                                    </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No hay plantillas registradas en el sistema.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Plantilla -->
<div class="modal fade" id="agregarPlantillaModal" tabindex="-1" aria-labelledby="agregarPlantillaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarPlantillaModalLabel">Añadir Nueva Plantilla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../controllers/PlantillaController.php" method="POST" id="formPlantilla">
                    <input type="hidden" name="action" id="formAccion" value="crear">
                    <input type="hidden" name="id" id="formId" value="">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Comercial de la Plantilla</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej. Certificado Oscuro (Diplomados)" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivo_vista" class="form-label">Nombre del Archivo Físico (.php)</label>
                        <select class="form-select" id="archivo_vista" name="archivo_vista" required>
                            <option value="">Seleccione un archivo...</option>
                            <?php 
                            $dir_certs = __DIR__ . '/../views/certificados/';
                            if (is_dir($dir_certs)) {
                                $archivos_certs = scandir($dir_certs);
                                foreach ($archivos_certs as $archivo_cert) {
                                    if (pathinfo($archivo_cert, PATHINFO_EXTENSION) === 'php') {
                                        echo '<option value="' . htmlspecialchars($archivo_cert) . '">' . htmlspecialchars($archivo_cert) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <div class="form-text">Debe ser uno de los archivos disponibles en <code>views/certificados/</code>.</div>
                        
                        <div class="form-check form-switch mt-2 d-flex justify-content-end align-items-center w-100">
                            <label class="form-check-label me-5 order-first text-muted" for="es_defecto">Establecer como diseño por defecto Global</label>
                            <input class="form-check-input order-last" type="checkbox" id="es_defecto" name="es_defecto" value="1" style="transform: scale(1.2);">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarPlantilla">Guardar Plantilla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para cargar datos en el modal al editar -->
<script>
(function() {
    const editButtons = document.querySelectorAll('.btn-editar');
    const modalLabel = document.getElementById('agregarPlantillaModalLabel');
    const btnSubmit = document.getElementById('btnGuardarPlantilla');
    const formAccion = document.getElementById('formAccion');
    const formId = document.getElementById('formId');
    const inputNombre = document.getElementById('nombre');
    const inputArchivo = document.getElementById('archivo_vista');
    const inputDefecto = document.getElementById('es_defecto');
    
    // Al abrir modal para AÑADIR (limpiar campos)
    const modalElement = document.getElementById('agregarPlantillaModal');
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            modalLabel.innerText = 'Añadir Nueva Plantilla';
            btnSubmit.innerText = 'Guardar Plantilla';
            formAccion.value = 'crear';
            formId.value = '';
            inputNombre.value = '';
            inputArchivo.value = '';
            inputDefecto.checked = false;
            
            // Quitar required momentaneamente si falla
            inputNombre.classList.remove('is-invalid');
            inputArchivo.classList.remove('is-invalid');
        });
    }

    // Al abrir modal para EDITAR (cargar campos)
    editButtons.forEach(button => {
        // Remover event listeners previos para evitar duplicidad si se recarga la vista
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            modalLabel.innerText = 'Editar Plantilla';
            btnSubmit.innerText = 'Actualizar Datos';
            formAccion.value = 'actualizar';
            
            formId.value = this.getAttribute('data-id');
            inputNombre.value = this.getAttribute('data-nombre');
            inputArchivo.value = this.getAttribute('data-archivo');
            inputDefecto.checked = this.getAttribute('data-defecto') === '1';
            
            // Mostrar modal
            var modal = new bootstrap.Modal(document.getElementById('agregarPlantillaModal'));
            modal.show();
        });
    });
})();
</script>
