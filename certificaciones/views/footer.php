<!-- Formulario flotante de sugerencias -->
<div class="suggestion-form-container position-fixed bottom-0 end-0 m-3">
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#suggestionFormContainer" aria-expanded="false" aria-controls="suggestionFormContainer">
        Sugerencias
    </button>
    <div class="collapse mt-2" id="suggestionFormContainer">
        <div class="card card-body">
            <form id="suggestionForm" method="POST">
                <div class="mb-3">
                    <label for="suggestion_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="suggestion_nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="suggestion_apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="suggestion_apellido" name="apellido" required>
                </div>
                <div class="mb-3">
                    <label for="suggestion_correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="suggestion_correo" name="correo" required>
                </div>
                <div class="mb-3">
                    <label for="suggestion_cedula" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="suggestion_cedula" name="cedula" required>
                </div>
                <div class="mb-3">
                    <label for="suggestion_sugerencia" class="form-label">Sugerencia</label>
                    <textarea class="form-control" id="suggestion_sugerencia" name="sugerencia" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
            <div id="suggestionThanks" class="alert alert-success mt-3" role="alert" style="display: none;">
                ¡Gracias por tu sugerencia! La hemos recibido y será revisada.
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="../public/assets/vendor/jquery/jquery.min.js"></script>
<script src="../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="../public/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="../public/assets/js/sb-admin-2.min.js"></script>

<script>
// Asegurarse de que el contenido principal no sea tapado por el footer
$(document).ready(function() {
    var footerHeight = $('footer').outerHeight();
    $('body').css('padding-bottom', footerHeight + 'px');

    // Rellenar campos del formulario si hay datos del usuario disponibles
    $.ajax({
        url: '../controllers/SugerenciaController.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.user_data) {
                $('#suggestion_nombre').val(response.user_data.nombre);
                $('#suggestion_apellido').val(response.user_data.apellido);
                $('#suggestion_correo').val(response.user_data.correo);
                $('#suggestion_cedula').val(response.user_data.cedula);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener los datos del usuario:', error);
            console.log(xhr.responseText); // Añadir esto para ver el error completo
        }
    });

    // Manejar el envío del formulario de sugerencias
    $('#suggestionForm').on('submit', function(event) {
        event.preventDefault(); // Evitar que el formulario se envíe de manera tradicional

        $.ajax({
            url: '../controllers/SugerenciaController.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json', // Añadir tipo de datos esperado
            success: function(response) {
                console.log(response); // Verificar la respuesta
                if (response.success) {
                    // Mostrar mensaje de agradecimiento
                    $('#suggestionThanks').show();
                } else {
                    console.error('Error en el servidor:', response.error);
                }

                // Limpiar el formulario
                $('#suggestionForm').trigger('reset');

                // Cerrar el formulario después del envío
                $('#suggestionFormContainer').collapse('hide');
            },
            error: function(xhr, status, error) {
                console.error('Error al enviar la sugerencia:', error);
                console.log(xhr.responseText); // Añadir esto para ver el error completo
            }
        });
    });
});
</script>
</body>
</html>