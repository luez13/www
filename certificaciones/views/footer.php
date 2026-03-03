<!-- Formulario flotante de sugerencias -->
<div class="suggestion-form-container position-fixed bottom-0 end-0 p-3"
    style="z-index: 1050; margin-bottom: 2rem; margin-right: 1.5rem;">
    <!-- Botón Flotante (FAB) -->
    <button
        class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center custom-hover-btn"
        type="button" data-bs-toggle="collapse" data-bs-target="#suggestionFormContainer" aria-expanded="false"
        aria-controls="suggestionFormContainer" style="width: 60px; height: 60px; font-size: 1.5rem;">
        <i class="fas fa-lightbulb"></i>
    </button>

    <!-- Contenedor del Formulario -->
    <div class="collapse mt-3 position-absolute bottom-100 end-0 mb-3" id="suggestionFormContainer"
        style="width: 350px;">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div
                class="card-header bg-primary text-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-comment-dots me-2"></i>Buzón de Sugerencias</h6>
                <button type="button" class="btn-close btn-close-white" style="font-size: 0.8rem;"
                    data-bs-toggle="collapse" data-bs-target="#suggestionFormContainer"></button>
            </div>
            <div class="card-body bg-light p-4">
                <form id="suggestionForm" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" id="suggestion_nombre" name="nombre"
                            placeholder="Nombre" required>
                        <label for="suggestion_nombre" class="text-muted small">Nombre</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control rounded-3" id="suggestion_apellido" name="apellido"
                            placeholder="Apellido" required>
                        <label for="suggestion_apellido" class="text-muted small">Apellido</label>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7 form-floating">
                            <input type="email" class="form-control rounded-3" id="suggestion_correo" name="correo"
                                placeholder="Correo" required>
                            <label for="suggestion_correo" class="text-muted small px-3">Correo</label>
                        </div>
                        <div class="col-5 form-floating">
                            <input type="text" class="form-control rounded-3" id="suggestion_cedula" name="cedula"
                                placeholder="Cédula" required>
                            <label for="suggestion_cedula" class="text-muted small px-3">Cédula</label>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control rounded-3" id="suggestion_sugerencia" name="sugerencia"
                            placeholder="Sugerencia" style="height: 100px;" required></textarea>
                        <label for="suggestion_sugerencia" class="text-muted small">Escribe tu sugerencia
                            aquí...</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm custom-hover-btn">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Sugerencia
                    </button>
                </form>
                <div id="suggestionThanks" class="alert alert-success mt-3 shadow-sm border-0 text-center" role="alert"
                    style="display: none; border-radius: 1rem;">
                    <i class="fas fa-check-circle fs-4 mb-2"></i><br>
                    <small>¡Gracias por tu sugerencia! La hemos recibido y será revisada.</small>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer mt-auto py-3 bg-light">
    <div class="container" bis_skin_checked="1">
        <span class="text-muted">© 2025 Sistema de gestión de cursos y certificaciones</span>
    </div>
</footer>

<!-- Bootstrap core JavaScript-->
<script src="../public/assets/vendor/jquery/jquery.min.js"></script>
<script src="../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="../public/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="../public/assets/js/sb-admin-2.min.js"></script>

<!-- Scripts de DataTables y Exportación (Cargados globalmente para evitar errores AJAX) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

<script>
    // Asegurarse de que el contenido principal no sea tapado por el footer
    $(document).ready(function () {
        var footerHeight = $('footer').outerHeight();
        $('body').css('padding-bottom', footerHeight + 'px');

        // Rellenar campos del formulario si hay datos del usuario disponibles
        $.ajax({
            url: '../controllers/SugerenciaController.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.user_data) {
                    $('#suggestion_nombre').val(response.user_data.nombre);
                    $('#suggestion_apellido').val(response.user_data.apellido);
                    $('#suggestion_correo').val(response.user_data.correo);
                    $('#suggestion_cedula').val(response.user_data.cedula);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener los datos del usuario:', error);
                console.log(xhr.responseText); // Añadir esto para ver el error completo
            }
        });

        // Manejar el envío del formulario de sugerencias
        $('#suggestionForm').on('submit', function (event) {
            event.preventDefault(); // Evitar que el formulario se envíe de manera tradicional

            let $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enviando...');

            $.ajax({
                url: '../controllers/SugerenciaController.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json', // Añadir tipo de datos esperado
                success: function (response) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Sugerencia');
                    
                    if (response.success) {
                        // Mostrar mensaje de agradecimiento
                        $('#suggestionForm').slideUp();
                        $('#suggestionThanks').fadeIn();

                        setTimeout(() => {
                            // Cerrar el formulario y resetear estado
                            $('#suggestionFormContainer').collapse('hide');
                            setTimeout(() => {
                                $('#suggestionForm').trigger('reset').show();
                                $('#suggestionThanks').hide();
                            }, 500); // Wait for collapse animation to finish
                        }, 3500);
                    } else {
                        console.error('Error en el servidor:', response.error);
                        alert("Hubo un error al enviar tu sugerencia.");
                    }
                },
                error: function (xhr, status, error) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Enviar Sugerencia');
                    console.error('Error al enviar la sugerencia:', error);
                    alert("Ocurrió un error inesperado de red.");
                }
            });
        });
    });
</script>
</body>

</html>