<?php
/**
 * Helper de Autorizacin (Auth)
 * Centraliza la validacin de roles en todo el sistema.
 */

function tieneAcceso($roles_permitidos) {
    if (!isset($_SESSION['id_rol'])) {
        return false;
    }
    
    // El Super Admin (7) siempre tiene acceso a todo el backend
    if ($_SESSION['id_rol'] == 7) {
        return true;
    }
    
    // Validar si el rol actual est en la lista de permitidos para esta accin
    return in_array($_SESSION['id_rol'], $roles_permitidos);
}
