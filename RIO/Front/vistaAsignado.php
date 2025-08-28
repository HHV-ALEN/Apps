<?php
session_start();
include("../../Back/config/config.php"); // --> Conexión con Base de datos
/// Habilitar la muestra de errores:
require '../../vendor/autoload.php';
$conn = connectMySQLi();

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);


$Nombre_Evaluado = $_SESSION['Name'];

$nombreArchivo = 'RIO_' . str_replace(' ', '_', $Nombre_Evaluado) . '.xlsx';
$rutaArchivo = '../Archivos/' . $nombreArchivo;
$archivoExiste = file_exists($rutaArchivo);
/*
if ($archivoExiste) {
    echo "<br> Existe el Archivo en: " . $rutaArchivo;
} else {
    echo "<br> No encontrado";
}
*/

function extraerDatosEvaluacion($rutaArchivo)
{
    //echo "<br> Archivo Procesado: " . $rutaArchivo;
    try {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();

        $datos = [];
        $filaInicio = 9;
        $ultimaResponsabilidad = '';

        for ($row = $filaInicio; $row <= $sheet->getHighestRow(); $row++) {
            $responsabilidad = $sheet->getCell('A' . $row)->getValue();


            if ($responsabilidad != '') {
                $datos[] = [
                    'responsabilidad' => $responsabilidad,
                ];
            }
        }

        return $datos;
    } catch (Exception $e) {
        error_log("Error al leer archivo Excel: " . $e->getMessage());
        return [];
    }
}

$datosEvaluacion = [];
if ($archivoExiste) {
    $datosEvaluacion = extraerDatosEvaluacion($rutaArchivo);
}

$periodo_actual = date("Y-m");
$periodo_anterior_1 = date("Y-m", strtotime("-1 month"));
$periodo_anterior_2 = date("Y-m", strtotime("-2 month"));

$periodo_consulta = $_GET['periodo'] ?? $periodo_actual;

// Array con los períodos a mostrar
$periodos = [
    $periodo_actual,
    $periodo_anterior_1,
    $periodo_anterior_2
];

/// Verificar Si ya existen Evidencias en el periodo seleccionado:
$periodo_seleccionado = $_GET['periodo'] ?? $periodos[0]; // Default al primero si no hay get
$InputsAbierto = True;
//echo "<br> Periodo Seleccionado: " . $periodo_seleccionado;

$query_EvidenciasRegistradas = "SELECT * FROM rio_evidencias WHERE Responsable = '$Nombre_Evaluado' AND Periodo = '$periodo_seleccionado'";
// 1. Ejecutar el query
$result = mysqli_query($conn, $query_EvidenciasRegistradas);

// 2. Validar si hay registros (en una sola línea)
$tiene_evidencias = ($result && mysqli_num_rows($result) > 0);
// 3. Liberar memoria (opcional pero recomendado)
if ($result) mysqli_free_result($result);
Uso:
if ($tiene_evidencias) {
    ///echo "✅ tiene evidencias registradas.";
    $InputsAbierto = TRUE;
} else {
    //echo "❌ Nel, no hay evidencias pa' este período.";
    $InputsAbierto = FALSE;
}

