<?php
// Se incluye el encabezado de la página.
include __DIR__ . '/../views/header.php';
require_once __DIR__ . '/../config/model.php';

try {
    $db = new DB();
    $pdo = $db->getConn(); // <-- Extraemos el objeto PDO real

    // Traer imágenes del carrusel usando el $pdo, no el wrapper $db
    $stmtC = $pdo->prepare("SELECT * FROM cursos.landing_carrusel WHERE activo = true ORDER BY id_carrusel ASC");
    $stmtC->execute();
    $carrusel_items = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay configurado, cargamos uno por defecto del sistema
    if (empty($carrusel_items)) {
        $carrusel_items = [
            ['ruta_imagen' => 'assets/img/curso1.jpg', 'titulo' => 'Bienvenido', 'descripcion' => 'Sistema de Certificaciones UPTAIET']
        ];
    }

    // Traer todos los cursos autorizados usando $pdo
    $stmtCur = $pdo->prepare("SELECT id_curso, nombre_curso, descripcion, tipo_curso, imagen_portada FROM cursos.cursos WHERE estado = true AND autorizacion IS NOT NULL ORDER BY id_curso DESC");
    $stmtCur->execute();
    $cursosTodos = $stmtCur->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { // <-- Throwable atrapa Exceptions y Fatal Errors
    // Registrar el error en el log del servidor para depuración
    error_log("Error crítico en el Index: " . $e->getMessage());

    // Si hay error continuar con arreglos vacíos para no romper la web
    $carrusel_items = [];
    $cursosTodos = [];
}

// Función helper para renderizar tarjetas de cursos
function renderizarCursos($cursosArray)
{
    if (empty($cursosArray)) {
        return '<div class="p-4 rounded shadow-sm" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);"><p class="text-center text-muted m-0">Aún no hay cursos disponibles en este momento.</p></div>';
    }

    $html = '<div class="row course-row justify-content-center">';
    foreach ($cursosArray as $curso) {
        $imgHtml = '';
        if (!empty($curso['imagen_portada'])) {
            $imgHtml = '<img src="' . htmlspecialchars($curso['imagen_portada']) . '" class="card-img-top" alt="' . htmlspecialchars($curso['nombre_curso']) . '" style="height: 200px; object-fit: cover;">';
        }

        $desc = htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...';
        $nombreAttr = htmlspecialchars(strtolower($curso['nombre_curso']));
        $tipoAttr = htmlspecialchars(strtolower($curso['tipo_curso']));

        $html .= '
        <div class="col-lg-4 col-md-6 mb-4 curso-card" data-nombre="' . $nombreAttr . '" data-tipo="' . $tipoAttr . '">
            <div class="card h-100 shadow-sm border-0" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px); transform: translateY(0); transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform=\'translateY(-5px)\'; this.style.boxShadow=\'0 10px 20px rgba(0,0,0,0.2)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 0.125rem 0.25rem rgba(0,0,0,0.075)\';">
                ' . $imgHtml . '
                <div class="card-body">
                    <h5 class="card-title fw-bold text-dark">' . htmlspecialchars($curso['nombre_curso']) . '</h5>
                    <p class="text-primary small mb-2"><i class="fas fa-certificate"></i> ' . htmlspecialchars($curso['tipo_curso']) . '</p>
                    <p class="card-text text-muted">' . $desc . '</p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <button class="btn btn-outline-primary w-100 fw-bold rounded-pill btn-mas-info" data-id="' . $curso['id_curso'] . '">Más Información</button>
                </div>
            </div>
        </div>';
    }
    $html .= '</div>';
    return $html;
}
?>

<header class="institutional-banner-container bg-light shadow-sm">
    <div class="container text-center">
        <img src="assets/img/vector membrete 1-01.png" alt="Banner Institucional UPTAIET" class="img-fluid">
    </div>
