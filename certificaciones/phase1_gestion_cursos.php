<?php
// phase1_gestion_cursos.php
$f = 'C:\laragon\www\certificaciones\public\gestion_cursos.php';
$c = file_get_contents($f);

// 1. Add PNFA option
if (strpos($c, 'value="PNFA"') === false) {
    $c = preg_replace(
        '/(<option value="charla">Charla<\/option>)/',
        "$1\n                              <option value=\"PNFA\">Programa Nacional de Formación Avanzada (PNFA)</option>",
        $c
    );
}

// 2. Hide modulos section by default for non-academic, but allow JS to show it
$c = str_replace(
    '<?php if ($_SESSION[\'es_academico\']): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Módulos del Curso</h6></div>',
    '<div class="card shadow mb-4" id="section_modulos" style="<?= $_SESSION[\'es_academico\'] ? \'\' : \'display:none;\' ?>">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary" id="modulos_title">Módulos del Curso</h6></div>',
    $c
);

// 3. Update Modulos label
$c = str_replace(
    '<label class="form-label">Número de módulos:</label>',
    '<label class="form-label" id="modulos_label">Número de módulos:</label>',
    $c
);

// 4. Remove closing endif for Modulos section
$c = preg_replace(
    '/(<div id="moduleContainer">\s*<\/div>\s*<\/div>\s*<\/div>)\s*<\?php endif; \?>/',
    '$1',
    $c
);

// 5. Inject JS mutation listener
$js = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tipoSelect = document.querySelector('select[name="tipo_curso"]');
    if (tipoSelect) {
        tipoSelect.addEventListener('change', function() {
            var val = this.value;
            var sec = document.getElementById('section_modulos');
            var title = document.getElementById('modulos_title');
            var label = document.getElementById('modulos_label');
            var isAcad = <?= \$_SESSION['es_academico'] ? 'true' : 'false' ?>;
            var numModulosInput = document.getElementById('numero_modulos');
            
            if (val === 'PNFA') {
                sec.style.display = 'block';
                title.innerHTML = '<i class="fas fa-layer-group me-2"></i>Términos del PNFA';
                label.innerHTML = 'Número de términos (Máx 12 + Otro):';
                // Trigger global flag for module_processing.js
                window.isPNFA = true;
                if (numModulosInput.value) numModulosInput.dispatchEvent(new Event('blur'));
            } else if (isAcad) {
                sec.style.display = 'block';
                title.innerHTML = '<i class="fas fa-layer-group me-2"></i>Módulos del Curso';
                label.innerHTML = 'Número de módulos:';
                window.isPNFA = false;
                if (numModulosInput.value) numModulosInput.dispatchEvent(new Event('blur'));
            } else {
                sec.style.display = 'none';
                window.isPNFA = false;
            }
        });
    }
});
</script>
JS;

if (strpos($c, 'window.isPNFA') === false) {
    $c = str_replace('<script src="../models/module_processing.js"></script>', $js . "\n<script src=\"../models/module_processing.js\"></script>", $c);
}

file_put_contents($f, $c);
echo "Patched gestion_cursos.php\n";
