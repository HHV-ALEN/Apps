<?php
include "../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>IA de Incidencias - AlenApps</title>
    <link rel="stylesheet" href="Front/css/IA.css">
    <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include "../Front/navbar.php"; ?>
    <br>
    

    <div class="container">
        <a href="index.php" class="btn btn-outline-dark">Regresar al Indice</a>
        <!-- Button to view results -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Visualización de Resultados Actuales</h2>
            <button class="btn btn-primary btn-lg" onclick="window.location.href='Front/resultados.php'">
                <i class="fas fa-chart-bar me-2"></i>Ver Resultados
            </button>
        </div>

        <div class="row g-4">
            <!-- Card 1: Top Incidents -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Top Incidentes</h5>
                    </div>

                    <div class="card-img-container p-3" onclick="openImageModal('Back/resultados_ia/top_incidentes.png', 'Top Incidents')">
                        <img src="Back/resultados_ia/top_incidentes.png" class="card-img-top img-fluid rounded" alt="Top incidentes">
                    </div>

                    <div class="card-footer bg-light">
                        <small class="text-muted">Tipos de Incidentes más Frecuentes</small>
                    </div>
                </div>
            </div>

            <!-- Card 2: Priorities -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-flag me-2"></i>Prioridades</h5>
                    </div>
                    <div class="card-img-container p-3" onclick="openImageModal('Back/resultados_ia/prioridades.png', 'Prioridades')">
                        <img src="Back/resultados_ia/prioridades.png" class="card-img-top img-fluid rounded" alt="Prioridades">
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">Distribución de Prioridades</small>
                    </div>
                </div>
            </div>

            <!-- Card 3: Status -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Estados</h5>
                    </div>
                    <div class="card-img-container p-3" onclick="openImageModal('Back/resultados_ia/estatus_pie.png', 'Top Incidents')">
                        <img src="Back/resultados_ia/estatus_pie.png" class="card-img-top img-fluid rounded" alt="Estatus">
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">Vista de estados de tickets</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    
    <div class="container">
        <div class="card text-center">
            <h2>Subir archivo de incidencias</h2>
            <p>Por favor, selecciona un archivo <strong>.xlsx</strong> o <strong>.csv</strong> con el histórico de incidencias.</p>
            <form action="Back/procesamiento.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="archivo_incidencias" class="form-control" accept=".xlsx, .csv" required>
                </div>
                <br>
                <button type="submit" class="btn btn-primary btn-block">Cargar y analizar</button>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImageTitle">Pre-Vista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Enlarged view">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <br>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function openImageModal(imageSrc, imageTitle) {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalImageTitle').textContent = imageTitle;
            modal.show();
        }
    </script>
</body>

</html>