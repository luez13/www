<?php
include '../views/header.php';
if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo '<script>alert("Enlace inválido. Por favor solicita uno nuevo."); window.location.href="recuperar_password.php";</script>';
    exit;
}
$token = htmlspecialchars($_GET['token']);
?>

<body class="bg-gradient-light" style="background: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 mt-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-5">
                    <div class="card-header bg-success text-white text-center py-4 border-0">
                        <i class="fas fa-unlock-alt fa-3x mb-3 text-white-50"></i>
                        <h4 class="mb-0 fw-bold">Nueva Contraseña</h4>
                        <p class="mb-0 text-white-50 small">Crea una clave segura para tu cuenta</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form id="resetForm" action="../controllers/autenticacion.php" method="post">
                            <input type="hidden" name="action" value="reset">
                            <input type="hidden" name="csrf_token"
                                value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                            <input type="hidden" name="token" value="<?= $token ?>">

                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control rounded-pill px-4" name="password"
                                    id="passwordInput" placeholder="Nueva Contraseña" required minlength="8"
                                    autocomplete="new-password">
                                <label for="passwordInput" class="px-4 text-muted"><i
                                        class="fas fa-lock ms-1 me-2"></i>Nueva Contraseña</label>
                                <button type="button"
                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none toggle-password"
                                    style="padding-right: 20px; z-index: 10;" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="form-floating mb-4 position-relative">
                                <input type="password" class="form-control rounded-pill px-4" name="confirm_password"
                                    id="confirmPasswordInput" placeholder="Confirmar Contraseña" required minlength="8"
                                    autocomplete="new-password">
                                <label for="confirmPasswordInput" class="px-4 text-muted"><i
                                        class="fas fa-check-double ms-1 me-2"></i>Confirmar Contraseña</label>
                                <button type="button"
                                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none toggle-password"
                                    style="padding-right: 20px; z-index: 10;" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="col-12 mb-4 px-2">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Recuerda usar al menos 8 caracteres.
                                </small>
                            </div>

                            <button type="submit"
                                class="btn btn-success btn-block w-100 rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>Actualizar Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../views/footer.php'; ?>
    <script>
        document.getElementById("resetForm").addEventListener("submit", function (event) {
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
</body>

</html>