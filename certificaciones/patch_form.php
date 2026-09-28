<?php
$file = 'views/curso_formulario.php';
$c = file_get_contents($file);

// Replace Fechas
$c = str_replace(
    "<?php if (\$_SESSION['es_academico']): ?>\r\n        <div class=\"card shadow mb-4\">\r\n            <div class=\"card-header py-3\">\r\n                <h6 class=\"m-0 font-weight-bold text-primary\">Fechas y Horarios</h6>",
    "<?php if (\$_SESSION['es_academico'] || (isset(\$curso_editar['tipo_curso']) && \$curso_editar['tipo_curso'] === 'PNFA')): ?>\r\n        <div class=\"card shadow mb-4\">\r\n            <div class=\"card-header py-3\">\r\n                <h6 class=\"m-0 font-weight-bold text-primary\">Fechas y Horarios</h6>",
    $c
);

// Replace Modulos
$c = str_replace(
    "<?php if (\$_SESSION['es_academico']): ?>\r\n        <div class=\"card shadow mb-4\">\r\n            <div class=\"card-header py-3 d-flex justify-content-between align-items-center\">\r\n                <h6 class=\"m-0 font-weight-bold text-primary\">Módulos del Curso</h6>",
    "<?php \$is_pnfa = (isset(\$curso_editar['tipo_curso']) && \$curso_editar['tipo_curso'] === 'PNFA'); ?>\r\n        <?php if (\$_SESSION['es_academico'] || \$is_pnfa): ?>\r\n        <div class=\"card shadow mb-4\">\r\n            <div class=\"card-header py-3 d-flex justify-content-between align-items-center\">\r\n                <h6 class=\"m-0 font-weight-bold text-primary\"><?= \$is_pnfa ? '<i class=\"fas fa-layer-group me-2\"></i>Términos del PNFA' : 'Módulos del Curso' ?></h6>",
    $c
);

// Also fix the JS variable if not already there
if (strpos($c, 'window.isPNFA =') === false) {
    $c = str_replace(
        '<script src="../models/module_processing.js"></script>',
        "<script>\nwindow.isPNFA = <?= isset(\$is_pnfa) && \$is_pnfa ? 'true' : 'false' ?>;\n</script>\n<script src=\"../models/module_processing.js\"></script>",
        $c
    );
}

// remove the php endif if the modulos section gets the same treatment inside module loop. But no, we just replaced the wrapper. Wait! For existing modules inside the loop:
$c = str_replace('Módulo <?= ', '<?= $is_pnfa ? "Término " : "Módulo " ?><?= ', $c);

file_put_contents($file, $c);
echo "Done.\n";
