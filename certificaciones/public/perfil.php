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
    
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#" onclick="loadProfile()">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="../public/assets/img/logo.png" width="50" height="50" alt="Logo UPTAIET"/>
        </div>
        <div class="sidebar-brand-text mx-3">UPTAIET</div>
    </a>
    
    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="perfil.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Panel Principal</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Área Académica</div>

    <?php if (in_array($_SESSION['id_rol'], [1, 2, 3, 4])): ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCatalogo"
            aria-expanded="true" aria-controls="collapseCatalogo">
            <i class="fas fa-fw fa-book-reader"></i>
            <span>Catálogo de Cursos</span>
        </a>
        <div id="collapseCatalogo" class="collapse" aria-labelledby="headingCatalogo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-primary">Oferta Activa:</h6>
                <a class="collapse-item" href="#" onclick="loadCategory('masterclass', true)"><i class="fas fa-chalkboard me-2 text-muted"></i>MasterClass</a>
                <a class="collapse-item" href="#" onclick="loadCategory('taller', true)"><i class="fas fa-tools me-2 text-muted"></i>Talleres</a>
                <a class="collapse-item" href="#" onclick="loadCategory('curso', true)"><i class="fas fa-laptop-code me-2 text-muted"></i>Cursos</a>
                <a class="collapse-item" href="#" onclick="loadCategory('seminario', true)"><i class="fas fa-users me-2 text-muted"></i>Seminarios</a>
                <a class="collapse-item" href="#" onclick="loadCategory('diplomado', true)"><i class="fas fa-graduation-cap me-2 text-muted"></i>Diplomados</a>
                <a class="collapse-item" href="#" onclick="loadCategory('congreso', true)"><i class="fas fa-microphone me-2 text-muted"></i>Congresos</a>
                <a class="collapse-item" href="#" onclick="loadCategory('charla', true)"><i class="fas fa-comments me-2 text-muted"></i>Charlas</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAprendizaje"
            aria-expanded="true" aria-controls="collapseAprendizaje">
            <i class="fas fa-fw fa-user-graduate"></i>
            <span>Mi Aprendizaje</span>
        </a>
        <div id="collapseAprendizaje" class="collapse" aria-labelledby="headingAprendizaje" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-success">Mi Expediente:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../views/historial.php?action=inscritos')"><i class="fas fa-play-circle me-2 text-muted"></i>Cursos en Progreso</a>
                <a class="collapse-item" href="#" onclick="loadPage('../views/historial.php?action=finalizados')"><i class="fas fa-check-circle me-2 text-muted"></i>Cursos Finalizados</a>
            </div>
        </div>
    </li>
    <?php endif; ?>

    <?php if (in_array($_SESSION['id_rol'], [1, 2, 3, 4])): ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagos"
            aria-expanded="true" aria-controls="collapsePagos">
            <i class="fas fa-fw fa-wallet"></i>
            <span>Facturación y Pagos</span>
        </a>
        <div id="collapsePagos" class="collapse" aria-labelledby="headingPagos" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-success">Gestión de Pagos:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../views/mis_pagos.php')"><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Mis Aranceles</a>
            </div>
        </div>
    </li>
    <?php endif; ?>

    <?php if (in_array($_SESSION['id_rol'], [2, 3, 4])): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Espacio Docente</div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDocencia"
            aria-expanded="true" aria-controls="collapseDocencia">
            <i class="fas fa-fw fa-chalkboard-teacher"></i>
            <span>Gestión Curricular</span>
        </a>
        <div id="collapseDocencia" class="collapse" aria-labelledby="headingDocencia" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                
                <h6 class="collapse-header text-primary">Mis Responsabilidades:</h6>
                <a class="collapse-item font-weight-bold text-success" href="#" onclick="loadPage('../views/mis_materias_facilitador.php')">
                    <i class="fas fa-tasks me-2"></i> Mis Materias (Notas)
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="collapse-header text-info">Creación de Cursos:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../public/gestion_cursos.php?action=crear')">Postular Nueva Propuesta</a>
                <a class="collapse-item" href="#" onclick="loadPage('../public/gestion_cursos.php?action=ver')">Mis Postulaciones</a>
                
                <?php if (in_array($_SESSION['id_rol'], [3, 4])): ?>
                <div class="dropdown-divider"></div>
                <h6 class="collapse-header text-danger">Coordinación:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../public/editar_cursos.php')">Evaluar Propuestas</a>
                <?php endif; ?>
            </div>
        </div>
    </li>
    <?php endif; ?>

<?php if (in_array($_SESSION['id_rol'], [3, 4])): ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Institucional</div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseComunidad" aria-expanded="true" aria-controls="collapseComunidad">
            <i class="fas fa-fw fa-users-cog"></i>
            <span>Comunidad y Tesorería</span>
        </a>
        <div id="collapseComunidad" class="collapse" aria-labelledby="headingComunidad" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                
                <h6 class="collapse-header text-primary">Gestión de Personas:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../public/usuarios.php')">Verificación de Usuarios</a>
                <a class="collapse-item" href="#" onclick="loadPage('../views/gestionar_cargos.php')">Directorio y Cargos</a>

                <div class="dropdown-divider"></div>
                
                <h6 class="collapse-header text-success">Tesorería y Pagos:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_pagos.php')"><i class="fas fa-search-dollar me-2 text-muted"></i>Auditar Pagos</a>
                <a class="collapse-item" href="#" onclick="loadPage('../views/gestion_cuentas_bancarias.php')"><i class="fas fa-university me-2 text-muted"></i>Cuentas Destino</a>

            </div>
        </div>
    </li>
    <?php endif; ?>

    <?php if ($_SESSION['id_rol'] == 4): ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAjustes" aria-expanded="true" aria-controls="collapseAjustes">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Sistema y Ajustes</span>
        </a>
        <div id="collapseAjustes" class="collapse" aria-labelledby="headingAjustes" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-primary">Ajustes Generales:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../views/ajustes_sistema.php')"><i class="fas fa-sliders-h me-2 text-muted"></i>Configuración Global</a>
                <div class="dropdown-divider"></div>
                <h6 class="collapse-header text-warning">Retroalimentación:</h6>
                <a class="collapse-item" href="#" onclick="loadPage('../views/sugerencias.php')"><i class="fas fa-lightbulb me-2 text-muted"></i>Buzón de Sugerencias</a>
            </div>
        </div>
    </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

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
<div class="text-center d-none d-md-inline mb-3">
        <button class="btn btn-secondary rounded-circle border-0" id="sidebarToggle">
            <i class="fas fa-arrows-alt-h"></i>
        </button>
    </div>

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

<script src="../public/assets/js/main.js"></script>