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

$NombreEvaluado = $_GET['Nombre'];
$Periodo_Seleccionado = $_GET['periodo'];

//echo "<br> Nombre Evaluado: " . $NombreEvaluado;
//echo "<br> Periodo Seleccionado: " . $Periodo_Seleccionado;

$meses_anteriores = [];
for ($i = 1; $i <= 3; $i++) {
    $meses_anteriores[] = date("Y-m", strtotime("-{$i} month", strtotime($Periodo_Seleccionado . "-01")));
}

//print_r($meses_anteriores);

$placeholders = implode(',', array_fill(0, count($meses_anteriores), '?'));
$sql = "SELECT * FROM rio_objetivos_individuales WHERE Periodo IN ($placeholders) AND Nombre_Evaluado = ?";

// Combina los meses y el nombre en un solo array
$params = array_merge($meses_anteriores, [$NombreEvaluado]);

// Prepara y ejecuta
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$datos_feedback = [];
while ($row = $result->fetch_assoc()) {
    $datos_feedback[] = $row;
}

/*
foreach ($datos_feedback as $dato) {
    echo "<br> {$dato['Id']} - {$dato['Objetivo']} - {$dato['Responsabilidad']} - {$dato['Calificacion_Obtenida']} - {$dato['Justificacion']}<br>";
} */

$agrupados = [];

foreach ($datos_feedback as $dato) {
    $resp = $dato['Responsabilidad'];
    $obj = $dato['Objetivo'];
    $periodo = $dato['Periodo'];
    $calificacion = $dato['Calificacion_Obtenida'];
    $justificacion = $dato['Justificacion'] ?? 'N/A';

    // Creamos la estructura
    if (!isset($agrupados[$resp])) {
        $agrupados[$resp] = [];
    }
    if (!isset($agrupados[$resp][$obj])) {
        $agrupados[$resp][$obj] = [];
    }
    $agrupados[$resp][$obj][$periodo] = [
        'Calificacion' => $calificacion
    ];

    if (!empty($justificacion)) {
        $agrupados[$resp][$obj][$periodo]['Justificacion'] = $justificacion;
    }
}

// Imprimir el campo Justificacion del arreglo $agrupados:
//print_r($agrupados);

// Paso 2: Detectar todos los periodos únicos para las columnas dinámicas
$periodos_unicos = [];
foreach ($datos_feedback as $dato) {
    if (!in_array($dato['Periodo'], $periodos_unicos)) {
        $periodos_unicos[] = $dato['Periodo'];
    }
}
sort($periodos_unicos); // Orden cronológico

//print_r($agrupados);
/// Obtener Feedbacks en Estados Inconforme y/o en desarrollo de periodos anteriores dentro de los Periodos:
$periodos = [
    '2025-01',
    '2025-04',
    '2025-07',
    '2025-10'
];

//echo "<br>" . $NombreEvaluado;

$Consulta_Feedback_Pasados = "SELECT * FROM rio_feedback WHERE Periodo IN ('" . implode("','", $periodos) . "') AND Nombre_Encuestado = ? AND Estado IN ('Inconforme', 'En Desarrollo')";
$stmt = $conn->prepare($Consulta_Feedback_Pasados);
$stmt->bind_param('s', $NombreEvaluado);
$stmt->execute();
$result = $stmt->get_result();

