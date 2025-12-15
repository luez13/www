<?php
// views/generar_acta_cierre.php

include '../controllers/init.php';
require_once('../config/model.php');

if (!isset($_SESSION['user_id'])) { die('Acceso denegado.'); }

$db = new DB();
$conn = $db->getConn();

$id_curso = isset($_REQUEST['id_curso']) ? (int)$_REQUEST['id_curso'] : 0;
if ($id_curso === 0) { echo '<div class="alert alert-danger">Error ID.</div>'; exit; }

// 1. Datos del Curso
$stmt = $conn->prepare("SELECT nombre_curso, fecha_finalizacion, inicio_mes FROM cursos.cursos WHERE id_curso = :id");
$stmt->execute(['id' => $id_curso]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_curso = $curso ? $curso['nombre_curso'] : 'Desconocido';
$fecha_fin = $curso ? date('d/m/Y', strtotime($curso['fecha_finalizacion'])) : date('d/m/Y');

// 2. Estadísticas Reales y Listado para PDF
// Calculamos el promedio ponderado
$sql_stats = "
    SELECT 
        u.id, u.nombre, u.apellido, u.cedula,
        AVG(promedio_materia) as promedio_decimal
    FROM (
        SELECT 
            np.id_usuario,
            m.id_materia_bimestre,
            SUM(np.calificacion_obtenida * (ac.ponderacion_porcentaje / 100)) as promedio_materia
        FROM cursos.notas_participante np
        JOIN cursos.actividades_config ac ON np.id_actividad_config = ac.id_actividad_config
        JOIN cursos.materias_bimestre m ON ac.id_materia_bimestre = m.id_materia_bimestre
        WHERE m.id_curso = :id
        GROUP BY np.id_usuario, m.id_materia_bimestre
    ) as promedios_por_materia
    JOIN cursos.usuarios u ON promedios_por_materia.id_usuario = u.id
    GROUP BY u.id, u.nombre, u.apellido, u.cedula
    ORDER BY u.apellido ASC
";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->execute(['id' => $id_curso]);
$resultados = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);

$total_inscritos = count($resultados);
$aprobados = 0;
$reprobados = 0;
$suma_promedios = 0;

// Array limpio para pasarlo a JavaScript (JSON)
$lista_para_pdf = array(); 

foreach($resultados as $r) {
    $prom_dec = (float)$r['promedio_decimal'];
    
    // --- LÓGICA DE REDONDEO ---
    // round() en PHP sigue la regla estándar: .5 sube, .4 baja.
    $nota_final = round($prom_dec);
    
    $suma_promedios += $nota_final;
    
    // Determinar estado (Asumiendo 12 como mínima aprobatoria ya que 11.5 sube a 12)
    // Si la mínima es 10, cambia el 12 por 10.
    $estado = ($nota_final >= 12) ? 'APROBADO' : 'REPROBADO'; 
    
    if ($estado == 'APROBADO') {
        $aprobados++;
    } else {
        $reprobados++;
    }

    // Agregar a la lista del PDF
    $lista_para_pdf[] = array(
        'cedula' => $r['cedula'],
        'alumno' => strtoupper($r['apellido'] . ' ' . $r['nombre']),
        'nota'   => $nota_final, // Nota redondeada (entero)
        'estado' => $estado
    );
}

$promedio_general = $total_inscritos > 0 ? ($suma_promedios / $total_inscritos) : 0;

