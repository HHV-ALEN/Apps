<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$loged_user = $_SESSION['Name'];


//echo "<br> Usuario Logeado: " . $loged_user;
//echo "<br> Usuarios a cargo:";

$usuarios_a_cargo = [];
//Obtener los usuarios que tengan $loged_user en el campo Jerarquia
$jerarquia_query = "SELECT Nombre FROM usuarios WHERE Jerarquia = '$loged_user'";
$result_jerarquia = mysqli_query($conn, $jerarquia_query);

while ($row = mysqli_fetch_assoc($result_jerarquia)) {
    $usuarios_a_cargo[] = $row;
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

// Configurar locale a español (Linux/Mac puede variar a 'es_ES.UTF-8')
setlocale(LC_TIME, 'es_MX.UTF-8', 'es_ES.UTF-8', 'spanish');

// Generar etiquetas más amigables
$periodos_etiquetas = [];
foreach ($periodos as $p) {
    $dateObj = DateTime::createFromFormat('Y-m', $p);
    $nombreMes = strftime('%B', $dateObj->getTimestamp()); // agosto
    $nombreMes = ucfirst($nombreMes); // Agosto
    $periodos_etiquetas[$p] = $p . ' - ' . $nombreMes;
}

// Si el arreglo : $usuarios_a_cargo[] esta vacio
if (empty($usuarios_a_cargo)) {
    //echo "<div class='alert alert-warning text-center'>No hay usuarios a cargo.</div>";
    /// Regresar a la página anterior con un mensaje de "No tienes usuarios a cargo"
    echo "<script>setTimeout(function(){ window.history.back(); }, 1);</script>";
    $_SESSION['mensaje'] = "No tienes usuarios a cargo";
    $_SESSION['tipo_mensaje'] = "warning";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
    <title>Rio - Index</title>
    <style>
        /* Animación heartbeat */
        @keyframes heartbeat {

            0%,
            100% {
                transform: scale(1);
            }

            25% {
                transform: scale(1.05);
            }

            50% {
                transform: scale(1);
            }

            75% {
                transform: scale(1.05);
            }
        }

        .heartbeat {
            animation: heartbeat 1.5s ease-in-out 2;
            /* dura 1.5s y se repite 3 veces */
            display: inline-block;
        }
    </style>
</head>

<body class="bg-light">
    <?php include "../Front/navbar.php"; ?>


    <div class="container py-5 text-center">
        <div class="row align-items-center my-4">
            <div class="col-md-4 text-md-end text-center">
                <h2 class="fw-bold">Evaluaciones Mensuales</h2>
            </div>
            <div class="col-md-8 text-md-start text-center">
                <label for="selectPeriodo"
                    class="form-label fw-bold text-primary heartbeat"
                    style="font-size: 1.2rem;">
                    📅 Seleccionar Periodo: &nbsp; &nbsp;
                </label>
                <select id="selectPeriodo"
                    class="form-select form-select-lg border-primary fw-bold heartbeat"
                    style="max-width: 300px; display: inline-block;"
                    onchange="window.location.href='?periodo=' + this.value;">
                    <?php foreach ($periodos as $periodo): ?>
                        <option value="<?= $periodo ?>" <?= ($periodo == $periodo_consulta) ? 'selected' : '' ?>>
                            <?= $periodos_etiquetas[$periodo] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <HR>

        <div class="table-responsive shadow rounded bg-white">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Archivo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios_a_cargo as $usuario):
                        // Generar el nombre esperado del archivo
                        $nombreArchivo = 'RIO_' . str_replace(' ', '_', $usuario['Nombre']) . '.xlsx';
                        $rutaArchivo = 'Archivos/' . $nombreArchivo;
                        $archivoExiste = file_exists($rutaArchivo);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['Nombre']) ?></td>
                            <td>
                                <?php if ($archivoExiste): ?>
                                    <span class="text-success"><?= $nombreArchivo ?></span>
                                    <br>
                                    <button
                                        class="btn btn-sm btn-warning reemplazarArchivoBtn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReemplazarArchivo"
                                        data-nombre="<?= htmlspecialchars($usuario['Nombre']) ?>"
                                        data-Archivo="<?= $nombreArchivo ?>">
                                        Reemplazar
                                    </button>
                                <?php else: ?>
                                    <!-- Botón para subir Archivo -->
                                    <button
                                        class="btn btn-sm btn-primary subirArchivoBtn"
                                        data-bs-toggle="modal"

                                        data-bs-target="#modalSubirArchivo"
                                        data-nombre="<?= htmlspecialchars($usuario['Nombre']) ?>">

                                        Subir Archivo Rio
                                    </button>

                                <?php endif; ?>
                            </td>

                            <!-- Modal para subir Archivo -->
                            <div class="modal fade" id="modalSubirArchivo" tabindex="-1"  aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="Back/subirArchivoRio.php" id="formSubirArchivo" enctype="multipart/form-data" method="POST">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Subir archivo para <span id="nombreUsuario"></span></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="NombreUsuario" id="NombreUsuario">
                                                <input type="file" name="archivo" class="form-control" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">Guardar</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Modal para reemplazar el Archivo -->
                            <div class="modal fade" id="modalReemplazarArchivo" tabindex="-1"  aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="Back/reeemplazarArchivoRio.php" id="formReemplazarArchivo" enctype="multipart/form-data" method="POST">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Subir archivo para <span id="nombreUsuario"></span></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="NombreUsuario" id="NombreUsuarioRemp">
                                                <input type="file" name="archivo" class="form-control" required>
                                                <input type="hidden" name="archivoOld" id="ArchivoOldRemp" clas="form-control">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">Guardar</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <td>
                                <span class="badge <?= $archivoExiste ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $archivoExiste ? 'Archivo Asignado' : 'Pendiente' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($archivoExiste): ?>
                                    <div class="d-flex flex-column gap-2">

                                        <?php
                                        $periodo_consulta = $_GET['periodo'] ?? $periodo_actual;
                                        $existe_registro = FALSE;
                                        $user_name = $usuario['Nombre'];

                                        // Consulta si existe evaluación
                                        $query = "SELECT COUNT(*) AS total FROM rio_objetivos_individuales WHERE Nombre_Evaluado = ? AND Periodo = ?";
                                        $stmt = mysqli_prepare($conn, $query);
                                        mysqli_stmt_bind_param($stmt, "ss", $user_name, $periodo_consulta);
                                        mysqli_stmt_execute($stmt);
                                        $result = mysqli_stmt_get_result($stmt);
                                        $row = mysqli_fetch_assoc($result);
                                        ?>

                                        <?php if ($row['total'] > 0): ?>
                                            <div class="alert alert-info p-1 mb-1 text-center">
                                                Ya existe evaluación para <strong><?= $periodo_consulta ?></strong>.
                                            </div>
                                            <a href="Front/resultados.php?NombreEvaluado=<?= $user_name ?>&Periodo=<?= $periodo_consulta ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-bar-chart-line"></i>
                                                <span class="d-none d-sm-inline">Ver Resultados</span>
                                            </a>
                                            <?php
                                            $existe_registro = TRUE; ?>

                                        <?php else: ?>
                                            <div class="alert alert-warning p-1 mb-1 text-center">
                                                No se ha evaluado el periodo <strong><?= $periodo_consulta ?></strong>.
                                            </div>
                                        <?php endif; ?>
                                        <!-- Botón Descargar -->
                                        <a href="<?= $rutaArchivo ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-download"></i>
                                            <span class="d-none d-sm-inline">Descargar RIO</span>
                                        </a>

                                        <?php
                                        if (!$existe_registro) {
                                            $query_total = "SELECT COUNT(*) AS total_evidencias FROM rio_evidencias WHERE Responsable = ? AND Periodo = ?";
                                            $stmt = mysqli_prepare($conn, $query_total);
                                            mysqli_stmt_bind_param($stmt, "ss", $user_name, $periodo_consulta);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $row = mysqli_fetch_assoc($result);
                                        ?>

                                            <?php if ($row['total_evidencias'] != 0): ?>
                                                <a href="Front/evaluar.php?Nombre=<?= $user_name ?>&Archivo=<?= $nombreArchivo ?>&periodo=<?= $periodo_consulta ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil-square"></i>
                                                    <span class="d-none d-sm-inline">Evaluar Personal</span>
                                                </a>
                                            <?php else: ?>
                                                <small class="text-muted">No hay evidencias registradas</small>
                                            <?php endif; ?>
                                        <?php } ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted">Pendiente de asignación</small>
                                <?php endif; ?>
                                <?php

                                /// Si ya se encuentran Registros de Feedback de este mes, no mostrar:
                                // Verificar Si ya existen registros de Feedback$user_name
                                $userName = $usuario['Nombre'];
                                $query_feedback = "SELECT COUNT(*) AS total FROM rio_feedback WHERE Nombre_Encuestado = '$userName' AND Periodo = '$periodo_consulta'";
                                $result_feedback = mysqli_query($conn, $query_feedback);
                                $linea_feedback = mysqli_fetch_assoc($result_feedback);
                                if ($linea_feedback['total'] > 0) {
                                ?>
                                    <a href="Front/resultadosFeedback.php?NombreEvaluado=<?= $userName ?>&Periodo=<?= $periodo_consulta ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-bar-chart-line"></i>
                                        <span class="d-none d-sm-inline">Resultados Feedback</span>

                                    </a>

                                    <?php
                                } else {
                                    //// Solo contestar en Los meses de Abril, Julio, Octubre y Enero
                                    $mes_seleccionado = substr($periodo_consulta, 5, 2);
                                    //echo "<br> Mes Seleccionado: " . $mes_seleccionado;
                                    if (in_array($mes_seleccionado, ['04', '07', '10', '01'])) {
                                        // Si ya se tienen registros de Evaluación acorde a este periodo
                                        $query_evaluacion = "SELECT COUNT(*) AS total FROM rio_objetivos_individuales WHERE Nombre_Evaluado = '$userName' AND Periodo = '$periodo_consulta'";
                                        $result_evaluacion = mysqli_query($conn, $query_evaluacion);
                                        $linea_evaluacion = mysqli_fetch_assoc($result_evaluacion);
                                        if ($linea_evaluacion['total'] > 0) {
                                    ?>
                                            <a href="Front/feedback.php?Nombre=<?= $userName ?>&Archivo=<?= $nombreArchivo ?>&periodo=<?= $periodo_consulta ?>" class="btn btn-sm btn-warning mt-2">
                                                <i class="bi bi-pencil-square"></i>
                                                <span class="d-none d-sm-inline">Feedback Personal</span>
                                            </a>
                                <?php
                                        }
                                    }
                                }

                                ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

    document.addEventListener('hidden.bs.modal', function (event) {
  document.body.classList.remove('modal-open');
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
});

        /// Modal para subir Archivo
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionamos todos los botones con la clase subirArchivoBtn
            document.querySelectorAll('.subirArchivoBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Obtenemos el nombre del atributo data-nombre
                    const nombre = this.getAttribute('data-nombre');

                    // Colocamos el nombre en el <span> del título del modal
                    document.getElementById('nombreUsuario').textContent = nombre;

                    // Colocamos el nombre en el input oculto/texto para enviarlo al servidor
                    document.getElementById('NombreUsuario').value = nombre;

                    // Mostramos el modal
                    const modal = new bootstrap.Modal(document.getElementById('modalSubirArchivo'));
                    modal.show();
                });
            });
        });

        /// Modal para reemplazar Archivo
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionamos todos los botones con la clase reemplazarArchivoBtn
            document.querySelectorAll('.reemplazarArchivoBtn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Obtenemos el nombre del atributo data-nombre
                    const nombre = this.getAttribute('data-nombre');
                    const archivoOld = this.getAttribute('data-Archivo');
                    console.log("Archivo Antiguo: ", archivoOld);
                    console.log("Nombre De Usuario", nombre);

                    // Colocamos el nombre en el <span> del título del modal
                    document.getElementById('nombreUsuario').textContent = nombre;

                    // Colocamos el nombre en el input oculto/texto para enviarlo al servidor
                    document.getElementById('NombreUsuarioRemp').value = nombre;

                    document.getElementById('ArchivoOldRemp').value = archivoOld;

                    // Mostramos el modal
                    const modal = new bootstrap.Modal(document.getElementById('modalReemplazarArchivo'));
                    modal.show();
                });
            });
        });
    </script>

</body>

</html>