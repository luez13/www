<?php
$files = [
    __DIR__ . '/views/certificados/certificado_base.php',
    __DIR__ . '/views/certificados/certificado_logo_ministerio.php',
    __DIR__ . '/views/certificados/certificado_prueba.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Reemplazo para P1
    $searchP1 = <<<EOT
        \$posX = 140; // Default: Centro
        \$pCod = strtolower(\$f['posicion_codigo']);
        
        if (strpos(\$pCod, 'izq') !== false) \$posX = 65;
EOT;
    $replaceP1 = <<<EOT
        \$posX = 140; // Default: Centro
        \$pCod = strtolower(\$f['posicion_codigo']);
        
        if (count(\$firmasP1) === 1) {
            \$posX = 140;
        } else {
            if (strpos(\$pCod, 'izq') !== false) \$posX = 65;
EOT;

    $content = str_replace($searchP1, $replaceP1, $content);

    // Y arreglar el cierre del else para P1
    $searchP1_end = <<<EOT
            else if (strpos(\$pCod, 'firme4') !== false || strpos(\$pCod, 'extra') !== false) \$posX = 140;

        \$w_box = 60; 
EOT;
    $replaceP1_end = <<<EOT
            else if (strpos(\$pCod, 'firme4') !== false || strpos(\$pCod, 'extra') !== false) \$posX = 140;
        }

        \$w_box = 60; 
EOT;
    $content = str_replace($searchP1_end, $replaceP1_end, $content);

    // Reemplazo para P2
    $searchP2 = <<<EOT
        \$posX2 = 175; // Default: Cuarta
        \$pCod2 = strtolower(\$f['posicion_codigo']);
        
        if (strpos(\$pCod2, 'izq') !== false) \$posX2 = 35;
EOT;
    $replaceP2 = <<<EOT
        \$posX2 = 175; // Default: Cuarta
        \$pCod2 = strtolower(\$f['posicion_codigo']);
        
        if (count(\$firmasP2) === 1) {
            \$posX2 = 140;
        } else {
            if (strpos(\$pCod2, 'izq') !== false) \$posX2 = 35;
EOT;
    $content = str_replace($searchP2, $replaceP2, $content);

    $searchP2_end = <<<EOT
        else if (strpos(\$pCod2, 'cuarta') !== false || strpos(\$pCod2, 'firme4') !== false || strpos(\$pCod2, 'extra') !== false) \$posX2 = 175;

        \$w_box2 = 55; 
EOT;
    $replaceP2_end = <<<EOT
        else if (strpos(\$pCod2, 'cuarta') !== false || strpos(\$pCod2, 'firme4') !== false || strpos(\$pCod2, 'extra') !== false) \$posX2 = 175;
        }

        \$w_box2 = 55; 
EOT;
    $content = str_replace($searchP2_end, $replaceP2_end, $content);

    file_put_contents($file, $content);
    echo "Actualizado: " . basename($file) . "\n";
}
echo "¡Hecho!\n";
