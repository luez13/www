<?php
// public/usuarios.php

// Incluimos el header una sola vez.
include '../views/header.php';
?>

<div class="container mt-4">
    <h3>Gestión de Usuarios</h3>

    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por cédula, nombre o apellido...">
    </div>

    <div id="user-list-container" class="accordion">
        <div class="text-center p-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </div>
    
    <div id="pagination-container" class="mt-3"></div>
</div>

<script>
let searchTimeout;

function loadUsers(page, busqueda) {
    $.ajax({
        url: '../controllers/buscarUsuarios.php', // Llamamos a nuestro nuevo controlador
        type: 'POST',
        data: {
            page: page,
            busqueda: busqueda
        },
        dataType: 'json', // Esperamos una respuesta en formato JSON
        success: function(response) {
            // Inyectamos el HTML recibido en los contenedores correspondientes
            $('#user-list-container').html(response.userHtml);
            $('#pagination-container').html(response.paginationHtml);
            
            // Re-aplicamos los eventos a los nuevos formularios cargados
            handleFormSubmission(); 
        },
        error: function() {
            $('#user-list-container').html('<div class="alert alert-danger">Error al cargar los usuarios.</div>');
        }
    });
}

function handleFormSubmission() {
    $('.editar-usuario-form button[type="submit"]').off('click').on('click', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        var actionValue = $(this).val();

        if (actionValue === 'eliminar_usuario') {
            if (!confirm("¿Está seguro de que desea eliminar este usuario? Esta acción es irreversible.")) {
                return;
            }
        }
        
        // Añadimos la acción al FormData
        var formData = form.serializeArray();
        formData.push({name: 'action', value: actionValue});

        $.ajax({
            url: '../controllers/usuarios_controlador.php', // El controlador que maneja la lógica de editar/eliminar
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                alert(response); // Muestra la respuesta del controlador (ej: "Usuario eliminado")
                // Recargamos la lista para ver los cambios
                const currentPage = $('#pagination-container .page-item.active .page-link').text() || 1;
                const currentSearch = $('#searchInput').val();
                loadUsers(currentPage, currentSearch);
            },
            error: function() {
                alert('Hubo un error al procesar la solicitud.');
            }
        });
    });
}

// --- EVENTOS ---
$(document).ready(function() {
    // 1. Carga inicial de usuarios al entrar a la página
    loadUsers(1, '');

    // 2. Evento de búsqueda al escribir en el campo
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout); // Resetea el temporizador anterior
        const searchTerm = $(this).val();
        
        // Espera 300ms después de que el usuario deja de escribir para buscar
        searchTimeout = setTimeout(function() {
            loadUsers(1, searchTerm); // La búsqueda siempre va a la página 1
        }, 300);
    });
});
</script>

<?php
// Incluimos el footer una sola vez.
include '../views/footer.php';
?>