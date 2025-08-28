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
$feedbacks = [];
/// Obtener Registros de Feedback

$consultar_feedbacks = "SELECT * FROM rio_feedback WHERE Nombre_Encuestado = ? AND Periodo = ?";
$stmt_general =  mysqli_prepare($conn, $consultar_feedbacks);
mysqli_stmt_bind_param($stmt_general, "ss", $NombreEvaluado, $Periodo);
mysqli_stmt_execute($stmt_general);
$resultados_General = mysqli_stmt_get_result($stmt_general);

while ($rowe = mysqli_fetch_assoc($resultados_General)) {
    $feedbacks[] = $rowe; // Agregamos cada fila al array
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Resultados Feedback RIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include "../../Front/navbar.php"; ?>

    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <form action="../Back/guardar_Compromiso.php?Periodo=<?= $Periodo ?>&Evaluado=<?= $NombreEvaluado ?>" method="POST">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-comment-dots me-2"></i>
                        Feedback para <?= htmlspecialchars($feedbacks[0]['Nombre_Encuestado']) ?> (<?= $feedbacks[0]['Periodo'] ?>)
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="25%">Feedback</th>
                                    <th width="15%">Estado</th>
                                    <th width="25%">Comentario</th>
                                    <th width="20%">Compromiso</th>
                                    <th width="15%">Fecha Límite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbacks as $item):
                                    $feedback = $item['Feedback'];
                                ?>

                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['Feedback']) ?></strong>
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-user-tie me-1"></i> <?= htmlspecialchars($item['Nombre_Encuestador']) ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?php
                                            $badgeColor = match ($item['Estado']) {
                                                'Concluido' => 'bg-success',
                                                'Conforme' => 'bg-primary',
                                                default => 'bg-warning'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeColor ?>">
                                                <?= $item['Estado'] ?>
                                            </span>
                                        </td>

                                        <td><?= nl2br(htmlspecialchars($item['Comentario'])) ?></td>

                                        <?php
                                        /// Responsabilidad del Evaluado
                                        $NombreEvaluadoSesion = $_SESSION['Name'];
                                        if ($NombreEvaluadoSesion ===  $NombreEvaluado) {
                                        ?>
                                            <td>
                                                <?php if (!in_array($item['Estado'], ['Concluido', 'Conforme'])): ?>
                                                    <input type="text"
                                                        class="form-control form-control-sm"
                                                        name="compromiso[<?= $item['Id'] ?>]"
                                                        value="<?= htmlspecialchars($item['Compromiso_Encuestado']) ?>"
                                                        placeholder="Compromiso..." required>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!in_array($item['Estado'], ['Concluido', 'Conforme'])): ?>
                                                    <input type="date"
                                                        class="form-control form-control-sm"
                                                        name="fecha_compromiso[<?= $item['Id'] ?>]"
                                                        value="<?= $item['Fecha_Compromiso'] ? htmlspecialchars($item['Fecha_Compromiso']) : '' ?>"
                                                        min="<?= date('Y-m-d') ?>" required>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php
                                        } else {
                                            /// Cuando se encuentran ya registros, mostrarlos
                                            if ($item['Compromiso_Encuestado'] != '') {
                                                echo "<td><span class='small'>Compromiso: " . htmlspecialchars($item['Compromiso_Encuestado']) . "</span></td>";
                                                echo "<td><span class>Fecha Seleccionada:</span> " . htmlspecialchars($item['Fecha_Compromiso'])   . " </td>";
                                            } else {
                                                echo "<td><span class='text-muted small'>No Contestado</span></td>";
                                            }
                                        }

                                        ?>
                                        <td>
                                            <input type="hidden" value="<?php echo $feedback; ?>" name="feedback[<?= $item['Id'] ?>]">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> Última actualización: <?= $feedbacks[0]['Fecha_Registro'] ?>
                        </small>
                        <?php
                        if ($NombreEvaluadoSesion ===  $NombreEvaluado) {
                        ?>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        <?php
                        }
                        ?>

                    </div>
                </div>
            </form>
        </div>
    </div>
    <br><br>

    <style>
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .badge {
            font-size: 0.85em;
            padding: 5px 8px;
        }
    </style>
</body>

</html>