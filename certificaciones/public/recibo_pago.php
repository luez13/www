<?php
session_start();
require_once '../config/model.php';
require_once '../controllers/autenticacion.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado. Inicie sesión para ver su recibo.");
}

$id_comprobante = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_comprobante <= 0) {
    die("ID de comprobante inválido.");
}

$db = new DB();
$pdo = $db->getConn();

// Query for the payment details
$sql = "
SELECT cp.*, 
       u.nombre as nombre_estudiante, u.cedula,
       c.nombre_curso, 
       cb.banco as banco_destino, cb.numero_cuenta as cuenta_destino,
       adm.nombre as nombre_admin
FROM cursos.comprobantes_pago cp
JOIN cursos.usuarios u ON cp.id_usuario = u.id
JOIN cursos.cursos c ON cp.id_curso = c.id_curso
LEFT JOIN cursos.cuentas_bancarias cb ON cp.id_cuenta_destino = cb.id_cuenta
LEFT JOIN cursos.usuarios adm ON cp.id_admin_gestor = adm.id
WHERE cp.id_comprobante = :id_comprobante
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_comprobante' => $id_comprobante]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    die("Comprobante no encontrado.");
}

// Verify ownership (Admin/Analysts can view any)
if ($pago['id_usuario'] != $_SESSION['user_id'] && !tieneAcceso([3, 4, 5, 6])) {
    die("No tiene permiso para ver este comprobante.");
}

// Only allow printable receipt for processed payments
if ($pago['estado'] !== 'Comprobado' && $pago['estado'] !== 'Aprobado') {
    die("El comprobante de pago aún no ha sido procesado o fue rechazado.");
}

// Formateo de datos
$fechaEmision = date('d/m/Y H:i A');
$fechaPago = date('d/m/Y', strtotime($pago['fecha_pago']));
$fechaProcesado = $pago['fecha_gestion'] ? date('d/m/Y H:i A', strtotime($pago['fecha_gestion'])) : 'N/A';
$monedaSimbolo = ($pago['moneda'] === 'Dolares' || $pago['moneda'] === 'Pesos') ? '$' : 'Bs.';
$montoTotal = number_format($pago['monto'], 2);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-top: 5px solid #4e73df;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #eaecf4;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-logo {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
        }
        .header-info {
            text-align: right;
            color: #858796;
            font-size: 14px;
        }
        .receipt-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #5a5c69;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 10px;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e3e6f0;
        }
        .info-label {
            font-weight: bold;
            font-size: 12px;
            color: #858796;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }
        .info-value {
            font-size: 15px;
            color: #3a3b45;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .payment-table th {
            background-color: #4e73df;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .payment-table td {
            padding: 12px;
            border-bottom: 1px solid #eaecf4;
            background: #f8f9fc;
        }
        .payment-table .amount {
            font-weight: bold;
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eaecf4;
            color: #858796;
            font-size: 13px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(28, 200, 138, 0.05);
            font-weight: bold;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
        }
        .btn-print {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 15px;
            background: #4e73df;
            color: #fff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .btn-print:hover {
            background: #2e59d9;
        }
        @media print {
            body { background: #fff; }
            .receipt-container { box-shadow: none; border: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Recibo</button>

    <div class="receipt-container" style="position: relative;">
        <div class="watermark">PROCESADO</div>
        
        <div class="header">
            <div class="header-logo">
                Sistema de Pagos
                <div style="font-size: 14px; font-weight: normal; color: #858796; margin-top: 5px;">Recibo Administrativo</div>
            </div>
            <div class="header-info">
                
                <strong>Fecha de Emisión:</strong> <?= $fechaEmision ?><br>
                <strong>Estado:</strong> <span style="color: #1cc88a; font-weight: bold;">Procesado Exitosamente</span>
            </div>
        </div>

        <div class="receipt-title">COMPROBANTE DE PAGO</div>

        <div class="info-grid">
            <div class="info-box">
                <span class="info-label">Datos del Estudiante</span>
                <div class="info-value">
                    <strong><?= htmlspecialchars($pago['nombre_estudiante']) ?></strong><br>
                    Cédula / RIF: <?= htmlspecialchars($pago['cedula']) ?>
                </div>
            </div>
            <div class="info-box">
                <span class="info-label">Concepto a Pagar</span>
                <div class="info-value">
                    <strong><?= htmlspecialchars($pago['nombre_curso']) ?></strong>
                </div>
            </div>
        </div>

        <div class="section-title">Detalles de la Operación</div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Descripción de la Operación</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Fecha del Pago:</strong> <?= $fechaPago ?><br>
                        <strong>Método:</strong> <?= htmlspecialchars($pago['banco_origen']) ?><br>
                        <strong>Referencia:</strong> <?= htmlspecialchars($pago['numero_operacion']) ?><br>
                        <?php if ($pago['banco_destino']): ?>
                        <strong>Destino:</strong> <?= htmlspecialchars($pago['banco_destino']) ?> (<?= htmlspecialchars(substr($pago['cuenta_destino'], -4)) ?>)
                        <?php endif; ?>
                    </td>
                    <td class="amount">
                        <?= $monedaSimbolo ?> <?= $montoTotal ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="info-grid">
            <div class="info-box" style="grid-column: span 2;">
                <span class="info-label">Información de Conciliación</span>
                <div class="info-value" style="font-size: 13px;">
                    Este pago fue procesado el <strong><?= $fechaProcesado ?></strong> por el departamento administrativo.<br>
                    <?php if (!empty($pago['observacion'])): ?>
                    <strong>Observaciones:</strong> <?= htmlspecialchars($pago['observacion']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer">
            Este documento es un comprobante de control interno administrativo y no constituye una factura fiscal.<br>
            Cualquier duda o inconveniente comuníquese con el departamento de administración académica.
        </div>
    </div>
</body>
</html>
