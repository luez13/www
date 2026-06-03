<?php
/**
 * Helper de Autorización (Auth)
 * Centraliza la validación de roles en todo el sistema.
 */

function tieneAcceso($roles_permitidos) {
    if (!isset($_SESSION['id_rol'])) {
        return false;
    }
    
    // El Administrador (4) siempre tiene acceso a todo el backend
    if ($_SESSION['id_rol'] == 4) {
        return true;
    }
    
    // Validar si el rol actual está en la lista de permitidos para esta acción
    return in_array($_SESSION['id_rol'], $roles_permitidos);
}
