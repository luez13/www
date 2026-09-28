<?php
$file = __DIR__ . '/views/generar_acta_cierre.php';
$content = file_get_contents($file);

$regex = '/foreach\s*\(\$resultados\s*as\s*\$r\)\s*\{.*?\$lista_para_pdf\[\]\s*=\s*array\([^;]+;\s*\n\}/is';

$replace_loop = <<<EOT
foreach(\$resultados as \$r) {
    \$estado = '';
    \$nota_final = 'N/A';
    
    // Replicando el comportamiento del PDF original:
    // El PDF evaluaba (bool)\$al['completado'], por lo que 'f' (falso en Postgres) era evaluado como verdadero en PHP.
    // Esto significa que en cursos normales, todos eran tratados como "completados" si tenían registro.
    // Además, si tienen nota, automáticamente el estatus se rige por la nota.
    \$es_completado = true; // Emulando el (bool)'f' === true del generador FPDF
    
    if (\$r['completado'] === false || \$r['completado'] === 0 || \$r['completado'] === '0' || \$r['completado'] === null) {
        \$es_completado = false; // Solo los explícitamente vacíos son falsos
    }

    if (!\$es_completado && \$r['promedio_decimal'] === null) {
        \$estado = 'REPROBADO';
        \$reprobados++;
    } else {
        if (\$r['promedio_decimal'] !== null && \$r['promedio_decimal'] !== '') {
            \$nota_final = round((float)\$r['promedio_decimal']);
            \$suma_promedios += \$nota_final;
            
            if (\$nota_final >= \$nota_min) {
                \$estado = 'APROBADO';
                \$aprobados++;
            } else {
                \$estado = 'PARTICIPACIÓN';
                \$participantes_count++;
            }
        } else {
            \$estado = 'PARTICIPACIÓN';
            \$participantes_count++;
        }
    }

    \$lista_para_pdf[] = array(
        'cedula' => \$r['cedula'],
        'alumno' => strtoupper(\$r['apellido'] . ' ' . \$r['nombre']),
        'nota'   => \$nota_final,
        'estado' => \$estado
    );
}
EOT;

$content = preg_replace($regex, $replace_loop, $content);
file_put_contents($file, $content);
echo "Lógica relajada aplicada a la vista previa.\n";
