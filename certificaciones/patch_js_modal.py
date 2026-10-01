import re

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "r", encoding="utf-8") as f:
    content = f.read()

# Fix Javascript for buscar_usuarios_ajax.php
bad_js = """        $.ajax({
            url: '../controllers/buscar_usuarios_ajax.php',
            type: 'POST',
            data: { buscar: query },
            success: function(response) {
                var res = JSON.parse(response);
                if(res.success && res.data.length > 0) {
                    var select = $('#id_estudiante_manual');
                    select.empty().removeClass('d-none');
                    res.data.forEach(function(u) {
                        select.append('<option value="'+u.id+'">'+u.nombre+' '+u.apellido+' (C.I: '+u.cedula+')</option>');
                    });
                } else {
                    alert('No se encontraron estudiantes.');
                    $('#id_estudiante_manual').addClass('d-none').empty();
                }
            }
        });"""

good_js = """        $.ajax({
            url: '../controllers/buscar_usuarios_ajax.php',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                var res = JSON.parse(response);
                if(Array.isArray(res) && res.length > 0) {
                    var select = $('#id_estudiante_manual');
                    select.empty().removeClass('d-none');
                    res.forEach(function(u) {
                        select.append('<option value="'+u.id+'">'+u.nombre+' '+u.apellido+' (C.I: '+u.cedula+')</option>');
                    });
                } else if (res.error) {
                    alert('Error: ' + res.error);
                } else {
                    alert('No se encontraron estudiantes.');
                    $('#id_estudiante_manual').addClass('d-none').empty();
                }
            }
        });"""

content = content.replace(bad_js, good_js)

with open(r"C:\laragon\www\certificaciones\views\gestion_pagos.php", "w", encoding="utf-8") as f:
    f.write(content)

print("JS in gestion_pagos patched")
