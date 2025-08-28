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

$NombreEvaluado = $_GET['NombreEvaluado'];
$Periodo = $_GET['Periodo'];
$Resultados = [];
$ResultadosGenerales = [];


//echo "<br> Nombre Recibido: " . $NombreEvaluado . "<br>";
$query = "SELECT * FROM rio_objetivos_individuales 
WHERE Nombre_Evaluado = ? AND Periodo = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $NombreEvaluado, $Periodo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ciclo para obtener TODOS los registros
while ($row = mysqli_fetch_assoc($result)) {
    $Resultados[] = $row; // Agregamos cada fila al array
}

$query_General = "SELECT * FROM rio_resultado_general WHERE Nombre_Evaluado = ? AND Periodo = ?";
$stmt_general =  mysqli_prepare($conn, $query_General);

mysqli_stmt_bind_param($stmt_general, "ss", $NombreEvaluado, $Periodo);
mysqli_stmt_execute($stmt_general);
$resultados_General = mysqli_stmt_get_result($stmt_general);

while ($rowe = mysqli_fetch_assoc($resultados_General)) {
    $ResultadosGenerales[] = $rowe; // Agregamos cada fila al array
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Resultados RIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include "../../Front/navbar.php"; ?>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Evaluación de <?= htmlspecialchars($Resultados[0]['Nombre_Evaluado']) ?> (<?= $Resultados[0]['Periodo'] ?>)
                </h4>
            </div>
            <div class="card-body">
                <div>
                    <!-- 👇 Resumen General (Nuevo) -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-3 text-primary">
                                <i class="fas fa-chart-pie me-2"></i>Resumen por Responsabilidad
                            </h5>

                            <div class="row">
                                <?php foreach ($ResultadosGenerales as $item): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="card-title text-center text-uppercase fw-bold">
                                                    <?= htmlspecialchars($item['Responsabilidad']) ?>
                                                </h6>

                                                <!-- Barra de progreso estilo moderno -->
                                                <div class="progress mb-2" style="height: 20px;">
                                                    <?php
                                                    $porcentaje = $item['Porcentaje_Obtenido'];
                                                    $color = ($porcentaje >= 80) ? 'bg-success' : (($porcentaje >= 50) ? 'bg-warning' : 'bg-danger');
                                                    ?>
                                                    <div
                                                        class="progress-bar progress-bar-striped <?= $color ?>"
                                                        role="progressbar"
                                                        style="width: <?= $porcentaje ?>%"
                                                        aria-valuenow="<?= $porcentaje ?>"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        <?= round($porcentaje, 1) ?>%
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between small">
                                                    <span class="text-muted">Obtenido: <strong><?= $item['Obtenido'] ?></strong></span>
                                                    <span class="text-muted">Total: <strong><?= $item['Total_Categoria'] ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- 👇 Tarjeta de Total General (opcional) -->
                                <div class="col-md-12 mt-3">
                                    <div class="card bg-light">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold">TOTAL GENERAL:</span>
                                                <span class="badge bg-primary fs-6">
                                                    <?= array_sum(array_column($ResultadosGenerales, 'Obtenido')) ?> /
                                                    <?= array_sum(array_column($ResultadosGenerales, 'Total_Categoria')) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 👆 Fin Resumen -->
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped text-center">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Responsabilidad</th>
                                <th width="10%">Ponderación</th>
                                <th width="30%">Objetivo</th>
                                <th width="10%">Calificación</th>
                                <th width="10%">Justificación</th>
                                <th width="10%">Porcentaje</th>
                                <th width="10%">Indicador</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $responsabilidad_actual = '';
                            foreach ($Resultados as $item):
                                // Mostrar solo cuando cambia la responsabilidad
                                $mostrar_responsabilidad = ($item['Responsabilidad'] != $responsabilidad_actual);
                                $responsabilidad_actual = $item['Responsabilidad'];
                            ?>
                                <tr>
                                    <!-- Columna Responsabilidad -->
                                    <td>
                                        <?php if ($mostrar_responsabilidad): ?>
                                            <span class="badge bg-info text-light">
                                                <?= htmlspecialchars($item['Responsabilidad']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Columna Ponderación -->
                                    <td>
                                        <?php if ($mostrar_responsabilidad): ?>
                                            <span class="badge bg-primary text-light">
                                                <?= htmlspecialchars($item['Ponderacion']) ?>%
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Objetivo -->
                                    <td><?= htmlspecialchars($item['Objetivo']) ?></td>

                                    <!-- Calificación -->
                                    <td>
                                        <span class="badge rounded-pill bg-<?= ($item['Calificacion_Obtenida'] >= 80 ? 'success' : 'warning') ?>">
                                            <?= $item['Calificacion_Obtenida'] ?>%
                                        </span>
                                    </td>

                                    <!-- Justificación -->
                                    <td>
                                        <?= $item['Justificacion'] ? htmlspecialchars($item['Justificacion']) : '<span class="text-muted">N/A</span>' ?>
                                    </td>

                                    <!-- Peso (individual) -->
                                    <td><?= $item['Peso'] ?></td>

                                    <!-- Indicador -->
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($item['Indicador']) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td></td>
                                <td></td>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold"><?= array_sum(array_column($Resultados, 'Peso')) ?></td>
                                <td class="fw-bold"><?= array_sum(array_column($Resultados, 'Peso_Obtenido')) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
            <div class="card-footer text-muted small">
                Evaluador: <?= htmlspecialchars($Resultados[0]['Nombre_Evaluador']) ?>
            </div>
        </div>
    </div>
    <br><br>
</body>

</html>