$feedback_past = [];
while ($row = $result->fetch_assoc()) {
    $feedback_past[] = $row;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Feedback - RIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <?php include "../../Front/navbar.php"; ?>

    <div class="container mt-4">
        <?php if (!empty($feedback_past)): ?>
            <!-- 🔹 Título descriptivo -->
            <div class="mb-4 text-center">
                <h4 class="fw-bold">Feedbacks pendientes del periodo anterior</h4>
                <p class="text-muted">
                    Periodo: <span class="fw-semibold"><?= htmlspecialchars($Periodo_Seleccionado) ?></span>
                </p>
            </div>

            <div id="FeedBacksPendientes">

                <form method="post" action="../Back/guardar_feedback_pendientes.php">
                    <input type="hidden" name="Nombre" value="<?= htmlspecialchars($NombreEvaluado) ?>">
                    <input type="hidden" name="periodo" value="<?= htmlspecialchars($Periodo_Seleccionado) ?>">

                    <!-- 🔹 Cards en grid -->
                    <div class="row g-3">
                        <?php foreach ($feedback_past as $fila): ?>
                            <div class="col-md-6 col-lg-4"> <!-- 2 en tablets, 3 en desktop -->
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="mb-1">
                                            <strong>Encuestado:</strong> <?= htmlspecialchars($fila['Nombre_Encuestado']) ?>
                                        </h6>
                                        <p class="mb-1">
                                            <strong>Encuestador:</strong> <?= htmlspecialchars($fila['Nombre_Encuestador']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Periodo:</strong> <?= htmlspecialchars($fila['Periodo']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Feedback:</strong> <?= htmlspecialchars($fila['Feedback']) ?>
                                        </p>

                                        <div class="mt-auto feedback-actions">
                                            <label class="form-label small mb-1">Estado</label>
                                            <select class="form-select form-select-sm estado-select"
                                                name="estado[<?= $fila['Id'] ?>]">
                                                <option value="">Selecciona...</option>
                                                <option value="Concluido" <?= ($fila['Estado'] == 'Concluido') ? 'selected' : '' ?>>Concluido ✅</option>
                                                <option value="Conforme" <?= ($fila['Estado'] == 'Conforme') ? 'selected' : '' ?>>Conforme 🤔</option>
                                                <option value="Inconforme" <?= ($fila['Estado'] == 'Inconforme') ? 'selected' : '' ?>>Inconforme ⛔</option>
                                                <option value="En Desarrollo" <?= ($fila['Estado'] == 'En Desarrollo') ? 'selected' : '' ?>>En Desarrollo 🕚</option>
                                            </select>

                                            <input type="text"
                                                class="form-control form-control-sm mt-1 comentario-input"
                                                name="comentario[<?= $fila['Id'] ?>]"
                                                placeholder="Comentarios"
                                                value="<?= htmlspecialchars($fila['Comentario']) ?>"
                                                <?= empty($fila['Estado']) ? 'disabled' : '' ?>>
                                        </div>

                                        <input type="hidden" name="id_registro[]" value="<?= $fila['Id'] ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Actualizar Seguimiento</button>

                        <br>
                </form>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.estado-select').forEach(function(select) {
                        select.addEventListener('change', function() {
                            let comentarioInput = this.closest('.feedback-actions').querySelector('.comentario-input');
                            comentarioInput.disabled = (this.value === '');
                        });
                    });
                });
            </script>

    </div><br>
    <hr>

<?php else: ?>
    <p class="text-muted text-center">No hay feedbacks pendientes.</p>
<?php endif; ?>

<div class="mb-4 text-center">
    <h4 class="fw-bold">Feedback correspondiente al Periodo <?= $Periodo_Seleccionado ?>: </h4>
</div>

