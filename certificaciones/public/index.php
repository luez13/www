<?php
include '../views/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image">
                            <!-- Puedes reemplazar esta imagen con una relacionada a certificaciones -->
                            <!-- <img src="../public/assets/img/IU.jpg" alt="Certificaciones" class="img-fluid h-100 w-100 object-fit-cover"> -->
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <img src="../public/assets/img/logo.png" width="80" height="80" class="mb-3">
                                    <h1 class="h4 text-gray-900">Bienvenido a UPTAIET</h1>
                                    <p class="text-muted">Sistema de Certificaciones</p>
                                </div>
                                <form class="user" action="../controllers/autenticacion.php" method="post">
                                    <input type="hidden" name="action" value="login">
                                    <div class="form-group mb-3">
                                        <input type="email" class="form-control form-input" name="correo" id="correo" aria-describedby="emailHelp" placeholder="Ingresa tu correo electrónico" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="password" class="form-control form-input" name="password" id="password" placeholder="Contraseña" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck">Recuérdame</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block btn-login">
                                        Iniciar sesión
                                    </button>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="register.php">¿No tienes una cuenta? ¡Regístrate aquí!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="../controllers/generar_certificado.php">!Mira aqui las certificaciones¡</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../views/footer.php';
?>