</header>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="assets/img/logo.png" alt="Logo UPTAIET" width="30" height="30"
                class="d-inline-block align-text-top me-2">
            UPTAIET Certs
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#quienes-somos">Quiénes Somos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cursos">Cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/ver_certificados.php">Ver Certificados</a>
                </li>
            </ul>
            <div class="d-flex">
                <button class="btn btn-primary me-2" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Iniciar Sesión
                </button>
                <a href="register.php" class="btn btn-outline-light" type="button">
                    Registrarse
                </a>
            </div>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($carrusel_items as $index => $item): ?>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $index ?>"
                    <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>
                    aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($carrusel_items as $index => $item): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>"
                    style="background-image: url('<?= htmlspecialchars($item['ruta_imagen']) ?>');">
                    <?php if (!empty($item['titulo']) || !empty($item['descripcion'])): ?>
                        <div class="carousel-caption d-none d-md-block"
                            style="background: rgba(0,0,0,0.5); border-radius: 10px; padding-bottom: 5px;">
                            <?php if (!empty($item['titulo']))
                                echo "<h5>" . htmlspecialchars($item['titulo']) . "</h5>"; ?>
                            <?php if (!empty($item['descripcion']))
                                echo "<p>" . htmlspecialchars($item['descripcion']) . "</p>"; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hero-text-overlay">
        <div class="container text-center">
            <h1 class="display-4 fw-bold text-white">Bienvenido al Sistema de Certificaciones UPTAIET</h1>
            <p class="lead text-white-50">Formando el futuro profesional del Táchira a través de la educación continua y
                de calidad.</p>
            <a href="#cursos" class="btn btn-primary btn-lg mt-3">Explorar Cursos</a>
        </div>
    </div>
</section>


