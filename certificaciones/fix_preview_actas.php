<?php
$file = __DIR__ . '/views/generar_acta_cierre.php';
$content = file_get_contents($file);

// 1. Añadir nota_minima_aprobatoria al SELECT
$content = str_replace(
    "SELECT nombre_curso, fecha_finalizacion, inicio_mes FROM cursos.cursos",
    "SELECT nombre_curso, fecha_finalizacion, inicio_mes, nota_minima_aprobatoria FROM cursos.cursos",
    $content
);

// 2. Definir $nota_min justo después de extraer $curso
$content = preg_replace(
    "/\\\$fecha_fin = \\\$curso \? date\('d\/m\/Y', strtotime\(\\\$curso\['fecha_finalizacion'\]\)\) : date\('d\/m\/Y'\);/i",
    "\$fecha_fin = \$curso ? date('d/m/Y', strtotime(\$curso['fecha_finalizacion'])) : date('d/m/Y');\n\$nota_min = isset(\$curso['nota_minima_aprobatoria']) ? (int)\$curso['nota_minima_aprobatoria'] : 12;",
    $content
);

// 3. Regex para matar el loop entero desde "foreach($resultados as $r) {" hasta el primer "} // fin loop" o algo así
$regex = '/foreach\s*\(\$resultados\s*as\s*\$r\)\s*\{.*?\$lista_para_pdf\[\]\s*=\s*array\([^;]+;\s*\n\}/is';

$replace_loop = <<<EOT
foreach(\$resultados as \$r) {
    \$estado = '';
    \$nota_final = 'N/A';
    
    // El booleano puede venir como 't', 'f', 1, 0, true, false dependiendo de PDO y PostgreSQL
    \$es_completado = (isset(\$r['completado']) && (\$r['completado'] === true || \$r['completado'] == '1' || \$r['completado'] === 't' || \$r['completado'] === 'true'));

    if (!\$es_completado) {
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
echo "Reemplazo ejecutado.\n";
