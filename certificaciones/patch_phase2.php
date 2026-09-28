<?php
$file = 'views/curso.php';
$c = file_get_contents($file);

$search = '<h4 class="mt-4 border-bottom pb-2">Módulos del Curso</h4>
            <div class="accordion" id="accordionModulos">';
$replace = '<?php if ($_SESSION[\'es_academico\']): ?>
            <h4 class="mt-4 border-bottom pb-2">Módulos del Curso</h4>
            <div class="accordion" id="accordionModulos">';

if (strpos($c, $search) !== false) {
    $c = str_replace($search, $replace, $c);
    
    // Now find the end of the accordion
    $end_search = '<?php endforeach; ?>
            </div>';
    $end_replace = '<?php endforeach; ?>
            </div>
            <?php endif; ?>';
    
    if (strpos($c, $end_search) !== false) {
        $c = str_replace($end_search, $end_replace, $c);
        file_put_contents($file, $c);
        echo "Patched curso.php modules visibility.\n";
    } else {
        echo "Could not find end of modules accordion.\n";
    }
} else {
    echo "Could not find modules start.\n";
}

// Let's also check historial.php for Certificados Modulares
$file2 = 'views/historial.php';
$c2 = file_get_contents($file2);

$search2 = '<h6 class="small font-weight-bold text-success mb-2 border-bottom pb-1">Certificados Modulares:</h6>';
$replace2 = '<?php if ($_SESSION[\'es_academico\']): ?>
                                        <h6 class="small font-weight-bold text-success mb-2 border-bottom pb-1">Certificados Modulares:</h6>';

if (strpos($c2, $search2) !== false) {
    $c2 = str_replace($search2, $replace2, $c2);
    
    // The foreach loop closes right below it
    $end_search2 = '<?php endforeach; ?>
                                          </div>';
    $end_replace2 = '<?php endforeach; ?>
                                          </div>
                                          <?php endif; ?>';
    
    if (strpos($c2, $end_search2) !== false) {
        $c2 = str_replace($end_search2, $end_replace2, $c2);
        file_put_contents($file2, $c2);
        echo "Patched historial.php modulares visibility.\n";
    } else {
        echo "Could not find end of modulares loop.\n";
    }
} else {
    echo "Could not find modulares start.\n";
}

?>
