import re

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "r", encoding="utf-8") as f:
    content = f.read()

# 1. Main View Header & Button
content = content.replace('<h6 class="m-0 font-weight-bold text-primary">Materias de: <?= htmlspecialchars($nombre_curso) ?></h6>',
                          '<h6 class="m-0 font-weight-bold text-primary"><?= $_SESSION[\'es_academico\'] ? \'Materias de:\' : \'Términos de:\' ?> <?= htmlspecialchars($nombre_curso) ?></h6>')

content = content.replace('<button class="btn btn-success btn-sm" onclick="abrirModalMateria()"><i class="fas fa-plus"></i> Nueva\n                Materia</button>',
                          '<button class="btn btn-success btn-sm" onclick="abrirModalMateria()"><i class="fas fa-plus"></i> <?= $_SESSION[\'es_academico\'] ? \'Nueva Materia\' : \'Nuevo Término\' ?></button>')

# 2. Main View Table Headers
content = content.replace('<th>Materia</th>\n                                    <th>Duración (Texto)</th>\n                                    <th>Modalidad</th>\n                                    <th>Facilitador</th>',
                          '<th><?= $_SESSION[\'es_academico\'] ? \'Materia\' : \'Término\' ?></th>\n                                    <?php if ($_SESSION[\'es_academico\']): ?>\n                                    <th>Duración (Texto)</th>\n                                    <th>Modalidad</th>\n                                    <th>Facilitador</th>\n                                    <?php endif; ?>')

# 3. Main View Table Body
content = content.replace('<td><?= htmlspecialchars($mat[\'duracion_bimestres\']) ?></td>\n                                        <td><?= htmlspecialchars($mat[\'modalidad\']) ?></td>\n                                        <td><?= htmlspecialchars($mat[\'nombre_docente\'] . \' \' . $mat[\'apellido_docente\']) ?></td>',
                          '<?php if ($_SESSION[\'es_academico\']): ?>\n                                        <td><?= htmlspecialchars($mat[\'duracion_bimestres\']) ?></td>\n                                        <td><?= htmlspecialchars($mat[\'modalidad\']) ?></td>\n                                        <td><?= htmlspecialchars($mat[\'nombre_docente\'] . \' \' . $mat[\'apellido_docente\']) ?></td>\n                                        <?php endif; ?>')

# 4. Modal Header
content = content.replace('<h5 class="modal-title" id="modalMateriaLabel">Gestión de Materia</h5>',
                          '<h5 class="modal-title" id="modalMateriaLabel"><?= $_SESSION[\'es_academico\'] ? \'Gestión de Materia\' : \'Gestión de Término\' ?></h5>')

# 5. Modal Label: Nombre
content = content.replace('<label>Nombre Materia</label>',
                          '<label><?= $_SESSION[\'es_academico\'] ? \'Nombre Materia\' : \'Descripción del Término (Ej. Arancel Término 1)\' ?></label>')

# 6. Modal Row: Duración & Horas
content = content.replace('<div class="row">\n                        <div class="col-6 mb-3">\n                            <label>Duración (Texto)</label>',
                          '<?php if ($_SESSION[\'es_academico\']): ?>\n                    <div class="row">\n                        <div class="col-6 mb-3">\n                            <label>Duración (Texto)</label>')

content = content.replace('<label>Horas</label>\n                            <input type="number" class="form-control" name="total_horas" id="total_horas" required>\n                        </div>\n                    </div>',
                          '<label>Horas</label>\n                            <input type="number" class="form-control" name="total_horas" id="total_horas" required>\n                        </div>\n                    </div>\n                    <?php endif; ?>')

# 7. Modal Modalidad, Temario, Facilitador
content = content.replace('<div class="mb-3">\n                        <label>Modalidad</label>',
                          '<?php if ($_SESSION[\'es_academico\']): ?>\n                    <div class="mb-3">\n                        <label>Modalidad</label>')

content = content.replace('<button class="btn btn-outline-primary" type="button" onclick="abrirBuscadorDocente()"><i\n                                    class="fas fa-search"></i></button>\n                        </div>\n                    </div>',
                          '<button class="btn btn-outline-primary" type="button" onclick="abrirBuscadorDocente()"><i\n                                    class="fas fa-search"></i></button>\n                        </div>\n                    </div>\n                    <?php endif; ?>')

with open(r"C:\laragon\www\certificaciones\views\gestionar_materias.php", "w", encoding="utf-8") as f:
    f.write(content)
