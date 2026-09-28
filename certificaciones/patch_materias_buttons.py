import re

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content = f.read()

# For Desktop View
old_desktop = """<button class="btn btn-primary btn-sm text-white" 
                                                    onclick="abrirRecuperativo(<?= $mat['id_materia_bimestre'] ?>, '<?= addslashes(htmlspecialchars($mat['nombre_materia'])) ?>')" 
                                                    style="background-color: #fd7e14; border-color: #fd7e14;" title="Evaluacin Recuperativa">
                                                    <i class="fas fa-life-ring"></i>
                                                </button>
                                                <button class="btn btn-info btn-sm" title="Acta de Cierre (Regular)"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Regular')">
                                                    <i class="fas fa-file-contract"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm" style="background-color: #e83e8c; border-color: #e83e8c;" title="Acta de Recuperativo"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Recuperativo')">
                                                    <i class="fas fa-file-invoice"></i>
                                                </button>
                                                <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"
                                                    target="_blank" class="btn btn-success btn-sm" title="Constancia de Docencia">
                                                    <i class="fas fa-certificate"></i>
                                                </a>"""

# Since the file has some weird UTF-8 characters for Evaluation, let's use Regex to find the block
desktop_pattern = r'<button class="btn btn-primary btn-sm text-white".*?Constancia de Docencia">.*?</a>'
desktop_replacement = """<?php if ($_SESSION['es_academico']): ?>
                                                <button class="btn btn-primary btn-sm text-white" 
                                                    onclick="abrirRecuperativo(<?= $mat['id_materia_bimestre'] ?>, '<?= addslashes(htmlspecialchars($mat['nombre_materia'])) ?>')" 
                                                    style="background-color: #fd7e14; border-color: #fd7e14;" title="Evaluacin Recuperativa">
                                                    <i class="fas fa-life-ring"></i>
                                                </button>
                                                <button class="btn btn-info btn-sm" title="Acta de Cierre (Regular)"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Regular')">
                                                    <i class="fas fa-file-contract"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm" style="background-color: #e83e8c; border-color: #e83e8c;" title="Acta de Recuperativo"
                                                    onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Recuperativo')">
                                                    <i class="fas fa-file-invoice"></i>
                                                </button>
                                                <a href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"
                                                    target="_blank" class="btn btn-success btn-sm" title="Constancia de Docencia">
                                                    <i class="fas fa-certificate"></i>
                                                </a>
                                                <?php endif; ?>"""
content = re.sub(desktop_pattern, desktop_replacement, content, flags=re.DOTALL)


# For Mobile View
mobile_pattern = r'<li><a class="dropdown-item py-2" href="#" onclick="abrirRecuperativo.*?Constancia</a></li>'
mobile_replacement = """<?php if ($_SESSION['es_academico']): ?>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirRecuperativo(<?= $mat['id_materia_bimestre'] ?>, '<?= addslashes(htmlspecialchars($mat['nombre_materia'])) ?>')"><i class="fas fa-life-ring me-2" style="color:#fd7e14;"></i> Ev. Recuperativa</a></li>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Regular')"><i class="fas fa-file-contract me-2 text-info"></i> Acta Regular</a></li>
                                                    <li><a class="dropdown-item py-2" href="#" onclick="abrirModalActaMateria(<?= $mat['id_materia_bimestre'] ?>, 'Recuperativo')"><i class="fas fa-file-invoice me-2" style="color:#e83e8c;"></i> Acta Recuperativa</a></li>
                                                    <li><a class="dropdown-item py-2" target="_blank" href="../controllers/generar_constancia_facilitador.php?id_materia=<?= $mat['id_materia_bimestre'] ?>"><i class="fas fa-certificate me-2 text-success"></i> Constancia</a></li>
                                                    <?php endif; ?>"""
content = re.sub(mobile_pattern, mobile_replacement, content, flags=re.DOTALL)

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content)
