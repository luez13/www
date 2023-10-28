<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IUT - SAREC</title>
    <title>Formulario de Cédula</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <!-- Asegúrate de incluir jQuery antes de bootstrap.min.js -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .container {
            max-width: 600px;
        }
        .list-group-item {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="header text-center">
        <h1>Instituto Universitario de Tecnología (IUT) - SAREC</h1>
    </div>
    <div class="container">
    <h2>Cédulas</h2>
    <div id="cedulas" class="list-group"></div>
    <input type="hidden" id="cedula">
    <div id="datos" class="collapse">
        <h2>Datos</h2>
        <div id="resultado"></div>
        <button id="generarPDF" class="btn btn-primary" disabled>Generar PDF</button>
        <button id="verPDF" class="btn btn-success" style="display: none;">Ver PDF</button>
        <div id="qrcode"></div>
    </div>
</div>

<script>
$(document).ready(function() {
    $.ajax({
        url: 'validar.php',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            if (data && Array.isArray(data)) {
                var html = '';
                data.forEach(function(item) {
                    html += '<button class="list-group-item list-group-item-action cedula">' + item.cedula + '</button>';
                });
                $('#cedulas').html(html);

                $('.cedula').on('click', function() {
                    var cedula = $(this).text();
                    $('#cedula').val(cedula);
                    var cedula = $(this).text();
                    $.ajax({
                        url: 'validar.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {cedula: cedula},
                        success: function(data) {
                            var html = '<div class="card">';
                            html += '<div class="card-body">';
                            html += '<h5 class="card-title">' + data[0].nombre + ' ' + data[0].apellido + '</h5>';
                            html += '<h6 class="card-subtitle mb-2 text-muted">' + data[0].correo + '</h6>';
                            data.forEach(function(item) {
                                html += '<div class="form-check">';
                                html += '<input class="form-check-input" type="radio" name="documento" id="' + item.nombre_doc + '" value="' + item.nombre_doc + '">';
                                html += '<label class="form-check-label" for="' + item.nombre_doc + '">' + item.nombre_doc + '</label>';
                                html += '</div>';
                            });
                            html += '</div>';
                            html += '</div>';
                            $('#resultado').html(html);
                            $('#datos').collapse('show');  // Mostrar los datos con una animación

                            // Almacenar la cédula en el campo oculto
                            $('#cedula').val(cedula);  // Almacenar la cédula en el campo oculto

                            $('#generarPDF').prop('disabled', false);  // Habilitar el botón de generar PDF
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Error: ' + textStatus + ', ' + errorThrown);
                        }
                    });
                });
            } else {
                console.error('Error: Los datos recibidos no son válidos');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error: ' + textStatus + ', ' + errorThrown);
        }
    });

    $('#generarPDF').on('click', function() {
    var selected = [$('input[name=documento]:checked').val()];
    
    // Validar que se haya seleccionado un documento
    if (!selected[0]) {
        alert('Por favor selecciona un documento');
        return;
    }

    var cedula = $('#cedula').val();

    var data = {selected: selected, cedula: cedula};
    var jsonData = JSON.stringify(data);
    var urlSafeData = encodeURIComponent(jsonData);

    // Generar la URL del PDF
    var pdfUrl = window.location.origin + '/generarPDF.php?data=' + urlSafeData;

    // Actualizar el código QR con la URL del PDF
    $('#qrcode').empty();
    var qrCode = new QRCode(document.getElementById("qrcode"), {
        text: pdfUrl,
        width: 256,
        height: 256
    });

    // Mostrar el código QR
    $('#qrcode').show();

    // Actualizar el botón con la URL del PDF
    $('#verPDF').off('click');
    $('#verPDF').on('click', function() {
        window.open(pdfUrl, '_blank');
    });
    
    // Mostrar el botón
    $('#verPDF').show();
});

});
</script>

</body>
</html>