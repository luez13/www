<?php
// Incluir el archivo header.php
include '../views/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <img src="../public/assets/img/logo.png" width="50" height="50"> UPTAIET
                                    <h1 class="h4 text-gray-900 mb-4">Bienvenido</h1>
                                </div>
                                <form class="user" action="../controllers/autenticacion.php" method="post">
                                    <input type="hidden" name="action" value="login">
                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-user" name="correo" id="correo" aria-describedby="emailHelp" placeholder="Ingresa correo electronico" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" name="password" id="password" placeholder="Contraseña" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck">Recuerdame</label>
                                        </div>
                                    </div>
                                    <input type="submit" value="Iniciar sesión" class="btn btn-primary btn-user btn-block">
                                    <hr>
                                    <!-- <a href="index.html" class="btn btn-google btn-user btn-block">
                                        <i class="fab fa-google fa-fw"></i> Registrate con Google
                                    </a>
                                    <a href="index.html" class="btn btn-facebook btn-user btn-block">Registrate con Facebook</a> -->
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="forgot-password.html">¿Olvidaste tu contraseña?</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="register.html">¡Crea una cuenta!</a>
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
// Incluir el archivo footer.php en views
include '../views/footer.php';
?>