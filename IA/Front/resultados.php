<?php
include "../../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - IA</title>
    <link rel="icon" type="image/png" href="Img/Icono-A.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            transition: transform 0.2s;
            border: none;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .alert pre {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 10px;
            border-radius: 5px;
        }

        .card-header {
            font-weight: 600;
        }
    </style>

</head>

<body>
    <?php include "../../Front/navbar.php"; ?>
    <div class="container mt-5">
        <!-- Prediction Execution Button -->
        <form method="post" class="text-center mb-5">
            <button type="submit" name="ejecutar_prediccion" class="btn btn-success btn-lg">
                <i class="fas fa-sync-alt me-2"></i> Ejecutar Predicción
            </button>
        </form>

        <div class="row g-4">
            <!-- Incident Prediction Column -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i> Predicción de Incidentes por Mes
                        </h4>
                    </div>

                    <!-- Text Results -->
                    <div class="card-body">
                        <?php

                        $descripcion_file = 'resultados_ia/descripcion_prediccion.txt';
                        if (file_exists($descripcion_file)) {
                            echo "<div class='alert alert-info mb-4'>";
                            echo "<h5 class='alert-heading'><i class='fas fa-info-circle me-2'></i>Resultados del Análisis</h5>";
                            echo "<hr>";
                            echo "<pre class='mb-0' style='white-space: pre-wrap; font-size: 0.9rem;'>";
                            echo htmlspecialchars(file_get_contents($descripcion_file));
                            echo "</pre>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Prediction Image -->
                    <div class="text-center p-3 border-top" onclick="openImageModal('resultados_ia/prediccion_incidentes.png', 'Predicción de Incidentes')">
                        <img src="resultados_ia/prediccion_incidentes.png"
                            alt="Gráfica de predicción"
                            class="img-fluid rounded shadow-sm"
                            style="cursor: pointer; max-height: 300px;">
                        <p class="text-muted mt-2">Haz clic para ampliar la imagen</p>
                    </div>
                </div>
            </div>

            <!-- Categories Prediction Column -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i> Predicción de Categorías más Solicitadas
                        </h4>
                    </div>

                    <!-- Text Results -->
                    <div class="card-body">

                        <?php
                        if (isset($_POST['ejecutar_prediccion'])) {
                            // No need to execute again, we already did it in the first column
                            // This prevents running the prediction twice on form submit
                        }

                        $descripcion_file_categorias = 'resultados_ia/descripcion_categorias.txt';
                        if (file_exists($descripcion_file_categorias)) {
                            echo "<div class='alert alert-info mb-4'>";
                            echo "<h5 class='alert-heading'><i class='fas fa-info-circle me-2'></i>Resultados por Categoría</h5>";
                            echo "<hr>";
                            echo "<pre class='mb-0' style='white-space: pre-wrap; font-size: 0.9rem;'>";
                            echo htmlspecialchars(file_get_contents($descripcion_file_categorias));
                            echo "</pre>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Categories Image -->
                    <div class="text-center p-3 border-top" onclick="openImageModal('resultados_ia/prediccion_categorias.png', 'Predicción de Categorías')">
                        <img src="resultados_ia/prediccion_categorias.png"
                            alt="Gráfica de categorias"
                            class="img-fluid rounded shadow-sm"
                            style="cursor: pointer; max-height: 300px;">
                        <p class="text-muted mt-2">Haz clic para ampliar la imagen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php
        $ruta_absoluta = "../Back/";
        if (isset($_POST['ejecutar_prediccion'])) {
            $comando = "python ../Back/prediccion.py \"$ruta_absoluta\"";

            $output = shell_exec($comando);
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];

            $process = proc_open($comando, $descriptorspec, $pipes);

            if (is_resource($process)) {
                // Cierra la entrada
                fclose($pipes[0]);

                echo "<pre>";
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line !== false) {
                        echo htmlspecialchars($line);
                        ob_flush();
                        flush(); // 👈 ¡Para que lo veas en tiempo real!
                    }
                }
                fclose($pipes[1]);

                // También mostramos errores (stderr)
                while (!feof($pipes[2])) {
                    $line = fgets($pipes[2]);
                    if ($line !== false) {
                        echo "<span style='color:red;'>" . htmlspecialchars($line) . "</span>";
                        ob_flush();
                        flush();
                    }
                }
                fclose($pipes[2]);

                proc_close($process);
                echo "</pre>";
            }


            echo "<div class='alert alert-success mb-4'>";
            echo "<i class='fas fa-check-circle me-2'></i> Análisis ejecutado correctamente.";
            echo "</div>";
        }
        ?>
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