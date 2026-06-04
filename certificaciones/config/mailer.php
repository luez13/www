<?php
// config/mailer.php

/**
 * Enviar correo electrónico usando la función nativa mail() de PHP.
 * Extrae la configuración dinámicamente de cursos.config_sistema.
 *
 * @param string $destinatario Correo del destinatario.
 * @param string $asunto Asunto del correo.
 * @param string $mensajeHtml Cuerpo del mensaje en formato HTML.
 * @return bool True si la orden de envío fue aceptada por el servidor, false en caso contrario.
 */
if (!function_exists('enviarCorreo')) {
    function enviarCorreo($destinatario, $asunto, $mensajeHtml) {
    try {
        // Asegurarse de tener la clase DB disponible
        if (!class_exists('DB')) {
            require_once __DIR__ . '/model.php';
        }
        
        $db = new DB();
        $stmt = $db->prepare("SELECT clave_config, valor_config FROM cursos.config_sistema WHERE clave_config IN ('CORREO_CONTACTO_POR_DEFECTO', 'NOMBRE_INSTITUCION')");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nombreInstitucion = 'Administracion';
        $correoContacto = 'no-reply@institucion.edu';

        foreach ($rows as $row) {
            if ($row['clave_config'] === 'NOMBRE_INSTITUCION' && !empty($row['valor_config'])) {
                $nombreInstitucion = $row['valor_config'];
            }
            if ($row['clave_config'] === 'CORREO_CONTACTO_POR_DEFECTO' && !empty($row['valor_config'])) {
                $correoContacto = $row['valor_config'];
            }
        }
        
        // Limpiar el nombre de la institución para evitar inyección en cabeceras pero permitiendo acentos
        $nombreInstitucion = preg_replace('/[^a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ]/u', '', $nombreInstitucion);
        
        // Cabeceras para correo HTML
        $cabeceras  = "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
        
        // Cabeceras de remitente
        $cabeceras .= "From: " . $nombreInstitucion . " <" . $correoContacto . ">\r\n";
        $cabeceras .= "Reply-To: " . $correoContacto . "\r\n";
        $cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Plantilla HTML
        $htmlCompleto = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <div style='background-color: #4e73df; padding: 20px; text-align: center; color: #ffffff;'>
                    <h2 style='margin: 0;'>{$nombreInstitucion}</h2>
                </div>
                <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                    {$mensajeHtml}
                </div>
                <div style='background-color: #f8f9fc; padding: 15px; text-align: center; font-size: 12px; color: #858796; border-top: 1px solid #e3e6f0;'>
                    <p style='margin: 0;'>Este es un mensaje automático generado por el sistema.</p>
                    <p style='margin: 5px 0 0 0;'>Contacto: {$correoContacto}</p>
                </div>
            </div>
        </div>";

        // Usar @mail para silenciar posibles advertencias de servidor (evita romper JSON AJAX)
        $enviado = @mail($destinatario, $asunto, $htmlCompleto, $cabeceras);
        
        return $enviado;
        
    } catch (Exception $e) {
        // Falla silenciosa
        error_log("Error al intentar enviar correo: " . $e->getMessage());
        return false;
    }
}
}
