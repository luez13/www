<?php
// Incluir el archivo header.php
include '../views/header.php';
?>
<body class="bg-gradient-light">
    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <img src="../public/assets/img/logo.png" width="50" height="50"/> UPTAIET 
                                <h1 class="h4 text-gray-900 mb-4">Regístrate con Nosotros</h1>
                            </div>
                            <form class="user" action="../controllers/autenticacion.php" method="post">
                                <input type="hidden" name="action" value="registro">
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user" name="nombre" id="exampleFirstName" placeholder="Nombres" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" name="apellido" id="exampleLastName" placeholder="Apellidos" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" name="correo" id="exampleInputEmail" placeholder="Correo Electrónico" required>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user" name="password" id="exampleInputPassword" placeholder="Contraseña" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user" name="confirm_password" id="exampleRepeatPassword" placeholder="Repite la Contraseña" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="number" class="form-control form-control-user" name="cedula" id="exampleCedula" placeholder="Cédula" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Regístrate ahora
                                </button>
                                <!-- <hr>
                                <a href="index.html" class="btn btn-google btn-user btn-block">
                                    <i class="fab fa-google fa-fw"></i> Regístrate con Google
                                </a>
                                <a href="index.html" class="btn btn-facebook btn-user btn-block">
                                    <i class="fab fa-facebook-f fa-fw"></i> Regístrate con Facebook
                                </a>
                            </form>
                            <hr> -->
                            <!-- <div class="text-center">
                                <a class="small" href="forgot-password.html">¿Olvidaste tu contraseña?</a>
                            </div> -->
                            <div class="text-center">
                                <a class="small" href="index.php">¿Ya tienes un usuario? Ingresa ahora!</a>
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