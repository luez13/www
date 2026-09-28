<?php
$files = [
    __DIR__ . '/views/gestion_pagos.php',
    __DIR__ . '/views/mis_pagos.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace $comp['monto']
    $content = str_replace(
        "number_format(\$comp['monto'], 2)", 
        "number_format(\$comp['monto'], 2, ',', '.')", 
        $content
    );
    
    // Replace $pago['monto']
    $content = str_replace(
        "number_format(\$pago['monto'], 2)", 
        "number_format(\$pago['monto'], 2, ',', '.')", 
        $content
    );

    // Replace $c['costo'] if it exists
    $content = str_replace(
        "number_format(\$c['costo'], 2)", 
        "number_format(\$c['costo'], 2, ',', '.')", 
        $content
    );
    
    file_put_contents($file, $content);
    echo "Actualizado: " . basename($file) . "\n";
}
echo "Hecho.\n";