if (empty($datosEvaluacion)) {
    //echo "<div class='alert alert-warning text-center'>No hay usuarios a cargo.</div>";
    /// Regresar a la página anterior con un mensaje de "No tienes usuarios a cargo"

    $_SESSION['mensaje'] = "No existe un Archivo RIO Registrado a tu nombre";
    $_SESSION['tipo_mensaje'] = "warning";
    echo "<script>setTimeout(function(){ window.history.back(); }, 1);</script>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Evaluación RIO</title>
</head>

<body>
    <?php include "../../Front/navbar.php"; ?>

    <?php
    if (empty($datosEvaluacion)) {
        echo "<div class='alert alert-warning text-center'>No hay datos de evaluación disponibles. Es necesario cargar el Archivo RIO</div>";
    } else {
    ?>

        <div class="container mt-4"> <!-- Contenedor principal centrado -->
            <div class="row justify-content-center"> <!-- Centrado horizontal -->
                <div class="col-lg-8 col-md-10"> <!-- Tamaño responsive -->
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0 text-center">Subir evidencias por responsabilidad</h4>
                        </div>
                        <div class="card-body">
                            <form action="../Back/procesar_evidencias.php" method="POST" enctype="multipart/form-data">

                                <!-- Selector de período con indicación -->
                                <div class="d-flex align-items-center mb-3 position-relative">
                                    <select id="selectPeriodo" class="form-select d-inline-block w-auto me-2" name="Periodo" onchange="cambiarPeriodo(this)">
                                        <?php foreach ($periodos as $periodo): ?>
                                            <option value="<?= $periodo ?>" <?= ($periodo == $periodo_consulta) ? 'selected' : '' ?>>
                                                <?= $periodo ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Indicación con flecha y mensaje -->
                                    <div class="periodo-indicacion ms-2">
                                        <span class="badge bg-info d-flex align-items-center">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Selecciona el período
                                        </span>
                                    </div>
                                </div>

                                <?php
                                if (!$InputsAbierto) {
                                ?>
                                    <br><br>
                                    <?php foreach ($datosEvaluacion as $index => $item): ?>
                                        <div class="card mb-3 border-success"> <!-- Card por responsabilidad -->
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0 text-primary">
                                                    <i class="fas fa-folder me-2"></i>
                                                    <?= htmlspecialchars($item['responsabilidad']) ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Evidencias:</label>
                                                    <input
                                                        type="file"
                                                        name="evidencias[<?= $index ?>][]"
                                                        class="form-control"
                                                        multiple
                                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.xls,.ppt,.zip">
                                                    <small class="text-muted">Formatos: imágenes, PDF, Office, ZIP (Máx. 5MB)</small>
                                                </div>
                                                <input type="hidden" name="responsabilidades[<?= $index ?>]" value="<?= $item['responsabilidad'] ?>">
                                                <div id="preview-<?= $index ?>" class="mt-2"></div> <!-- Preview aquí -->
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-success btn-lg" id="btnGuardar" disabled>
                                            <i class="fas fa-save me-2"></i>Guardar todo
                                        </button>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <br>
                                    <hr>

                                    <!-- Sección de Evidencias Registradas -->
                                    <div class="evidencias-section">
                                        <h3 class="text-center mb-4">Evidencias Registradas en este periodo</h3>

                                        <div class="row justify-content-center">
                                            <!-- Botón Descargar Evidencias -->
                                            <div class="col-md-3 mb-3">
                                                <a href="../Back/descargarEvidencias.php?Evaluado=<?php echo $Nombre_Evaluado; ?>&Periodo=<?php echo $periodo_seleccionado; ?>"
                                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-download me-2"></i>Descargar Evidencias
                                                </a>
                                            </div>

                                            <!-- Botón Eliminar Evidencias -->
                                            <div class="col-md-3 mb-3">
                                                <button class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-trash-alt me-2"></i>Eliminar Evidencias
                                                </button>
                                            </div>

                                            <?php
                                            // Consulta para rio_objetivos_individuales
                                            $Consulta_rio_objetivos_individuales = "SELECT COUNT(*) AS total FROM rio_objetivos_individuales 
                                               WHERE Nombre_Evaluado = '$Nombre_Evaluado' 
                                               AND Periodo = '$periodo_seleccionado'";
                                            $resultado = $conn->query($Consulta_rio_objetivos_individuales);
                                            $row = $resultado->fetch_assoc();

                                            if ($row['total'] > 0) {
                                                // Mostrar el botón de resultados
                                            ?>
                                                <div class="col-md-3 mb-3">
                                                    <a href="resultados.php?NombreEvaluado=<?php echo $Nombre_Evaluado; ?>&Periodo=<?php echo $periodo_seleccionado; ?>"
                                                        class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-chart-bar me-2"></i>Resultados (<?php echo $periodo_seleccionado; ?>)
                                                    </a>
                                                </div>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="col-md-3 mb-3">
                                                    <div class="d-flex align-items-center justify-content-center text-muted h-100">
                                                        <i class="fas fa-chart-bar me-2"></i>No Resultados Registrados
                                                    </div>
                                                </div>
                                            <?php
                                            }

                                            // Consulta para verificar si existe feedback
                                            $sql_feedback = "SELECT COUNT(*) AS total FROM rio_feedback 
                        WHERE Nombre_Encuestado = '$Nombre_Evaluado' 
                        AND Periodo = '$periodo_seleccionado'";
                                            $result_feedback = $conn->query($sql_feedback);
                                            $row_feedback = $result_feedback->fetch_assoc();
                                            ?>

                                            <!-- Botón Feedback -->
                                            <div class="col-md-3 mb-3">
                                                <?php if ($row_feedback['total'] > 0): ?>
                                                    <a href="resultadosFeedback.php?NombreEvaluado=<?php echo urlencode($Nombre_Evaluado); ?>&Periodo=<?php echo urlencode($periodo_seleccionado); ?>"
                                                        class="btn btn-info w-100 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-comments me-2"></i>Ver Feedback
                                                    </a>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center justify-content-center text-muted h-100">
                                                        <i class="fas fa-comments me-2"></i>No Feedback Registrado
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                    <?php
                                }
                    ?>

                    </form>
                    </div>
                </div>
            </div>
        </div>

        </div>
        <br><br>

    <?php
    }
    ?>

    <script>
        function cambiarPeriodo(select) {
            const periodo = select.value;
            const url = new URL(window.location.href);
            url.searchParams.set('periodo', periodo);
            window.location.href = url.toString();
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form[action='../Back/procesar_evidencias.php']");
            const btnGuardar = document.getElementById("btnGuardar");
            const fileInputs = form.querySelectorAll("input[type='file']");

            function validarArchivos() {
                let completos = true;

                fileInputs.forEach(input => {
                    if (input.files.length === 0) {
                        completos = false;
                    }
                });

                btnGuardar.disabled = !completos;
            }

            // Cada vez que cambie un input file, validamos
            fileInputs.forEach(input => {
                input.addEventListener("change", validarArchivos);
            });

            // Validamos al inicio por si acaso
            validarArchivos();
        });
    </script>




</body>

</html>