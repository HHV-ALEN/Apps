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

function getStatusColor($status)
{
    $colors = [
        'Concluido' => 'success',
        'Conforme' => 'primary',
        'Inconforme' => 'danger',
        'En Desarrollo' => 'warning'
    ];
    return $colors[$status] ?? 'secondary';
}

function formatDate($date)
{
    return date('d M Y', strtotime($date));
}


// echo "<pre>";
// print_r($Resultados);
// echo "</pre>";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Feedback - RIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .feedback-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding: 1rem;
        }

        .feedback-card {
            width: 320px;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .feedback-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .feedback-title {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }

        .feedback-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .feedback-content {
            padding: 1rem;
            flex-grow: 1;
        }

        .feedback-actions {
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .feedback-comment p,
        .feedback-commitment p,
        .feedback-date p {
            margin: 0.25rem 0 0;
            color: #555;
        }
    </style>
</head>

<body>
    <?php include "../../Front/navbar.php"; ?>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Feedback para <?= htmlspecialchars($Resultados[0]['Nombre_Evaluado']) ?> (<?= $Resultados[0]['Periodo'] ?>)
                </h4>
            </div>

            <div class="card-body">

                <?php
                // Consultar la tabla rio_feedback para obtener los registros pendientes
                $query_feedback = "SELECT * FROM rio_feedback 
                    WHERE Nombre_Encuestado = ? 
                    AND Estado NOT IN ('Concluido', 'Conforme')";
                $stmt_feedback = mysqli_prepare($conn, $query_feedback);
                mysqli_stmt_bind_param($stmt_feedback, "s", $NombreEvaluado);
                mysqli_stmt_execute($stmt_feedback);
                $result_feedback = mysqli_stmt_get_result($stmt_feedback);

                // Solo mostrar si hay registros
                if (mysqli_num_rows($result_feedback) > 0) {
                ?>
                    <div class="table-responsive">
                        <h4>Pendientes de Concluir:</h4>
                        <hr>
                        <form method="POST" action="../Back/procesar_feedback.php?Evaluado=<?= $NombreEvaluado ?>&Periodo=<?= $Periodo ?>">
                            <div class="feedback-container">
                                <?php while ($row_feedback = mysqli_fetch_assoc($result_feedback)): ?>
                                    <div class="feedback-card">
                                        <!-- Encabezado de la tarjeta -->
                                        <div class="feedback-header">
                                            <h6 class="feedback-title"><?= htmlspecialchars($row_feedback['Feedback']) ?></h6>
                                            <div class="feedback-meta">
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($row_feedback['Periodo']) ?></span>
                                                <span class="badge bg-<?= getStatusColor($row_feedback['Estado']) ?>">
                                                    <?= htmlspecialchars($row_feedback['Estado']) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Contenido principal -->
                                        <div class="feedback-content">
                                            <?php if (!empty($row_feedback['Comentario'])): ?>
                                                <div class="feedback-comment">
                                                    <strong>Comentario:</strong>
                                                    <p><?= htmlspecialchars($row_feedback['Comentario']) ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <div class="feedback-commitment">
                                                <strong>Compromiso registrado:</strong>
                                                <p><?= htmlspecialchars($row_feedback['Compromiso_Encuestado']) ?></p>
                                            </div>

                                            <?php if (!empty($row_feedback['Fecha_Compromiso'])): ?>
                                                <div class="feedback-date">
                                                    <strong>Fecha seleccionada:</strong>
                                                    <p><?= formatDate($row_feedback['Fecha_Compromiso']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Formulario de actualización -->
                                        <div class="feedback-actions">
                                            <div class="mb-3">
                                                <label class="form-label">Nuevo Comentario</label>
                                                <textarea name="comentario[<?= $row_feedback['Id'] ?>]"
                                                    class="form-control"
                                                    rows="2"
                                                    placeholder="Agregar un comentario..."></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Actualizar Estado</label>
                                                <select name="estado[<?= $row_feedback['Id'] ?>]" class="form-select">
                                                    <option value="">Selecciona un estado...</option>
                                                    <option value="Concluido">Concluido ✅</option>
                                                    <option value="Conforme">Conforme 🤔</option>
                                                    <option value="Inconforme">Inconforme ⛔</option>
                                                    <option value="En Desarrollo">En Desarrollo 🕚</option>
                                                </select>
                                            </div>

                                            <input type="hidden" name="id_registro[]" value="<?= $row_feedback['Id'] ?>">
                                        </div>
                                        
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                <?php
                } // Fin del if
                ?>
                <hr>



                <div class="table-responsive">
                    <form action="../Back/guardar_feedback.php?Periodo=<?= $Periodo ?>&Evaluado=<?= $NombreEvaluado ?>" method="POST">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Responsabilidad</th>
                                    <th>Objetivo</th>
                                    <th>Calificación</th>
                                    <th>Peso</th>
                                    <th>Peso Obtenido</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $responsabilidad_actual = '';
                                foreach ($Resultados as $index => $item):

                                    $mostrar_responsabilidad = ($item['Responsabilidad'] != $responsabilidad_actual);
                                    $responsabilidad_actual = $item['Responsabilidad'];
                                    $id = $item['ID'] ?? $index; // Asegúrate de tener un ID único por fila
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-info text-light">
                                                <?php echo $id; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($mostrar_responsabilidad): ?>
                                                <span class="badge bg-info text-light">
                                                    <?= htmlspecialchars($item['Responsabilidad']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['Objetivo']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?= ($item['Calificacion_Obtenida'] >= 80 ? 'success' : 'warning') ?>">
                                                <?= $item['Calificacion_Obtenida'] ?>%
                                            </span>
                                        </td>
                                        <td><?= $item['Peso'] ?></td>
                                        <td><span class="fw-bold"><?= $item['Peso_Obtenido'] ?></span></td>
                                        <td>
                                            <select class="form-select form-select-sm estado-select" name="estado[<?= $id ?>]">
                                                <option value="">Selecciona...</option>
                                                <option value="Concluido">Concluido ✅</option>
                                                <option value="Conforme">Conforme 🤔</option>
                                                <option value="Inconforme">Inconforme ⛔</option>
                                                <option value="En Desarrollo">En Desarrollo 🕚</option>
                                            </select>

                                            <input type="text"
                                                class="form-control form-control-sm mt-1 comentario-input"
                                                name="comentario[<?= $id ?>]"
                                                placeholder="Comentarios"
                                                disabled>

                                            <input type="hidden"
                                                name="Objetivo[<?= $id ?>]"
                                                value="<?= htmlspecialchars($item['Objetivo']) ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-primary">Guardar Feedback</button>
                    </form>

                </div>
            </div>
            <div class="card-footer text-muted small">
                Evaluador: <?= htmlspecialchars($Resultados[0]['Nombre_Evaluador']) ?>
            </div>
        </div>
    </div>
    <br><br>
</body>


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