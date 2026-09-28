<?php
// Patching gestion_pagos.php for fecha_gestion
$f = 'C:\laragon\www\certificaciones\views\gestion_pagos.php';
$c = file_get_contents($f);
$c = str_replace('<th>Gestionado Por</th>', "<th>Gestionado Por</th>\n                                <th>Fecha Gestión</th>", $c);
$c = str_replace('<?= htmlspecialchars($comp[\'gestor_nombre\']) ?> <?= htmlspecialchars($comp[\'gestor_apellido\']) ?>', "<?= htmlspecialchars(\$comp['gestor_nombre'] ?? '') ?> <?= htmlspecialchars(\$comp['gestor_apellido'] ?? '') ?>\n                                    </td>\n                                    <td class=\"text-left\">\n                                        <?= !empty(\$comp['fecha_gestion']) ? date('d/m/Y H:i', strtotime(\$comp['fecha_gestion'])) : '-' ?>", $c);
file_put_contents($f, $c);

// Patching gestion_cursos.php for PNFA
$f = 'C:\laragon\www\certificaciones\public\gestion_cursos.php';
$c = file_get_contents($f);
if (strpos($c, '<option value="PNFA">Programa Nacional de Formación Avanzada (PNFA)</option>') === false) {
    $c = str_replace('<?php if ($_SESSION[\'es_academico\']): ?>', '<option value="PNFA">Programa Nacional de Formación Avanzada (PNFA)</option>
                            <?php if ($_SESSION[\'es_academico\']): ?>', $c);
    $c = str_replace('<?php if ($_SESSION[\'es_academico\']): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6></div>', 
            '<?php $is_pnfa = false; // Determined dynamically ?>
        <div class="card shadow mb-4" id="section_modulos" style="<?= $_SESSION[\'es_academico\'] ? \'\' : \'display:none;\' ?>">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary" id="modulos_title">Módulos del Curso</h6></div>', $c);
    $c = str_replace('<label class="form-label">Número de módulos:</label>', '<label class="form-label" id="modulos_label">Número de módulos:</label>', $c);
}
file_put_contents($f, $c);

// Wait, doing this via script is risky for the UI JS mutations. I will write a custom JS injector.
echo "Patched gestion_pagos.php\n";
