<?php
// Incluir el archivo header.php en views
include '../views/header.php';

// Obtener los datos del usuario actual
$user_id = $_SESSION['user_id'];

$db = new DB();

try {
    $stmt = $db->prepare('SELECT usuarios.*, roles.nombre_rol FROM cursos.usuarios INNER JOIN cursos.roles ON usuarios.id_rol = roles.id_rol WHERE usuarios.id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // Manejar el error
    $user = null;
}
?>
    <!-- cuerpo -->
    <div id="wrapper">

<!-- navegador -->
<ul class="navbar-nav bg-gradient-dark primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Menú de navegación -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#" onclick="loadProfile()">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="../public/assets/img/logo.png" width="50" height="50"/>
        </div>
        <div class="sidebar-brand-text mx-3">UPTAIET<sup></sup></div>
    </a>
    <!-- Dividir -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - panel -->
    <li class="nav-item active">
        <a class="nav-link" href="perfil.php">
            <i class="fas fa-desktop"></i>
            <span>INICIO</span></a>
    </li>

    <!-- Divi -->
    <hr class="sidebar-divider">

    <!-- Heading PAR 1-->
    <div class="sidebar-heading">
        MENÚ
    </div>
    <!-- Nav Item - Utilities Collapse Menu -->
    <?php if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4): ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-flag"></i>
            <span>RUTA APRENDIZAJE</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Rutas Activas:</h6>
                <a class="collapse-item" href="#" onclick="loadCategory('masterclass', true)">MasterClass</a>
                <a class="collapse-item" href="#" onclick="loadCategory('talleres', true)">Talleres</a>
                <a class="collapse-item" href="#" onclick="loadCategory('cursos', true)">Cursos</a>
                <a class="collapse-item" href="#" onclick="loadCategory('seminarios', true)">Seminarios</a>
                <a class="collapse-item" href="#" onclick="loadCategory('diplomados', true)">Diplomados</a>
                <a class="collapse-item" href="#" onclick="loadCategory('congreso', true)">Congreso</a>
                <a class="collapse-item" href="#" onclick="loadCategory('charla', true)">Charla</a>
                <h6 class="collapse-header">Rutas Cerradas:</h6>
                <a class="collapse-item" href="#" onclick="loadCategory('masterclass', false)">MasterClass</a>
                <a class="collapse-item" href="#" onclick="loadCategory('talleres', false)">Talleres</a>
                <a class="collapse-item" href="#" onclick="loadCategory('cursos', false)">Cursos</a>
                <a class="collapse-item" href="#" onclick="loadCategory('seminarios', false)">Seminarios</a>
                <a class="collapse-item" href="#" onclick="loadCategory('diplomados', false)">Diplomados</a>
                <a class="collapse-item" href="#" onclick="loadCategory('congreso', false)">Congreso</a>
                <a class="collapse-item" href="#" onclick="loadCategory('charla', false)">Charla</a>
            </div>
        </div>
    </li>
    <?php endif; ?>

<!-- Nav Item - Historial -->
<?php if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4): ?>
<hr class="sidebar-divider">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseHistorial"
        aria-expanded="true" aria-controls="collapseHistorial">
        <i class="fas fa-folder-open"></i>
        <span>Historial</span>
    </a>
    <div id="collapseHistorial" class="collapse" aria-labelledby="headingHistorial" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Rutas:</h6>
            <a class="collapse-item" href="#" onclick="loadPage('../views/historial.php?action=inscritos')">Cursos Inscritos</a>
            <a class="collapse-item" href="#" onclick="loadPage('../views/historial.php?action=finalizados')">Cursos Finalizados</a>
        </div>
    </div>
</li>
<?php endif; ?>

<!-- Nav Item - Registro Propuesta -->
<?php if ($_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4): ?>
<hr class="sidebar-divider">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
        aria-expanded="true" aria-controls="collapsePages">
        <i class="fas fa-spinner fa-folder"></i>
        <span>REGISTRO PROPUESTA</span>
    </a>
    <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <div class="bg-white py-2 collapse-inner rounded">
                <?php if ($_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 3): ?>
                <h6 class="collapse-header">Facilitadores</h6>
                <a class="collapse-item" href="#" onclick="loadPage('gestion_cursos.php?action=crear')">Postular Propuesta</a>
                <a class="collapse-item" href="#" onclick="loadPage('gestion_cursos.php?action=ver')">Ver Postulaciones</a>
                <?php endif; ?>
                <?php if ($_SESSION['id_rol'] == 4 || $_SESSION['id_rol'] == 3): ?>
                <h6 class="collapse-header">Administrador</h6>
                <a class="collapse-item" href="#" onclick="loadPage('editar_cursos.php')">Verificar Postulación</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</li>
