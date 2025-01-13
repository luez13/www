<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Consulta para contar el total de cursos
$stmt = $db->prepare("SELECT COUNT(*) FROM cursos.cursos");
$stmt->execute();
$total_cursos = $stmt->fetchColumn();
$total_pages = ceil($total_cursos / $limit);

// Consulta para obtener los cursos paginados
$stmt = $db->prepare("SELECT id_curso, nombre_curso, descripcion, estado, autorizacion FROM cursos.cursos LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Gestión de Usuarios</h3>
    <form id="buscar-usuarios-form" method="GET" action="javascript:void(0);">
        <div class="input-group mb-3">
            <input type="text" class="form-control" name="busqueda" id="busqueda-input" placeholder="Buscar usuarios..." value="<?= htmlspecialchars($busqueda); ?>">
        </div>
    </form>
    
    <div class="list-group" id="user-list">
        <?php foreach ($usuarios as $usuario): ?>
            <div class="list-group-item">
                <h5 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h5>
                <input type="checkbox" class="usuario-checkbox" data-id="<?= htmlspecialchars($usuario['id']); ?>">
                <p class="mb-1">Cédula: <?= htmlspecialchars($usuario['cedula']); ?></p>
                <small>Email: <?= htmlspecialchars($usuario['correo']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Botón para abrir el modal -->
    <button id="abrir-modal-btn" class="btn btn-primary mt-3">Agregar al curso</button>
    
    <!-- Modal para seleccionar curso -->
    <div class="modal fade" id="seleccionarCursoModal" tabindex="-1" aria-labelledby="seleccionarCursoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="seleccionarCursoModalLabel">Seleccionar curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="buscar-curso-input" class="form-control" placeholder="Buscar curso...">
                    <select id="curso-id" class="form-control mt-3">
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= htmlspecialchars($curso['id_curso']); ?>"><?= htmlspecialchars($curso['nombre_curso']); ?> - <?= htmlspecialchars($curso['descripcion']); ?> - <?= $curso['estado'] ? 'Activo' : 'Inactivo'; ?> - <?= $curso['autorizacion'] ? 'Autorizado' : 'No Autorizado'; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="inscribir-usuarios-btn" class="btn btn-primary">Inscribir usuarios</button>
                </div>
            </div>
        </div>
    </div>

    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link pagination-link" href="#" data-page="<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script>
    // Filtrar los cursos en tiempo real dentro del modal
    document.getElementById('buscar-curso-input').addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        var options = document.getElementById('curso-id').getElementsByTagName('option');
        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var optionText = option.textContent.toLowerCase();
            if (optionText.indexOf(filter) > -1) {
                option.style.display = "";
            } else {
                option.style.display = "none";
            }
        }
    });

    // Manejar el botón para abrir el modal
    document.getElementById('abrir-modal-btn').addEventListener('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('seleccionarCursoModal'));
        modal.show();
    });

    // Manejar el botón para inscribir usuarios seleccionados
    document.getElementById('inscribir-usuarios-btn').addEventListener('click', function() {
        var selectedUsers = [...document.querySelectorAll('.usuario-checkbox:checked')].map(cb => cb.dataset.id);
        if (selectedUsers.length > 0) {
            var cursoId = document.getElementById('curso-id').value; // Obtener el ID del curso seleccionado

            $.ajax({
                url: '../controllers/usuarios_controlador.php',
                method: 'POST',
                data: {
                    action: 'inscribir_usuarios',
                    usuarios: selectedUsers,
                    curso_id: cursoId
                },
                success: function(response) {
                    alert('Usuarios registrados correctamente en el curso.');
                    location.reload(); // Recargar la página para reflejar los cambios
                },
                error: function() {
                    alert('Hubo un error al registrar los usuarios en el curso.');
                }
            });
        } else {
            alert('No hay usuarios seleccionados.');
        }
    });
</script>