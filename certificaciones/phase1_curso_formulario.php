<?php
// phase1_curso_formulario.php
$f = 'C:\laragon\www\certificaciones\views\curso_formulario.php';
$c = file_get_contents($f);

// Determine isPNFA
$c = str_replace(
    '<?php if ($_SESSION[\'es_academico\']): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6></div>',
    '<?php $is_pnfa = (isset($curso_editar[\'tipo_curso\']) && $curso_editar[\'tipo_curso\'] === \'PNFA\'); ?>
        <div class="card shadow mb-4" id="section_modulos" style="<?= ($_SESSION[\'es_academico\'] || $is_pnfa) ? \'\' : \'display:none;\' ?>">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary" id="modulos_title"><?= $is_pnfa ? \'<i class="fas fa-layer-group me-2"></i>Términos del PNFA\' : \'<i class="fas fa-layer-group me-2"></i>Módulos del Curso\' ?></h6></div>',
    $c
);

$c = str_replace(
    '<label class="form-label">Número de módulos:</label>',
    '<label class="form-label" id="modulos_label"><?= $is_pnfa ? \'Número de términos:\' : \'Número de módulos:\' ?></label>',
    $c
);

// Inject JS flag at the bottom
if (strpos($c, 'window.isPNFA =') === false) {
    $c = str_replace(
        '<script src="../models/module_processing.js"></script>',
        "<script>\nwindow.isPNFA = <?= isset(\$is_pnfa) && \$is_pnfa ? 'true' : 'false' ?>;\n</script>\n<script src=\"../models/module_processing.js\"></script>",
        $c
    );
}

// Remove closing endif for Modulos section
$c = preg_replace(
    '/(<div id="moduleContainer">\s*<\?php foreach \(\$curso_editar\[\'modulos\'\] as \$modulo\): \?>.*?<\/div>\s*<\/div>\s*<\/div>)\s*<\?php endif; \?>/s',
    '$1',
    $c
);

// Update module labels in PHP edit mode
$c = preg_replace(
    '/(<h4[^>]*>)Módulo (<\?= \$idx \+ 1 \?>)/',
    '$1<?= $is_pnfa ? ($idx === 12 ? "Otro" : "Término " . ($idx + 1)) : "Módulo " . ($idx + 1) ?>',
    $c
);

$c = preg_replace(
    '/(placeholder=")Nombre del módulo(")/',
    '$1<?= $is_pnfa ? "Nombre del término" : "Nombre del módulo" ?>$2',
    $c
);

file_put_contents($f, $c);
echo "Patched curso_formulario.php\n";
