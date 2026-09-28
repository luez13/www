<?php
// phase1_module_processing.php
$f = 'C:\laragon\www\certificaciones\models\module_processing.js';
$c = file_get_contents($f);

// Change label depending on window.isPNFA
$c = str_replace(
    'moduleDiv.appendChild(createElement("h4", "Módulo " + (index + 1)));',
    'var termName = window.isPNFA ? (index === 12 ? "Otro" : "Término " + (index + 1)) : "Módulo " + (index + 1);
    moduleDiv.appendChild(createElement("h4", termName));',
    $c
);

$c = str_replace(
    'moduleDiv.appendChild(createInput("text", "nombre_modulo[]", "Nombre del módulo"));',
    'var termPlaceholder = window.isPNFA ? "Nombre del término" : "Nombre del módulo";
    moduleDiv.appendChild(createInput("text", "nombre_modulo[]", termPlaceholder));',
    $c
);

file_put_contents($f, $c);
echo "Patched module_processing.js\n";
