import re

#########################
# 1. Update taquilla_controlador.php
#########################
with open(r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php", "r", encoding="utf-8") as f:
    ctrl_content = f.read()

# Fix access roles
ctrl_content = re.sub(r'tieneAcceso\(\[4, 5\]\)', 'tieneAcceso([3, 4, 5])', ctrl_content)

# Fix registrar_pago_taquilla block
new_pago_logic = """        case 'registrar_pago_taquilla':
            $id_usuario = (int)$_POST['id_usuario'];
            $id_curso = (int)$_POST['id_curso'];
            $numero_operacion = trim($_POST['numero_operacion']);
            $monto = (float)$_POST['monto'];
            $moneda = trim($_POST['moneda']);
            $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : '';
            $tipo_recepcion = isset($_POST['tipo_recepcion']) ? trim($_POST['tipo_recepcion']) : 'transferencia';
            
            if (empty($id_usuario) || empty($id_curso) || empty($numero_operacion) || $monto <= 0 || empty($moneda)) {
                throw new Exception("Datos de pago incompletos o invlidos.");
            }
            
            // Verificar que el curso pertenece a la sede actual
            $stmtCheck = $pdo->prepare("SELECT 1 FROM cursos.cursos WHERE id_curso = :id_curso AND id_extension = :id_ext");
            $stmtCheck->execute([':id_curso' => $id_curso, ':id_ext' => $id_extension_activa]);
            if (!$stmtCheck->fetch()) {
                throw new Exception("El concepto a pagar no pertenece a su Sede.");
            }
            
            $estado_pago = 'Pendiente';
            $banco_origen = '';
            $id_cuenta_destino = null;

            if ($tipo_recepcion === 'efectivo_punto') {
                $estado_pago = 'Comprobado';
                $banco_origen = 'Taquilla Fsica';
                
                // Obtener la cuenta bancaria CAJA FISICA para esta sede
                $stmtCuenta = $pdo->prepare("SELECT id_cuenta FROM cursos.cuentas_bancarias WHERE id_extension = :id_ext AND banco = 'CAJA FSICA' LIMIT 1");
                $stmtCuenta->execute([':id_ext' => $id_extension_activa]);
                $cuenta_caja = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
                if (!$cuenta_caja) {
                    throw new Exception("Error contable: No existe una CAJA FSICA configurada para esta sede.");
                }
                $id_cuenta_destino = $cuenta_caja['id_cuenta'];
            } else {
                $estado_pago = 'Pendiente';
                $banco_origen = trim($_POST['banco_origen']);
                $id_cuenta_destino = (int)$_POST['id_cuenta_destino'];
                
                if (empty($banco_origen) || empty($id_cuenta_destino)) {
                    throw new Exception("Debe especificar el Banco de Origen y la Cuenta Destino para una transferencia.");
                }
            }
            
            // Registrar pago
            $stmt = $pdo->prepare("INSERT INTO cursos.comprobantes_pago 
                (id_usuario, id_curso, numero_operacion, banco_origen, monto, estado, fecha_pago, observacion, moneda, id_admin_gestor, fecha_gestion, id_cuenta_destino, id_extension) 
                VALUES 
                  (:id_usuario, :id_curso, :numero_operacion, :banco_origen, :monto, :estado, CURRENT_DATE, :observacion, :moneda, :id_admin, CURRENT_TIMESTAMP, :id_cuenta_destino, (SELECT id_extension FROM cursos.cursos WHERE id_curso = :id_curso LIMIT 1))");
            
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':id_curso' => $id_curso,
                ':numero_operacion' => $numero_operacion,
                ':banco_origen' => $banco_origen,
                ':monto' => $monto,
                ':estado' => $estado_pago,
                ':observacion' => $observacion,
                ':moneda' => $moneda,
                ':id_admin' => $id_admin,
                ':id_cuenta_destino' => $id_cuenta_destino
            ]);
            
            echo json_encode(['status' => 'success']);
            break;"""

ctrl_content = re.sub(r"case 'registrar_pago_taquilla':.*?echo json_encode\(\['status' => 'success'\]\);\s*break;", new_pago_logic, ctrl_content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\controllers\taquilla_controlador.php", "w", encoding="utf-8") as f:
    f.write(ctrl_content)

print("taquilla_controlador.php updated")


#########################
# 2. Update taquilla_pagos.php
#########################
with open(r"C:\laragon\www\certificaciones\public\taquilla_pagos.php", "r", encoding="utf-8") as f:
    view_content = f.read()

view_content = re.sub(r'tieneAcceso\(\[4, 5\]\)', 'tieneAcceso([3, 4, 5])', view_content)

php_head_addon = """
$id_extension_admin = $_SESSION['id_extension'];

// Obtener cuentas bancarias de la sede
$db = new DB();
$pdo = $db->getConn();
$stmtCuentas = $pdo->prepare("SELECT id_cuenta, banco, titular, numero_cuenta FROM cursos.cuentas_bancarias WHERE id_extension = :id_ext AND activo = true AND banco != 'CAJA FSICA'");
$stmtCuentas->execute([':id_ext' => $id_extension_admin]);
$cuentas_sede = $stmtCuentas->fetchAll(PDO::FETCH_ASSOC);
"""
view_content = re.sub(r'\$id_extension_admin = \$_SESSION\[\'id_extension\'\];', php_head_addon, view_content)

# Replace form HTML
new_form_html = """            <form id="registrarPagoForm">
                <input type="hidden" id="id_usuario_pago" name="id_usuario">
                <input type="hidden" name="action" value="registrar_pago_taquilla">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Estudiante seleccionado:</label>
                        <input type="text" class="form-control bg-light" id="nombre_estudiante_pago" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Concepto / Curso a pagar:</label>
                        <select class="form-select" name="id_curso" id="select_curso" required>
                            <option value="">Cargando conceptos...</option>
                        </select>
                    </div>
                </div>

                <div class="card bg-light mb-3 border-0">
                    <div class="card-body py-2">
                        <label class="form-label fw-bold mb-2">Tipo de Operacin:</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_recepcion" id="tipo_transferencia" value="transferencia" checked>
                                <label class="form-check-label text-primary fw-bold" for="tipo_transferencia">
                                    <i class="fas fa-university"></i> Recepcin de Transferencia
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_recepcion" id="tipo_efectivo" value="efectivo_punto">
                                <label class="form-check-label text-success fw-bold" for="tipo_efectivo">
                                    <i class="fas fa-money-bill-wave"></i> Efectivo / Punto de Venta
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3" id="row_bancos">
                    <div class="col-md-6">
                        <label class="form-label">Banco de Origen:</label>
                        <input type="text" class="form-control" name="banco_origen" id="banco_origen" placeholder="Ej: Banco de Venezuela, Provincial...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cuenta Destino:</label>
                        <select class="form-select" name="id_cuenta_destino" id="id_cuenta_destino">
                            <option value="">-- Seleccione la Cuenta --</option>
                            <?php foreach($cuentas_sede as $cta): ?>
                                <option value="<?= $cta['id_cuenta'] ?>"><?= htmlspecialchars($cta['banco'] . ' - ' . $cta['numero_cuenta']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Referencia / N Operacin:</label>
                        <input type="text" class="form-control" name="numero_operacion" placeholder="Ej: 123456789" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Monto Recibido:</label>
                        <input type="number" class="form-control" name="monto" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Moneda:</label>
                        <select class="form-select" name="moneda" required>
                            <option value="Bolivares">Bolvares (Bs)</option>
                            <option value="Dolares">Dlares ($)</option>
                            <option value="Pesos">Pesos ($)</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observacin (Opcional):</label>
                    <textarea class="form-control" name="observacion" rows="2" placeholder="Notas sobre el pago..."></textarea>
                </div>
                
                <div class="alert alert-info" id="alert_transferencia">
                    <i class="fas fa-info-circle me-2"></i> <strong>Atencin:</strong> Al registrar una transferencia, el pago quedar en estado <strong>Pendiente</strong> a la espera de conciliacin bancaria.
                </div>
                
                <div class="alert alert-warning" id="alert_efectivo" style="display:none;">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Atencin:</strong> Al registrar Efectivo/Punto, el pago quedar <strong>Aprobado Automticamente</strong> bajo su autora.
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnProcesarPago"><i class="fas fa-save me-2"></i> Registrar Pago</button>
                </div>
            </form>"""

view_content = re.sub(r'<form id="registrarPagoForm">.*?</form>', new_form_html, view_content, flags=re.DOTALL)

# Add JS logic for the toggle
js_logic = """
    // Toggle Payment Mode
    $('input[name="tipo_recepcion"]').change(function() {
        if ($(this).val() === 'transferencia') {
            $('#row_bancos').slideDown();
            $('#banco_origen, #id_cuenta_destino').prop('required', true);
            $('#alert_transferencia').show();
            $('#alert_efectivo').hide();
            $('#btnProcesarPago').removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-save me-2"></i> Registrar Transferencia (Pendiente)');
        } else {
            $('#row_bancos').slideUp();
            $('#banco_origen, #id_cuenta_destino').prop('required', false);
            $('#alert_transferencia').hide();
            $('#alert_efectivo').show();
            $('#btnProcesarPago').removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check-circle me-2"></i> Procesar y Aprobar (Efectivo)');
        }
    });
    $('input[name="tipo_recepcion"]:checked').trigger('change');
"""

view_content = view_content.replace('$(document).ready(function() {', '$(document).ready(function() {\n' + js_logic)

with open(r"C:\laragon\www\certificaciones\public\taquilla_pagos.php", "w", encoding="utf-8") as f:
    f.write(view_content)

print("taquilla_pagos.php updated")