<?php endif; ?>

<!-- Nav Item - Menú de Usuarios -->
<?php if ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4): ?>
<hr class="sidebar-divider">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUsuarios" aria-expanded="true" aria-controls="collapseUsuarios">
        <i class="fas fa-users"></i>
        <span>Usuarios</span>
    </a>
    <div id="collapseUsuarios" class="collapse" aria-labelledby="headingUsuarios" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="#" onclick="loadPage('usuarios.php')">Verificación Usuarios</a>
        </div>
    </div>
</li>
<?php endif; ?>
</ul>
</ul>
<!-- End of Sidebar -->

<!-- Wrapper cuerpo pag -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Cuerpo Interno -->
    <div id="content">

        <!-- Topbar ojo navegador izq-->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Toda la estructura del nav izq Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Search 
            <form
                class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                        aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
            -->
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <li class="nav-item dropdown no-arrow d-sm-none">
                    <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                    <!-- Dropdown - Messages -->
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                        aria-labelledby="searchDropdown">
                        <form class="form-inline mr-auto w-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 small"
                                    placeholder="Search for..." aria-label="Search"
                                    aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>
                <div class="topbar-divider d-none d-sm-block"></div>

<!-- Nav Item - Usuario sup izq-->
<li class="nav-item dropdown no-arrow">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
        <img class="img-profile rounded-circle" src="../public/assets/img/undraw_profile.svg">
    </a>
    <!-- Editar Inf -->
    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editUserModal">
            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
            Editar Datos
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
            Cerrar
        </a>
    </div>
