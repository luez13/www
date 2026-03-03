<?php
// Incluir el archivo header.php
include '../views/header.php';

$fm_nombre = $_SESSION['form_data']['nombre'] ?? '';
$fm_apellido = $_SESSION['form_data']['apellido'] ?? '';
$fm_cedula = $_SESSION['form_data']['cedula'] ?? '';
$fm_correo = $_SESSION['form_data']['correo'] ?? '';
unset($_SESSION['form_data']);
?>

<body class="bg-gradient-light" style="background: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-9 mt-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-5">
                    <div class="card-header bg-primary text-white text-center py-4 border-0">
                        <img src="../public/assets/img/logo.png" width="70" height="70"
                            class="mb-2 shadow-sm rounded-circle p-2 bg-white">
                        <h4 class="mb-0 fw-bold">Crear una Cuenta</h4>
                        <p class="mb-0 text-white-50 small">Regístrate para acceder a los cursos y certificaciones</p>
                    </div>
                    <div class="card-body p-5">
                        <form class="user" id="registerForm" action="../controllers/autenticacion.php" method="post">
                            <input type="hidden" name="action" value="registro">
                            <!-- Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control rounded-3" name="nombre" id="nombreInput"
                                        placeholder="Nombres" value="<?= htmlspecialchars($fm_nombre) ?>" required>
                                    <label for="nombreInput" class="px-4 text-muted"><i
                                            class="fas fa-user ms-1 me-2"></i>Nombres</label>
                                </div>
                                <div class="col-md-6 form-floating">
                                    <input type="text" class="form-control rounded-3" name="apellido" id="apellidoInput"
                                        placeholder="Apellidos" value="<?= htmlspecialchars($fm_apellido) ?>" required>
                                    <label for="apellidoInput" class="px-4 text-muted"><i
                                            class="fas fa-user ms-1 me-2"></i>Apellidos</label>
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="number" class="form-control rounded-3" name="cedula" id="cedulaInput"
                                    placeholder="Cédula" value="<?= htmlspecialchars($fm_cedula) ?>" required>
                                <label for="cedulaInput" class="px-4 text-muted"><i
                                        class="fas fa-id-card ms-1 me-2"></i>Cédula de Identidad</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control rounded-3" name="correo" id="correoInput"
                                    placeholder="Correo Electrónico" value="<?= htmlspecialchars($fm_correo) ?>"
                                    required spellcheck="false" autocomplete="off">
                                <label for="correoInput" class="px-4 text-muted"><i
                                        class="fas fa-envelope ms-1 me-2"></i>Correo Electrónico</label>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 form-floating position-relative">
                                    <input type="password" class="form-control rounded-3 px-4" name="password"
                                        id="passwordInput" placeholder="Contraseña" required autocomplete="off"
                                        minlength="8">
                                    <label for="passwordInput" class="px-4 text-muted"><i
                                            class="fas fa-lock ms-1 me-2"></i>Contraseña</label>
                                    <button type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none toggle-password"
                                        style="padding-right: 20px; z-index: 10;" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="col-md-6 form-floating position-relative">
                                    <input type="password" class="form-control rounded-3 px-4" name="confirm_password"
                                        id="confirmPasswordInput" placeholder="Repite la Contraseña" required
                                        autocomplete="off" minlength="8">
                                    <label for="confirmPasswordInput" class="px-4 text-muted"><i
                                            class="fas fa-check-double ms-1 me-2"></i>Confirmar Clave</label>
                                    <button type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none toggle-password"
                                        style="padding-right: 20px; z-index: 10;" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="col-12 mt-1 px-3">
                                    <small id="passwordHelpBlock" class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i> La contraseña debe tener al menos 8
                                        caracteres.
                                    </small>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-primary btn-user btn-block w-100 rounded-pill py-3 fw-bold shadow-sm custom-hover-btn">
                                <i class="fas fa-user-plus me-2"></i>Completar Registro
                            </button>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-center py-4 border-0">
                        <span class="text-muted">¿Ya tienes una cuenta? <a
                                class="text-primary fw-bold text-decoration-none" href="index.php">¡Inicia sesión
                                aquí!</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Incluir el archivo footer.php en views
    include '../views/footer.php';
    ?>
    <script>
        document.getElementById("registerForm").addEventListener("submit", function (event) {
            const password = document.getElementById("passwordInput").value;
            const confirm_password = document.getElementById("confirmPasswordInput").value;

            if (password.length < 8) {
                alert("La contraseña debe tener al menos 8 caracteres.");
                event.preventDefault();
                return;
            }

            if (password !== confirm_password) {
                alert("Las contraseñas no coinciden. Por favor, verifica.");
                event.preventDefault();
                return;
            }
        });

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
    </script>