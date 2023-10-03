<!DOCTYPE html>
<html>
<head>
    <title>Formulario de Cédula</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Ingrese su cédula</h2>
        <form id="cedulaForm">
            <div class="form-group">
                <label for="cedula">Cédula:</label>
                <input type="text" class="form-control" id="cedula" required>
            </div>
            <input type="hidden" id="nombre" name="nombre">
            <input type="hidden" id="apellido" name="apellido">
            <input type="hidden" id="correo" name="correo">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
        <div id="resultado"></div>
        <button id="generarPDF" class="btn btn-secondary" disabled>Generar PDF</button>
    </div>

    <script>
    $('#cedulaForm').on('submit', function(e) {
    e.preventDefault();
    var cedula = $('#cedula').val();
    $.ajax({
        url: 'validar.php',
        type: 'POST',
        dataType: 'json',
        data: {cedula: cedula},
        success: function(data) {
            var html = '<p>Nombre: ' + data[0].nombre + '</p><p>Apellido: ' + data[0].apellido + '</p><p>Correo: ' + data[0].correo + '</p>';
            data.forEach(function(item) {
                html += '<p><input type="checkbox" id="' + item.nombre_doc + '" name="' + item.nombre_doc + '" value="' + item.nombre_doc + '"><label for="' + item.nombre_doc + '"> ' + item.nombre_doc + '</label></p>';
            });
            $('#resultado').html(html);
            $('#generarPDF').prop('disabled', false);
            
            // Almacenar los datos en los campos ocultos
            $('#nombre').val(data[0].nombre);
            $('#apellido').val(data[0].apellido);
            $('#correo').val(data[0].correo);
        }
    });
});

$('#generarPDF').on('click', function() {
    var selected = [];
    $('input[type=checkbox]:checked').each(function() {
        selected.push($(this).attr('value'));
    });

    // Obtener los datos de los campos ocultos
    var nombre = $('#nombre').val();
    var apellido = $('#apellido').val();
    var correo = $('#correo').val();

    $.ajax({
        url: 'generarPDF.php',
        type: 'POST',
        data: {selected: selected, nombre: nombre, apellido: apellido, correo: correo},
        success: function() {
            // Agregar un enlace al PDF en la página
            var link = $('<a>').attr('href', 'documentos/certificado.pdf').attr('target', '_blank').text('Ver PDF').addClass('btn btn-primary');
            $('#resultado').append(link);
        }
    });
});
    </script>
</body>
</html>