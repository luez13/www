<?php
// Se incluye el encabezado de la página.
include '../views/header.php';
?>

<header class="institutional-banner-container bg-light shadow-sm">
    <div class="container text-center">
        <img src="../public/assets/img/vector membrete 1-01.png" alt="Banner Institucional UPTAIET" class="img-fluid">
    </div>
</header>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../public/assets/img/logo.png" alt="Logo UPTAIET" width="30" height="30" class="d-inline-block align-text-top me-2">
            UPTAIET Certs
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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

<div id="particle-background"></div>

<section class="hero-section">
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="4" aria-label="Slide 5"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('../public/assets/img/curso1.jpg');"></div>
            <div class="carousel-item" style="background-image: url('../public/assets/img/curso2.jpg');"></div>
            <div class="carousel-item" style="background-image: url('../public/assets/img/curso3.jpg');"></div>
            <div class="carousel-item" style="background-image: url('../public/assets/img/curso4.jpg');"></div>
            <div class="carousel-item" style="background-image: url('../public/assets/img/curso5.jpg');"></div>
        </div>
    </div>
    <div class="hero-text-overlay">
        <div class="container text-center">
            <h1 class="display-4 fw-bold text-white">Bienvenido al Sistema de Certificaciones UPTAIET</h1>
            <p class="lead text-white-50">Formando el futuro profesional del Táchira a través de la educación continua y de calidad.</p>
            <a href="#cursos" class="btn btn-primary btn-lg mt-3">Explorar Cursos</a>
        </div>
    </div>
</section>


<main>
    <section id="quienes-somos" class="py-5 content-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <h2 class="display-5">Nuestra Misión</h2>
                    <p class="lead">
                        Somos la Universidad Politécnica Territorial Agroindustrial del estado Táchira, comprometidos con la formación de profesionales integrales, innovadores y con un alto sentido de responsabilidad social.
                    </p>
                    <p>
                        Nuestro sistema de certificaciones busca validar y potenciar las habilidades de nuestros estudiantes y de la comunidad en general, ofreciendo cursos y diplomados de alta demanda en el mercado laboral.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="text-center mb-5">
                        <h2 class="display-5">Nuestra Oferta Académica</h2>
                        <p class="lead text-muted">Selecciona un área para ver los cursos disponibles.</p>
                    </div>

                    <ul class="nav nav-pills custom-pills justify-content-center mb-4" id="pills-tab" role="tablist">
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-geociencias-tab" data-bs-toggle="pill" data-bs-target="#pills-geociencias" type="button" role="tab" aria-controls="pills-geociencias" aria-selected="true">
                                <i class="fas fa-globe-americas me-1"></i> Geociencias
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-artes-tab" data-bs-toggle="pill" data-bs-target="#pills-artes" type="button" role="tab" aria-controls="pills-artes" aria-selected="false">
                                <i class="fas fa-paint-brush me-1"></i> Artes y Oficios
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-salud-tab" data-bs-toggle="pill" data-bs-target="#pills-salud" type="button" role="tab" aria-controls="pills-salud" aria-selected="false">
                                <i class="fas fa-heartbeat me-1"></i> Salud
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-agro-tab" data-bs-toggle="pill" data-bs-target="#pills-agro" type="button" role="tab" aria-controls="pills-agro" aria-selected="false">
                                <i class="fas fa-seedling me-1"></i> Agroalimentación
                            </button>
                        </li>
                        
                        </ul>

                    <div class="tab-content" id="pills-tabContent">

                        <div class="tab-pane fade show active" id="pills-geociencias" role="tabpanel" aria-labelledby="pills-geociencias-tab">
                            <div class="p-4 rounded bg-white shadow-sm">
                            <p class="text-center text-muted m-0">Aún no hay cursos disponibles en Geociencias.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-artes" role="tabpanel" aria-labelledby="pills-artes-tab">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm border-0">
                                        
                                        <img src="../public/assets/img/curso_ceramica.jpg" class="card-img-top" alt="Curso de Cerámica">
                                        <div class="card-body">
                                            <h5 class="card-title">Cerámica Artesanal</h5>
                                            <p class="card-text">Aprende las técnicas básicas de modelado y esmaltado para crear tus propias piezas únicas.</p>
                                        </div>
                                        <div class="card-footer bg-white border-0">
                                            <a href="#" class="btn btn-primary">Más Información</a>
                                        </div>
                                    </div>
                                </div>
                                </div>
                        </div>

                        <div class="tab-pane fade" id="pills-salud" role="tabpanel" aria-labelledby="pills-salud-tab">
                            <div class="p-4 rounded bg-white shadow-sm">
                            <p class="text-center text-muted m-0">Próximamente cursos en el área de Salud.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-agro" role="tabpanel" aria-labelledby="pills-agro-tab">
                            <div class="p-4 rounded bg-white shadow-sm">
                            <p class="text-center text-muted m-0">Explora nuestras próximas certificaciones en Agroalimentación.</p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>
</main>


<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img src="../public/assets/img/logo.png" width="80" height="80" class="mb-3">
                    <h1 class="h4 text-gray-900">Bienvenido de Nuevo</h1>
                </div>
                <form class="user" action="../controllers/autenticacion.php" method="post">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group mb-3">
                        <input type="email" class="form-control form-control-user" name="correo" placeholder="Correo Electrónico" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" class="form-control form-control-user" name="password" placeholder="Contraseña" required>
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox small">
                            <input type="checkbox" class="custom-control-input" id="customCheck">
                            <label class="custom-control-label" for="customCheck">Recuérdame</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-user btn-block">
                        Iniciar sesión
                    </button>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                 <a class="small" href="register.php">¿No tienes cuenta? ¡Regístrate!</a>
            </div>
        </div>
    </div>
</div>

<?php
// Se incluye el pie de página.
include '../views/footer.php';
?>