</li>
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid" id="page-content">
            <!-- Content Row -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="text-center">
                        <h1 class="h3 mb-4 text-gray-800">Perfil del Usuario</h1>
                        <?php if ($user): ?>
                            <p class="user-info"><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre']) ?></p>
                            <p class="user-info"><strong>Email:</strong> <?= htmlspecialchars($user['correo']) ?></p>
                            <p class="user-info"><strong>Rol:</strong> <?= strtoupper(htmlspecialchars($user['nombre_rol'])) ?></p>
                        <?php else: ?>
                            <p class="user-info">No se pudieron obtener los datos del usuario.</p>
                        <?php endif; ?>
                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                             src="../public/assets/img/undraw_posting_photo.svg" alt="...">
                    </div>
                </div>
            </div>
        </div>
        <!-- fin cuerpo-->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Cerrar seccion user-->
    <!-- <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a> -->

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">¿Deseas cerrar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Estas cerrando la sesión como usuario</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <form class="form-inline" action="../controllers/autenticacion.php" method="post">
                        <input type="hidden" name="action" value="logout">
                        <input class="btn btn-primary" type="submit" value="Cerrar sesión">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="modalConfirm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Datos del Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="action" value="editar_perfil">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($_SESSION['nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" class="form-control" value="<?= htmlspecialchars($_SESSION['apellido']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo:</label>
                        <input type="email" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($_SESSION['correo']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula:</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" value="<?= htmlspecialchars($_SESSION['cedula']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nueva_contrasena">Nueva Contraseña:</label>
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </form>
                <div class="form-group">
                    <label for="firma_digital">Subir firma digital:</label>
                    <input type="file" class="form-control-file" id="firma_digital" name="firma_digital">
                </div>
                <button type="button" class="btn btn-secondary" onclick="subirFirmaDigital()">Subir firma digital</button>
            </div>
        </div>
    </div>
</div>

<?php
include '../views/footer.php';
?>

<script>
function loadPage(page, params = {}) {
    console.log('Loading page:', page, 'with params:', params); // Para depuración

    $.ajax({
        url: page,
        method: 'GET',
        data: params,
        success: function(data) {
            $('#page-content').html(data); // Asegúrate de que el ID del contenedor sea correcto
            reapplyEvents(); // Si necesitas reaplicar eventos de JavaScript
        },
        error: function(xhr, status, error) {
            console.error('Error loading page:', page, error);
            alert('Error al cargar la página.');
        }
    });
}

let selectedUsers = new Set(); // Usar un Set para almacenar los IDs de usuarios seleccionados

function reapplyEvents() {
    // Remove previous event listeners
    $('.editarCursoForm').off('submit');
    $('.page-link-nav').off('click');
    $('#busqueda-input').off('input');
    $(document).off('click', '.pagination-link');
    $('.usuario-checkbox').off('change');
    $('#inscribir-usuarios-btn').off('click');

    // Add new event listeners
    $('.editarCursoForm').on('submit', function(event) {
        event.preventDefault();
        var form = this;
        var formData = new FormData(form);
        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.includes('El curso se ha editado correctamente')) {
                alert('El curso se ha editado correctamente');
                var page = document.querySelector('.page-item.active .page-link').dataset.page;
                loadPage('editar_cursos.php', { page: page });
            } else {
                alert('Hubo un error al editar el curso: ' + result);
            }
        })
        .catch(error => {
            alert('Hubo un error al procesar la solicitud: ' + error);
        });
    });

    $('.page-link-nav').on('click', function(event) {
        event.preventDefault();
        var page = $(this).data('page');
        loadPage('usuarios.php', { page: page });
    });

    $('#busqueda-input').on('input', function() {
        var inputField = $(this);
        var busqueda = inputField.val().replace(/%/g, ''); // Eliminar caracteres %
        inputField.val(busqueda); // Actualizar el valor del campo para reflejar el cambio
        if (inputField.length > 0 && typeof inputField[0].setSelectionRange === 'function') {
            setTimeout(() => {
                inputField[0].setSelectionRange(busqueda.length, busqueda.length); // Mantener el cursor al final del texto
            }, 0);
        }
        loadPage('../controllers/usuarios_controlador.php', { action: 'buscar', busqueda: busqueda });
    });

    $(document).on('click', '.pagination-link', function(event) {
        event.preventDefault();
        var page = $(this).data('page');
        var busqueda = $('#busqueda-input').val();
        loadPage('../controllers/usuarios_controlador.php', { page: page, busqueda: busqueda });
    });

    $('.usuario-checkbox').on('change', function() {
        var userId = $(this).data('id');
        if ($(this).is(':checked')) {
            selectedUsers.add(userId); // Agregar el usuario al conjunto de seleccionados
        } else {
            selectedUsers.delete(userId); // Eliminar el usuario del conjunto de seleccionados
        }
    });

    // Restaurar la selección de usuarios al recargar la página
    $('.usuario-checkbox').each(function() {
        var userId = $(this).data('id');
        if (selectedUsers.has(userId)) {
            $(this).prop('checked', true);
        }
    });

    // Manejar el botón de acción con usuarios seleccionados
    $('#inscribir-usuarios-btn').on('click', function() {
        if (selectedUsers.size > 0) {
            var cursoId = $('#curso-id').val(); // Obtener el ID del curso seleccionado
            var usuariosArray = Array.from(selectedUsers); // Convertir el conjunto a un array
            $.ajax({
                url: '../controllers/usuarios_controlador.php',
                method: 'POST',
                data: {
                    action: 'inscribir_usuarios',
                    usuarios: usuariosArray,
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

    // Add event listeners for inscripcion forms
    $('form[id^="inscripcionForm"]').on('submit', function(event) {
        event.preventDefault();
        var form = this;
        var formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.includes('Te has inscrito correctamente en el curso')) {
                alert('Usuario inscrito correctamente.');
                var idCurso = form.querySelector('input[name="curso_id"]').value;
                loadPage('buscar.php', { id_curso: idCurso });
            } else {
                alert('Hubo un error al inscribir al usuario: ' + result);
            }
        })
        .catch(error => {
            alert('Hubo un error al procesar la solicitud: ' + error);
        });
    });
}

// Call reapplyEvents when the document is ready
$(document).ready(function() {
    reapplyEvents();
});

document.addEventListener('DOMContentLoaded', function() {
    reapplyEvents();
});

function applySidebarToggle() {
    $('#sidebarToggleTop').off('click').on('click', function() {
        $('#accordionSidebar').toggleClass('toggled');
    });
}

$(document).ready(function() {
    applySidebarToggle();
});

function loadProfile() {
    $.ajax({
        url: '../public/perfil.php',
        method: 'GET',
        success: function(data) {
            $('#page-content').html($(data).find('#page-content').html());
        },
        error: function() {
            alert('Error al cargar el perfil.');
        }
    });
}

function loadCategory(tipo_curso, estado) {
    $.ajax({
        url: '../public/cursos.php',
        method: 'GET',
        data: { tipo_curso: tipo_curso, estado: estado },
        success: function(data) {
            $('#page-content').html(data);
        },
        error: function() {
            alert('Error al cargar la categoría.');
        }
    });
}

function showAlert(message, redirect = false) {
    alert(message);
    if (redirect) {
        loadProfile();
    }
}

function showModal(message, callback) {
    $('#modalMessage').text(message);
    $('#modalConfirm').off('click').on('click', function() {
        callback();
        $('#confirmationModal').modal('hide');
    });
    $('#confirmationModal').modal('show');
}

$(document).ready(function() {
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '../models/datos_usuario.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response);
                $('#editUserModal').modal('hide');

                $('#userDropdown span').text($('#nombre').val());
                $('#userDropdown img').attr('src', '../public/assets/img/undraw_profile.svg');

                $('.user-info').each(function() {
                    var field = $(this).find('strong').text().toLowerCase();
                    if (field.includes('nombre')) {
                        $(this).find('span').text($('#nombre').val());
                    } else if (field.includes('apellido')) {
                        $(this).find('span').text($('#apellido').val());
                    } else if (field.includes('correo')) {
                        $(this).find('span').text($('#correo').val());
                    } else if (field.includes('cédula')) {
                        $(this).find('span').text($('#cedula').val());
                    }
                });
            },
            error: function() {
                alert('Error al editar los datos.');
            }
        });
    });
});

