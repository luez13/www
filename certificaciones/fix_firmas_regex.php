<?php
$files = [
    __DIR__ . '/views/certificados/certificado_base.php',
    __DIR__ . '/views/certificados/certificado_logo_ministerio.php',
    __DIR__ . '/views/certificados/certificado_prueba.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "No existe: " . basename($file) . "\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $original_content = $content;

    // Para la página 1 (P1)
    // Buscamos: $pCod = strtolower($f['posicion_codigo']); \n if (strpos($pCod, 'izq') !== false) $posX = 
    // Y lo cambiamos a la validación de count($firmasP1) === 1
    
    $content = preg_replace(
        "/(pCod\s*=\s*strtolower\([^\)]+\);)\s*if\s*\(\s*strpos\(\s*\\\$pCod,\s*'izq'\s*\)\s*!==\s*false\s*\)\s*\\\$posX\s*=\s*([0-9]+);/i",
        "$1\n        if (count(\$firmasP1) === 1) {\n            \$posX = 140;\n        } else if (strpos(\$pCod, 'izq') !== false) \$posX = $2;",
        $content
    );

    // Para la página 2 (P2)
    $content = preg_replace(
        "/(pCod2\s*=\s*strtolower\([^\)]+\);)\s*if\s*\(\s*strpos\(\s*\\\$pCod2,\s*'izq'\s*\)\s*!==\s*false\s*\)\s*\\\$posX2\s*=\s*([0-9]+);/i",
        "$1\n        if (count(\$firmasP2) === 1) {\n            \$posX2 = 140;\n        } else if (strpos(\$pCod2, 'izq') !== false) \$posX2 = $2;",
        $content
    );

    if ($content !== $original_content) {
        file_put_contents($file, $content);
        echo "Actualizado con éxito: " . basename($file) . "\n";
    } else {
        echo "Sin cambios (ya aplicado o no encontrado): " . basename($file) . "\n";
    }
}
echo "Proceso terminado.\n";
