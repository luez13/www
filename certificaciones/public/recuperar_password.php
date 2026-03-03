<?php include '../views/header.php'; ?>

<body class="bg-gradient-light" style="background: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 mt-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-5">
                    <div class="card-header bg-primary text-white text-center py-4 border-0">
                        <i class="fas fa-key fa-3x mb-3 text-white-50"></i>
                        <h4 class="mb-0 fw-bold">Recuperar Contraseña</h4>
                        <p class="mb-0 text-white-50 small">Te enviaremos un enlace para restablecerla</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="../controllers/autenticacion.php" method="post">
                            <input type="hidden" name="action" value="recuperar">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                            <div class="form-floating mb-4">
                                <input type="email" class="form-control rounded-pill px-4" name="correo"
                                    id="correoInput" placeholder="Correo Electrónico" required autocomplete="off">
                                <label for="correoInput" class="px-4 text-muted"><i
                                        class="fas fa-envelope ms-1 me-2"></i>Correo con el que te registraste</label>
                            </div>

                            <button type="submit"
                                class="btn btn-primary btn-block w-100 rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Enlace
                            </button>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-center py-4 border-0">
                        <span class="text-muted">¿La recordaste? <a class="text-primary fw-bold text-decoration-none"
                                href="index.php">Inicia Sesión</a></span><br>
                        <span class="text-muted">¿No tienes cuenta? <a class="text-primary fw-bold text-decoration-none"
                                href="register.php">Regístrate</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../views/footer.php'; ?>