function loadCourse(courseId) {
    const url = '../views/curso.php';
    console.log('Cargando curso con ID:', courseId);
    console.log('URL:', url);
    $.ajax({
        url: url,
        method: 'GET',
        data: { id: courseId },
        success: function(data) {
            console.log('Datos recibidos:', data);
            $('#page-content').html(data);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar el curso:', error);
            alert('Error al cargar el curso.');
        }
    });
}

function loadHistorial(action) {
    const url = '../views/historial.php';
    console.log('Cargando historial con acción:', action);
    console.log('URL:', url);
    $.ajax({
        url: url,
        method: 'GET',
        data: { action: action },
        success: function(data) {
            console.log('Datos recibidos:', data);
            $('#page-content').html(data);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar el historial:', error);
            alert('Error al cargar el historial.');
        }
    });
}

function subirFirmaDigital() {
    var formData = new FormData();
    var fileInput = document.getElementById('firma_digital');
    formData.append('firma_digital', fileInput.files[0]);

    fetch('../controllers/subir_firma.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              alert(data.message);
          } else if (data.file_exists) {
              if (confirm('El archivo ya existe. ¿Desea sobreescribirlo?')) {
                  formData.append('overwrite', 'true');
                  fetch('../controllers/subir_firma.php', {
                      method: 'POST',
                      body: formData
                  }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Firma digital sobreescrita exitosamente');
                        } else {
                            alert('Error al subir la firma digital: ' + data.message);
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                    });
              }
          } else {
              alert('Error al subir la firma digital: ' + data.message);
          }
      }).catch(error => {
          console.error('Error:', error);
      });
}
function inscribirUsuario(userId) {
    var form = document.getElementById('inscripcionForm-' + userId);
    var formData = new FormData(form);

    var actionUrl = form.getAttribute('action');
    var idCursoElement = form.querySelector('input[name="curso_id"]');
    var currentPageElement = form.querySelector('input[name="page"]');
    var idCurso = idCursoElement ? idCursoElement.value : null;
    var currentPage = currentPageElement ? currentPageElement.value : 1; // Asume página 1 si no se encuentra

    if (!idCurso) {
        console.error('Error: id_curso is null.');
        alert('Error: No se pudo determinar el curso.');
        return;
    }

    fetch(actionUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        console.log('Result:', result); // Imprimir el resultado completo para depuración

        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = result;

        var alertElement = tempDiv.querySelector('.alert');
        var alertMessage = alertElement ? alertElement.innerText.trim() : 'Solicitud procesada correctamente.';

        if (alertMessage.includes('correctamente')) {
            alert(alertMessage); // Mostrar solo el mensaje sin HTML
            loadPage('../controllers/buscar.php', { id_curso: idCurso, page: currentPage });
        } else if (alertMessage.includes('Ha ocurrido un error') || alertMessage.includes('Datos de inscripción inválidos')) {
            alert('Hubo un error al procesar la solicitud: ' + alertMessage);
        } else {
            // Manejar respuestas que no sean errores explícitos
            alert(alertMessage);
            loadPage('../controllers/buscar.php', { id_curso: idCurso, page: currentPage });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error al procesar la solicitud: ' + error);
    });
}
</script>