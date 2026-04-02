<?php
/**
 * Herramienta de Diagnóstico y Reparación de Posiciones de Firma
 * -------------------------------------------------------------
 * Muestra las posiciones actuales y permite agregar las faltantes (Cuarta Firma).
 */
require_once('../config/model.php');

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = new DB();
    $pdo = $db->getConn();

    // 1. Mostrar Posiciones Actuales
    echo "=== POSICIONES DE FIRMA ACTUALES ===\n\n";
    $stmt = $pdo->query("SELECT * FROM cursos.posiciones_firma ORDER BY id_posicion ASC");
    $posiciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($posiciones)) {
        echo "No hay posiciones registradas.\n";
    } else {
        printf("%-3s | %-20s | %-30s | %-6s\n", "ID", "CODIGO", "DESCRIPCION", "PAGINA");
        echo str_repeat("-", 70) . "\n";
        foreach ($posiciones as $p) {
            printf("%-3d | %-20s | %-30s | %-6d\n", 
                $p['id_posicion'], 
                $p['codigo_posicion'], 
                $p['descripcion_posicion'], 
                $p['pagina']
            );
        }
    }

    // 2. Lógica de Inserción (Modo Seguro para Servidor)
    if (isset($_GET['reparar']) && $_GET['reparar'] == '1') {
        echo "\n\n=== EJECUTANDO REPARACIÓN (MODO SEGURO) ===\n\n";

        // Definir las 2 posiciones oficiales para la 4ta columna
        // Nota: Bajamos el ID a 9 en caso de que 7 u 8 estén ocupados en local
        $nuevas = [
            [
                'codigo' => 'P1_CUARTA_FIRMA',
                'desc' => 'Página 1, Cuarta Firma Adicional',
                'pag' => 1
            ],
            [
                'codigo' => 'P2_CUARTA_FIRMA',
                'desc' => 'Página 2, Cuarta Firma Adicional',
                'pag' => 2
            ]
        ];

        foreach ($nuevas as $n) {
            // Buscamos si el CÓDIGO ya existe, sin importar el ID
            $check = $pdo->prepare("SELECT id_posicion FROM cursos.posiciones_firma WHERE codigo_posicion = :cod");
            $check->execute([':cod' => $n['codigo']]);
            $exists = $check->fetch();

            if ($exists) {
                echo "⚠️ El código '{$n['codigo']}' ya existe con el ID {$exists['id_posicion']}. No se requiere acción.\n";
            } else {
                // Si no existe, lo insertamos dejando que la DB asigne el siguiente ID serial
                $ins = $pdo->prepare("INSERT INTO cursos.posiciones_firma (codigo_posicion, descripcion_posicion, pagina) 
                                      VALUES (:cod, :desc, :pag)");
                $ins->execute([
                    ':cod' => $n['codigo'],
                    ':desc' => $n['desc'],
                    ':pag' => $n['pag']
                ]);
                echo "✅ Insertada satisfactoriamente: {$n['codigo']}\n";
            }
        }

        echo "\nNota: Se ha desactivado la limpieza automática de registros 'EXTRA' para evitar errores de integridad (Llave Foránea).";
        echo "\nSi deseas limpiar tu base de datos local, primero debes cambiar el cargo de las firmas que usan las posiciones 8 o 9 en la configuración de tus cursos.";
        echo "\n\nFinalizado. Vuelve a cargar la página para ver el resultado.";
    } else {
        echo "\n\nPara agregar las posiciones de 'Cuarta Firma' faltantes automáticamente,";
        echo "\nagrega ?reparar=1 al final de la URL en tu navegador.";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage();
}