<form action="../Back/guardar_feedback.php?Periodo=<?= $Periodo_Seleccionado ?>&Evaluado=<?= $NombreEvaluado ?>" method="POST">
    <div class="card p-4">
        <?php
        // Agrupar datos
        $agrupados = [];
        foreach ($datos_feedback as $dato) {
            $clave = $dato['Objetivo'] . '|' . $dato['Responsabilidad'];

            if (!isset($agrupados[$clave])) {
                $agrupados[$clave] = [
                    'Objetivo' => $dato['Objetivo'],
                    'Responsabilidad' => $dato['Responsabilidad'],
                    'calificaciones' => [],
                    'Justificacion' => $dato['Justificacion'] ?? 'N/A',
                    'Id' => $dato['Id'] // Asumo que hay un ID para cada registro
                ];

            }

            $agrupados[$clave]['calificaciones'][$dato['Periodo']] = $dato['Calificacion_Obtenida'];
        }

        ?>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Id</th>
                        <th>Objetivo</th>
                        <th>Responsabilidad</th>
                        <?php foreach ($periodos_unicos as $periodo): ?>
                            <th class="text-center"><?= $periodo ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">Promedio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agrupados as $fila): ?>

                        <?php
                        // Obtener calificaciones
                        $calificaciones = [];
                        foreach ($periodos_unicos as $periodo) {
                            $calificaciones[$periodo] = $fila['calificaciones'][$periodo] ?? '-';
                        }

                        // Calcular promedio
                        $valores = array_filter($calificaciones, fn($v) => is_numeric($v));
                        $promedio = count($valores) > 0 ? round(array_sum($valores) / count($valores), 2) : '-';
                        ?>

                        <tr>
                            <td><?= htmlspecialchars($fila['Id']) ?>
                                <input type="hidden"
                                    class="form-control form-control-sm mt-1"
                                    name="Id[<?= $fila['Id'] ?>]"
                                    value="<?= htmlspecialchars($fila['Id']) ?>"
                                    required>

                            </td>
                            <td><?= htmlspecialchars($fila['Objetivo']) ?>
                                <input type="hidden"
                                    class="form-control form-control-sm mt-1"
                                    name="Objetivo[<?= $fila['Id'] ?>]"
                                    value="<?= htmlspecialchars($fila['Objetivo']) ?>"
                                    required>
                            </td>
                            <td><?= htmlspecialchars($fila['Responsabilidad']) ?></td>
                            <?php foreach ($periodos_unicos as $periodo): ?>
                                <td class="text-center <?= getCellClass($calificaciones[$periodo]) ?>">
                                    <?php
                                    $valor = $fila['calificaciones'][$periodo] ?? '-';

                                    $justificacion = $fila['calificaciones'][$periodo]['Justificacion'] ?? '-';
                                    echo $valor;

                                    // Validar calificación numérica
                                    if (is_numeric($valor) && $valor <= 50) {
                                        $justificacion = $fila['calificaciones'][$periodo]['Justificacion'] ?? 'N/A';
                                        }
                                    ?>
                                </td>
                            <?php endforeach; ?>


                            <td class="text-center fw-bold <?= getCellClass($promedio) ?>">
                                <?= $promedio ?>
                            </td>

                            <td class="p-3">
                                <div class="feedback-actions">
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Estado</label>
                                        <select class="form-select form-select-sm estado-select" name="estado[<?= $fila['Id'] ?>]">
                                            <option value="">Selecciona...</option>
                                            <option value="Concluido">Concluido ✅</option>
                                            <option value="Conforme">Conforme 🤔</option>
                                            <option value="Inconforme">Inconforme ⛔</option>
                                            <option value="En Desarrollo">En Desarrollo 🕚</option>
                                        </select>

                                        <input type="text"
                                            class="form-control form-control-sm mt-1 comentario-input"
                                            name="comentario[<?= $fila['Id'] ?>]"
                                            placeholder="Comentarios"
                                            rows="2"
                                            disabled>
                                    </div>

                                    <input type="hidden" name="id_registro[]" value="<?= $fila['Id'] ?>">
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3">
            Guardar Feedback
        </button>
    </div>

</form>

<?php
// Función helper para clases de celdas basadas en el valor
function getCellClass($value)
{
    if (!is_numeric($value)) return '';

    if ($value >= 51) return 'table-success';
    if ($value >= 1) return 'table-warning';
    return 'table-danger';
}
?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".estado-select").forEach(function(select) {
            select.addEventListener("change", function() {
                let inputComentario = this.closest("td").querySelector(".comentario-input");

                if (this.value === "Inconforme" || this.value === "En Desarrollo") {
                    inputComentario.disabled = false;
                    inputComentario.required = true;
                } else {
                    inputComentario.disabled = true;
                    inputComentario.required = false;
                    inputComentario.value = "";
                }
            });
        });
    });
</script>


</html>