// Convertir datos PHP a JSON para JS
$json_pdf = json_encode($lista_para_pdf);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Acta de Cierre Final</h1>
        <button class="btn btn-secondary btn-sm" onclick="loadPage('../public/editar_cursos.php', { page: 1 })">
            <i class="fas fa-arrow-left"></i> Volver
        </button>
    </div>

    <p class="mb-4">Diplomado: <strong><?= htmlspecialchars($nombre_curso) ?></strong></p>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-primary">
                <div class="card-body">
                    <h5 class="font-weight-bold text-primary mb-3">Estadísticas del Diplomado</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-gray-800">Total Procesados:</span>
                        <span class="h5 font-weight-bold"><?= $total_inscritos ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php 
                            $porc_aprob = $total_inscritos > 0 ? ($aprobados/$total_inscritos)*100 : 0; 
                            $porc_reprob = $total_inscritos > 0 ? ($reprobados/$total_inscritos)*100 : 0;
                        ?>
                        <div class="progress-bar bg-success" style="width: <?= $porc_aprob ?>%"></div>
                        <div class="progress-bar bg-danger" style="width: <?= $porc_reprob ?>%"></div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h6 class="text-success font-weight-bold">Aprobados</h6>
                            <span class="h4"><?= $aprobados ?></span>
                        </div>
                        <div class="col-4 border-end">
                            <h6 class="text-danger font-weight-bold">Reprobados</h6>
                            <span class="h4"><?= $reprobados ?></span>
                        </div>
                        <div class="col-4">
                            <h6 class="text-info font-weight-bold">Promedio</h6>
                            <span class="h4"><?= number_format($promedio_general, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100 py-2 border-left-success">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="font-weight-bold text-success mb-3">Documento Oficial</h5>
                    
                    <p class="text-muted small mb-3">
                        Se generará el Acta de Cierre con la lista de calificaciones finales redondeadas.
                        <br><strong>Criterio:</strong> Nota >= 12 aprueba (redondeo 0.5 hacia arriba).
                    </p>

                    <?php if ($total_inscritos > 0): ?>
                        <button class="btn btn-success btn-lg shadow" onclick="generarPDF()">
                            <i class="fas fa-file-pdf fa-lg me-2"></i> Descargar Acta de Cierre
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            No hay notas registradas
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Vista Previa de Datos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr><th>Cédula</th><th>Alumno</th><th>Nota Final</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($lista_para_pdf as $item): ?>
                        <tr>
                            <td><?= $item['cedula'] ?></td>
                            <td><?= $item['alumno'] ?></td>
                            <td class="text-center font-weight-bold"><?= $item['nota'] ?></td>
                            <td class="<?= $item['estado']=='APROBADO'?'text-success':'text-danger' ?>"><?= $item['estado'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Recibir datos de PHP
    const DATOS_ACTA = <?= $json_pdf ?>;
    const INFO_CURSO = {
        nombre: "<?= htmlspecialchars($nombre_curso) ?>",
        fecha: "<?= $fecha_fin ?>"
    };

    function generarPDF() {
        if (!window.jspdf) { alert("Librería PDF cargando, intente en un segundo..."); return; }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // --- 1. ENCABEZADO ---
        // Intentar cargar logo (Asegúrate que la ruta sea accesible públicamente)
        var logoImg = new Image();
        logoImg.src = "../public/assets/img/logo.png"; // Ruta relativa a la carpeta public
        
        // Dibujar logo si carga, sino solo texto
        logoImg.onload = function() {
            doc.addImage(logoImg, 'PNG', 15, 10, 25, 25);
            generarContenidoPDF(doc);
        };
        logoImg.onerror = function() {
            generarContenidoPDF(doc); // Generar sin logo si falla
        };
    }

    function generarContenidoPDF(doc) {
        // Títulos
        doc.setFont("helvetica", "bold");
        doc.setFontSize(16);
        doc.text("ACTA DE CIERRE FINAL DE DIPLOMADO", 105, 20, null, null, "center");
        
        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text("UPTAIET - Coordinación de Formación Permanente", 105, 28, null, null, "center");

        // Info del Curso
        doc.setFontSize(11);
        doc.text("Diplomado: " + INFO_CURSO.nombre, 14, 45);
        doc.text("Fecha de Cierre: " + INFO_CURSO.fecha, 14, 52);

        // --- 2. TABLA DE NOTAS (Usando AutoTable) ---
        // Preparamos los datos para la tabla [Cédula, Nombre, Nota, Estado]
        let bodyData = DATOS_ACTA.map(d => [d.cedula, d.alumno, d.nota, d.estado]);

        doc.autoTable({
            startY: 60,
            head: [['Cédula', 'Participante', 'Nota Final', 'Estado']],
            body: bodyData,
            theme: 'grid',
            headStyles: { fillColor: [44, 62, 80], textColor: 255, halign: 'center' },
            columnStyles: {
                0: { cellWidth: 30 }, // Cédula
                2: { cellWidth: 25, halign: 'center', fontStyle: 'bold' }, // Nota
                3: { cellWidth: 30, halign: 'center' }  // Estado
            },
            didParseCell: function(data) {
                // Colorear texto de Reprobados
                if (data.section === 'body' && data.column.index === 3) {
                    if (data.cell.raw === 'REPROBADO') {
                        data.cell.styles.textColor = [231, 76, 60]; // Rojo
                    } else {
                        data.cell.styles.textColor = [39, 174, 96]; // Verde
                    }
                }
            }
        });

        // --- 3. FIRMAS (Al final) ---
        let finalY = doc.lastAutoTable.finalY + 40; // Espacio después de la tabla
        
        // Verificar si cabe en la hoja, si no, nueva página
        if (finalY > 250) {
            doc.addPage();
            finalY = 40;
        }

        // Líneas de firma
        doc.setLineWidth(0.5);
        
        // Firma 1: Coordinador
        doc.line(30, finalY, 90, finalY);
        doc.setFontSize(10);
        doc.text("Coord. Formación Permanente", 60, finalY + 5, null, null, "center");

        // Firma 2: Vicerrectorado (o Facilitador, según configures)
        doc.line(120, finalY, 180, finalY);
        doc.text("Vicerrectorado Académico", 150, finalY + 5, null, null, "center");

        // Guardar
        doc.save("Acta_Cierre_" + INFO_CURSO.nombre + ".pdf");
    }
</script>