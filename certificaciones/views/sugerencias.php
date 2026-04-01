<?php
// Incluir el archivo model.php en config
include '../config/model.php';

// Crear una instancia de la clase DB
$db = new DB();

// Check role
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 4) {
    echo '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-lock me-2"></i>Acceso denegado. Se requiere nivel de administrador.</div>';
    exit;
}

// Fetch sugerencias
try {
    // Intentar asumiendo la típica estructura de PostgreSQL con id y fecha serializada autogenerada
    $stmt = $db->prepare("SELECT * FROM cursos.buzon_sugerencias ORDER BY id_sugerencia DESC");
    $stmt->execute();
    $sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la columna id_sugerencia no existe, fallback estándar
    try {
        $stmt = $db->prepare("SELECT * FROM cursos.buzon_sugerencias");
        $stmt->execute();
        $sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Intentar revertir el arreglo manual
        $sugerencias = array_reverse($sugerencias);
    } catch (PDOException $e2) {
        $sugerencias = [];
    }
}
?>

<div class="container-fluid mt-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-inbox me-2 text-primary"></i>Buzón de Sugerencias</h1>
        <p class="mb-0 text-muted">Revisión de la retroalimentación enviada por la comunidad</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>Sugerencia eliminada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-lg border-0 rounded-4 mb-5">
        <div
            class="card-header bg-white py-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center rounded-top-4">
            <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0"><i class="fas fa-comments me-2"></i>Comentarios
                Recibidos (<span id="total-sugs"><?= count($sugerencias) ?></span>)</h6>

            <!-- Barra de Búsqueda -->
            <?php if (!empty($sugerencias)): ?>
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-light border-0" id="search-icon"><i
                            class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control bg-light border-0"
                        placeholder="Buscar por nombre, correo, cédula..." aria-label="Buscar"
                        aria-describedby="search-icon">
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body bg-light p-4">
            <?php if (empty($sugerencias)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3 opacity-50"></i>
                    <h5 class="text-gray-600 font-weight-bold">Bandeja Vacía</h5>
                    <p class="text-muted">Aún no se han recibido sugerencias en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="row g-4" id="suggestionsContainer">
                    <?php foreach ($sugerencias as $index => $sug): ?>
                        <div class="col-md-6 col-xl-4 suggestion-card" data-index="<?= $index ?>"
                            data-search="<?= strtolower(htmlspecialchars($sug['nombre'] . ' ' . $sug['apellido'] . ' ' . $sug['cedula'] . ' ' . $sug['correo'] . ' ' . $sug['sugerencia'])) ?>">
                            <div class="card h-100 border-0 shadow-sm rounded-4 custom-hover-btn">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                                        <div>
                                            <h6 class="card-title fw-bold text-dark mb-1">
                                                <?= htmlspecialchars($sug['nombre'] . ' ' . $sug['apellido']) ?>
                                            </h6>
                                            <span class="badge bg-primary rounded-pill mb-1"><i
                                                    class="fas fa-id-card me-1"></i><?= htmlspecialchars($sug['cedula']) ?></span>
                                            <small class="text-muted d-block"><i
                                                    class="fas fa-envelope me-2"></i><?= htmlspecialchars($sug['correo']) ?></small>
                                        </div>
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-light rounded-circle p-3 text-primary d-flex align-items-center justify-content-center shadow-sm mb-2"
                                                style="width: 45px; height: 45px;">
                                                <i class="fas fa-quote-right"></i>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm btn-delete-sugerencia"
                                                data-id="<?= isset($sug['id_sugerencia']) ? $sug['id_sugerencia'] : $sug['id'] ?>"
                                                style="width: 35px; height: 35px;" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="position-relative">
                                        <i class="fas fa-quote-left position-absolute text-muted opacity-25"
                                            style="font-size: 2rem; top: -10px; left: -10px;"></i>
                                        <p class="card-text text-secondary mb-0 bg-light p-3 rounded-3 position-relative z-1"
                                            style="font-style: italic; min-height: 80px;">
                                            <?= nl2br(htmlspecialchars($sug['sugerencia'])) ?>
                                        </p>
                                    </div>
                                    <?php if (isset($sug['fecha_creacion'])): ?>
                                        <div class="mt-3 text-end">
                                            <small class="text-muted"><i
                                                    class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($sug['fecha_creacion'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación Frontend -->
                <nav class="mt-5" aria-label="Navegación de sugerencias" id="paginationNav">
                    <ul class="pagination justify-content-center" id="paginationList">
                        <!-- Generado por JS -->
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($sugerencias)): ?>
    <script>
        (function () {
            const searchInput = document.getElementById('searchInput');
            const cards = Array.from(document.querySelectorAll('.suggestion-card'));
            const paginationList = document.getElementById('paginationList');
            const totalLabel = document.getElementById('total-sugs');

            let itemsPerPage = 6;
            let currentPage = 1;
            let filteredCards = [...cards];

            function renderCards() {
                // Ocultar todos primero
                cards.forEach(c => c.style.display = 'none');

                // Calcular inicio y fin
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;

                // Mostrar solo los del rango actual
                const cardsToShow = filteredCards.slice(start, end);
                cardsToShow.forEach(c => c.style.display = 'block');

                renderPagination();
            }

            function renderPagination() {
                paginationList.innerHTML = '';
                const totalPages = Math.ceil(filteredCards.length / itemsPerPage);

                if (totalPages <= 1) return; // No pagination needed

                // Botón Anterior
                let prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link shadow-sm border-0" href="#" data-page="${currentPage - 1}">Anterior</a>`;
                paginationList.appendChild(prevLi);

                // Numeros
                for (let i = 1; i <= totalPages; i++) {
                    let li = document.createElement('li');
                    li.className = `page-item ${currentPage === i ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link shadow-sm border-0" href="#" data-page="${i}">${i}</a>`;
                    paginationList.appendChild(li);
                }

                // Botón Siguiente
                let nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link shadow-sm border-0" href="#" data-page="${currentPage + 1}">Siguiente</a>`;
                paginationList.appendChild(nextLi);
            }

            // Escuchar clicks de paginacion
            paginationList.addEventListener('click', function (e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const newPage = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(newPage)) {
                        currentPage = newPage;
                        renderCards();
                    }
                }
            });

            // Escuchar clicks en los botones de eliminar sugerencias (con delegación de eventos)
            document.getElementById('suggestionsContainer').addEventListener('click', function(e) {
                // Buscar si el click fue en el botón o en el ícono dentro del botón
                const button = e.target.closest('.btn-delete-sugerencia');
                
                if (button) {
                    if (confirm('¿Estás seguro que deseas eliminar esta sugerencia? Esta acción no se puede deshacer.')) {
                        const id = button.getAttribute('data-id');
                        
                        $.ajax({
                            url: '../controllers/SugerenciaController.php',
                            type: 'POST',
                            data: {
                                action: 'delete_ajax',
                                id: id
                            },
                            dataType: 'json',
                            success: function(response) {
                                if(response.success) {
                                    alert('Sugerencia eliminada correctamente.');
                                    // Recargar a través del SPA
                                    loadPage('../views/sugerencias.php');
                                } else {
                                    alert('Error al eliminar: ' + response.error);
                                }
                            },
                            error: function() {
                                alert('Error de conexión al intentar eliminar la sugerencia. Verifica en la red.');
                            }
                        });
                    }
                }
            });

            // Escuchar búsqueda en vivo
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase();

                filteredCards = cards.filter(card => {
                    return card.getAttribute('data-search').includes(query);
                });

                totalLabel.textContent = filteredCards.length;
                currentPage = 1; // Volver a pag 1 al buscar
                renderCards();
            });

            // Inicializar
            renderCards();
        })();
    </script>
<?php endif; ?>