<main>
    <section id="quienes-somos" class="py-5 content-section" style="position: relative; z-index: 5;">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-10 text-center p-5 rounded-4 shadow-lg"
                    style="background: rgba(11, 17, 32, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(56, 189, 248, 0.2);">
                    <h2 class="display-5 fw-bold text-white mb-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                        Nuestra Misión</h2>
                    <p class="lead text-white-50">
                        Somos la Universidad Politécnica Territorial Agroindustrial del estado Táchira, comprometidos
                        con la formación de profesionales integrales, innovadores y con un alto sentido de
                        responsabilidad social.
                    </p>
                    <p class="text-light" style="opacity: 0.8;">
                        Nuestro sistema de certificaciones busca validar y potenciar las habilidades de nuestros
                        estudiantes y de la comunidad en general, ofreciendo cursos y diplomados de alta demanda en el
                        mercado laboral.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="text-center mb-5" style="position: relative; z-index: 10;">
                        <h2 class="display-4 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.6);">
                            Nuestra Oferta Académica</h2>
                        <p class="lead text-white-50" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Explora todos
                            nuestros cursos y certificaciones disponibles.</p>
                    </div>

                    <div id="all-courses-container" style="position: relative; z-index: 10;">
                        <?= renderizarCursos($cursosTodos) ?>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<!-- Modal de Detalles del Curso -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1" aria-labelledby="courseDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold" id="courseDetailsModalLabel"><i class="fas fa-book-open me-2"></i>
                    Detalles del Programa</h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalDetalles()"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="text-center my-5 d-none" id="courseLoading">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted fw-bold">Cargando detalles...</p>
                </div>

                <div id="courseContent" style="display: none;">
                    <img src="" id="courseModalImg" class="img-fluid rounded-3 shadow-sm mb-4 w-100"
                        style="max-height: 250px; object-fit: cover; display: none;">

                    <h3 id="courseModalTitle" class="fw-bold text-dark mb-2"></h3>
                    <p class="badge bg-info text-dark mb-4 fs-6 px-3 py-2 rounded-pill shadow-sm" id="courseModalType"
                        style="letter-spacing: 0.5px;"></p>

                    <div class="bg-white p-4 rounded-3 shadow-sm mb-4">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3"><i
                                class="fas fa-info-circle me-2"></i>Descripción</h5>
                        <p id="courseModalDesc" class="text-secondary m-0"
                            style="white-space: pre-wrap; font-size: 1.05rem; line-height: 1.6;"></p>
                    </div>

                    <div id="courseModalReqContainer" style="display: none;"
                        class="bg-white p-4 rounded-3 shadow-sm mb-4 border-start border-4 border-warning">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i
                                class="fas fa-clipboard-list text-warning me-2"></i>Requisitos</h5>
                        <p id="courseModalReq" class="text-secondary m-0"
                            style="white-space: pre-wrap; font-size: 1rem;"></p>
                    </div>

                    <div id="courseModalModContainer" style="display: none;"
                        class="bg-white p-4 rounded-3 shadow-sm mb-2">
                        <h5 class="fw-bold text-success border-bottom pb-2 mb-3"><i
                                class="fas fa-layer-group me-2"></i>Contenido / Módulos</h5>
                        <div class="accordion accordion-flush" id="accordionModules">
                            <!-- Modulos renderizados dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="modal-footer bg-white pt-3 pb-3 px-4 border-top-0 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary fw-bold rounded-pill px-4"
                    onclick="cerrarModalDetalles()">Cerrar</button>
                <div class="text-end">
                    <small class="text-muted d-block mb-2"><i class="fas fa-user-lock me-1"></i>Para matricularte,
                        requieres una cuenta.</small>
                    <a href="register.php" id="btn-main-register" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm"
                        style="transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'"
                        onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-sign-in-alt me-2"></i>Inscribirse / Acceder
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseModal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));

        window.cerrarModalDetalles = function () {
            courseModal.hide();
        };



        document.body.addEventListener('click', function (e) {
            if (e.target && (e.target.classList.contains('btn-mas-info') || e.target.closest('.btn-mas-info'))) {
                const btn = e.target.classList.contains('btn-mas-info') ? e.target : e.target.closest('.btn-mas-info');
                const courseId = btn.getAttribute('data-id');
                const loading = document.getElementById('courseLoading');
                const content = document.getElementById('courseContent');

                // UI reset
                loading.classList.remove('d-none');
                content.style.display = 'none';
                document.getElementById('accordionModules').innerHTML = '';

                courseModal.show();

                fetch(`api_curso_detalles.php?id_curso=${courseId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            courseModal.hide();
                            return;
                        }

                        document.getElementById('courseModalTitle').textContent = data.nombre_curso;
                        document.getElementById('courseModalType').textContent = (data.tipo_curso || '').replace('_', ' ').toUpperCase();
                        document.getElementById('courseModalDesc').textContent = data.descripcion || 'Sin descripción detallada.';

                        if (data.imagen_portada) {
                            const img = document.getElementById('courseModalImg');
                            img.src = data.imagen_portada;
                            img.style.display = 'block';
                        } else {
                            document.getElementById('courseModalImg').style.display = 'none';
                        }

                        const reqContainer = document.getElementById('courseModalReqContainer');
                        if (data.requisitos && data.requisitos.trim() !== '') {
                            document.getElementById('courseModalReq').textContent = data.requisitos;
                            reqContainer.style.display = 'block';
                        } else {
                            reqContainer.style.display = 'none';
                        }

                        const modContainer = document.getElementById('courseModalModContainer');
                        if (data.modulos && data.modulos.length > 0) {
                            let html = '';
                            data.modulos.forEach((m, idx) => {
                                html += `
                            <div class="accordion-item shadow-sm mb-2 rounded border-0">
                                <h2 class="accordion-header" id="headingMod${idx}">
                                    <button class="accordion-button collapsed fw-bold text-dark rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMod${idx}">
                                        <i class="fas fa-bookmark text-primary me-2"></i> ${m.numero}: ${m.nombre_modulo}
                                    </button>
                                </h2>
                                <div id="collapseMod${idx}" class="accordion-collapse collapse" data-bs-parent="#accordionModules">
                                    <div class="accordion-body text-secondary" style="white-space: pre-wrap; border-left: 3px solid #0d6efd; margin-left: 10px;">${m.contenido ? m.contenido.replace(/[\[\]"']/g, '').replace(/,/g, ', ').trim() : 'Sin detalles específicos.'}</div>
                                </div>
                            </div>`;
                            });
                            document.getElementById('accordionModules').innerHTML = html;
                            modContainer.style.display = 'block';
                        } else {
                            modContainer.style.display = 'none';
                        }

                        loading.classList.add('d-none');
                        content.style.display = 'block';

                        // SET REDIRECT ID
                        const redirectInput = document.getElementById('redirect_course_id');
                        if (redirectInput) redirectInput.value = courseId;

                        const registerLink = document.getElementById('register_link_modal');
                        if (registerLink) registerLink.href = `register.php?redirect_course_id=${courseId}`;

                        const mainRegister = document.getElementById('btn-main-register');
                        if (mainRegister) mainRegister.href = `register.php?redirect_course_id=${courseId}`;
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Error al cargar detalles. Contacte al administrador.");
                        courseModal.hide();
                    });
            }
        });
    });
</script>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="loginModalLabel"><i class="fas fa-sign-in-alt me-2"></i>Acceso al
                    Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 bg-light">
                <div class="text-center mb-4">
                    <img src="assets/img/logo.png" width="80" height="80"
                        class="mb-3 shadow-sm rounded-circle p-2 bg-white">
                    <h4 class="text-gray-900 fw-bold">Bienvenido de Nuevo</h4>
                    <p class="text-muted small">Ingresa tus credenciales para continuar</p>
                </div>
                <form class="user" action="../controllers/autenticacion.php" method="post">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token"
                        value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                    <input type="hidden" name="redirect_course_id" id="redirect_course_id" value="">

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-pill px-4" id="floatingInputCorreo"
                            name="correo" placeholder="correo@ejemplo.com" required>
                        <label for="floatingInputCorreo" class="px-4 text-muted"><i
                                class="fas fa-envelope me-2"></i>Correo Electrónico</label>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="password" class="form-control rounded-pill px-4" id="floatingPassword"
                            name="password" placeholder="Contraseña" required>
                        <label for="floatingPassword" class="px-4 text-muted"><i
                                class="fas fa-lock me-2"></i>Contraseña</label>
                        <button type="button"
                            class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none toggle-password"
                            style="padding-right: 20px; z-index: 10;" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="customCheck">
                            <label class="form-check-label small text-muted" for="customCheck">Recuérdame</label>
                        </div>
                        <a class="small text-primary text-decoration-none fw-bold"
                            href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit"
                        class="btn btn-primary btn-user btn-block w-100 rounded-pill py-2 fw-bold shadow-sm">
                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                    </button>
                </form>
            </div>
            <div class="modal-footer justify-content-center bg-white border-0 py-3">
                <span class="text-muted small">¿No tienes cuenta? <a id="register_link_modal"
                        class="text-primary fw-bold text-decoration-none" href="register.php">¡Regístrate
                        aquí!</a></span>
            </div>
        </div>
    </div>
</div>

<?php
// Se incluye el pie de página.
include '../views/footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemsPerPage = 6;

        function applyFilterAndPagination(page = 1) {
            const container = document.getElementById('all-courses-container');
            if (!container) return;

            const allCards = Array.from(container.querySelectorAll('.curso-card'));

            // Paginate directly since there is no search anymore
            const totalItems = allCards.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
            if (page > totalPages) page = totalPages;

            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            allCards.forEach((card, index) => {
                if (index >= start && index < end) {
                    card.style.setProperty('display', 'block', 'important');
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            renderPagination(container, totalPages, page);
        }

        function renderPagination(container, totalPages, currentPage) {
            let pagContainer = container.querySelector('.pagination-container');
            if (!pagContainer) {
                pagContainer = document.createElement('div');
                pagContainer.className = 'pagination-container mt-5 d-flex justify-content-center w-100';
                const row = container.querySelector('.course-row');
                if (row) {
                    row.after(pagContainer);
                } else {
                    container.appendChild(pagContainer);
                }
            }

            if (totalPages <= 1) {
                pagContainer.innerHTML = '';
                return;
            }

            // Estilos de paginacion adaptados a fondo oscuro
            let html = '<nav aria-label="Navegación de cursos"><ul class="pagination pagination-lg shadow-sm">';
            for (let i = 1; i <= totalPages; i++) {
                let activeClass = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${activeClass}"><a class="page-link" href="#cursos" data-page="${i}" style="${i === currentPage ? 'background-color:#0d6efd; border-color:#0d6efd;' : 'background-color:rgba(255,255,255,0.8); color:#0d6efd; border:none;'}">${i}</a></li>`;
            }
            html += '</ul></nav>';
            pagContainer.innerHTML = html;

            pagContainer.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    applyFilterAndPagination(parseInt(this.getAttribute('data-page')));
                    document.getElementById('cursos').scrollIntoView({ behavior: 'smooth' });
                });
            });
        }

        // Initial load
        applyFilterAndPagination(1);

        // Ajustar el fondo del id="cursos" para que luzca bien con las partículas
        const cursosSection = document.getElementById('cursos');
        if (cursosSection) {
            cursosSection.classList.remove('bg-white'); // Asegurar transparencia si tenia color
            cursosSection.style.position = 'relative';
            cursosSection.style.zIndex = '5';
        }

        // Toggle Password Visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>