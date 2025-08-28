<?php
include("../../Back/config/config.php"); // --> Conexión con Base de datos
session_start(); // --> Iniciar sesión
$conn = connectMySQLi();
$conn = connectMySQLi();
$Nombre_Consulta = $_GET['Nombre'] ?? null;
$ver = isset($_GET['ver']) ? $_GET['ver'] : 'actuales';
/// Actualizar Información de la tabla vacaciones_general
$NombreEncoded = urlencode($Nombre_Consulta); // por si tiene espacios o acentos

$DaysUsed = 0;
$DaysRestantes = 0;


$anio = date('Y');

function getMexicanHolidays($year)
{
    $holidays = [
        "$year-01-01", // Año Nuevo
        "$year-02-03", // Constitución (primer lunes feb)
        "$year-03-17", // Benito Juárez (tercer lunes marzo)
        "$year-04-17", // Jueves Santo
        "$year-04-18", // Viernes Santo
        "$year-05-01", // Día del Trabajo
        "$year-09-16", // Independencia
        "$year-11-17", // Revolución (tercer lunes nov)
        "$year-12-25", // Navidad
    ];

    return $holidays;
}

$Dias_Feriados = getMexicanHolidays($anio);

$fechas = array_merge(
    getMexicanHolidays(2024),
    getMexicanHolidays(2025)
);

$HumanR = ($_SESSION['User_Id'] == 26 || $_SESSION['User_Id'] == 27 );


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $Nombre_Consulta ?? "Detalles" ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <link rel="stylesheet" href="css/detalles.css">
    <!-- CSS de SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- JS de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "../../Front/navbar.php";

    /// Mostrar mensajes de éxito o error
    if (isset($_SESSION['Mensaje'])) {
        $mensaje = $_SESSION['Mensaje'];
        $tipo_mensaje = $_SESSION['Tipo_Mensaje'];
        echo "<script>
            Swal.fire({
                icon: '$tipo_mensaje',
                title: '$mensaje',
                showConfirmButton: true,
                timer: 3000
            });
        </script>";
        unset($_SESSION['Mensaje']);
        unset($_SESSION['Tipo_Mensaje']);
    }

    ?>

    <div class="container py-4">
        <?php

        $query = "SELECT * FROM vacaciones_general WHERE Usuario = '$Nombre_Consulta'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $Dias_Restantes_bd = $row['Dias_Restantes'];
            $Dias_Solicitados_bd = $row['Dias_Solicitados'];
            $Antiguedad = $row['Antiguedad'];

            // Calculate period dates
            $fechaAntiguedad = new DateTime($Antiguedad);
            $fechaHoy = new DateTime();
            $anioHoy = $fechaHoy->format("Y");
            $aniversarioEsteAnio = DateTime::createFromFormat("Y-m-d", $anioHoy . '-' . $fechaAntiguedad->format('m-d'));

            if ($fechaHoy < $aniversarioEsteAnio) {
                $inicioPeriodo = clone $aniversarioEsteAnio;
                $inicioPeriodo->modify('-1 year');
                $finPeriodo = clone $aniversarioEsteAnio;
            } else {
                $inicioPeriodo = clone $aniversarioEsteAnio;
                $finPeriodo = clone $aniversarioEsteAnio;
                $finPeriodo->modify('+1 year');
            }

            // Calculate seniority
            $diferencia = $fechaAntiguedad->diff($fechaHoy);


            $anios = $diferencia->y;
            $meses = $diferencia->m;
            $dias = $diferencia->d;

            if ($anios < 1) {
                $Dias_Restantes = 0;
            } elseif ($anios == 1) {
                $Dias_Restantes = 12;
            } elseif ($anios == 2) {
                $Dias_Restantes = 14;
            } elseif ($anios == 3) {
                $Dias_Restantes = 16;
            } elseif ($anios == 4) {
                $Dias_Restantes = 18;
            } elseif ($anios == 5) {
                $Dias_Restantes = 20;
            } elseif ($anios >= 6 && $anios <= 10) {
                $Dias_Restantes = 22;
            } elseif ($anios > 10 && $anios <= 15) {
                $Dias_Restantes = 24;
            } elseif ($anios > 15 && $anios <= 20) {
                $Dias_Restantes = 26;
            } elseif ($anios > 20) {
                $Dias_Restantes = 28;
            } elseif ($anios > 5) { // Para antigüedad mayor a 5 años
                $Dias_Restantes = 20 + floor(($anios - 5) / 5) * 2;
            }
            $Variable_Helper = 0;


            if ($ver === 'actuales') {
                $Dias_Feriados = getMexicanHolidays($anio);
                actualizarResumenVacaciones($conn, $Nombre_Consulta, $Dias_Feriados, $inicioPeriodo, $finPeriodo);
            }
            // Información General de la Persona:

        ?>

            <h1 class="mb-4 text-center">Detalles de Vacaciones para <?php echo $row['Usuario']; ?></h1>

            <!-- Vacaciones Solicitadas -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Vacaciones Solicitadas
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-end align-items-stretch gap-2">
                                <?php

                                if ($_SESSION['Name'] == $Nombre_Consulta || $HumanR ) {
                                ?>
                                    <!-- Botón para solicitar Vacaciones -->
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSolicitarVacaciones">
                                        📄 Solicitar Vacaciones
                                    </button>
                                <?php
                                }
                                ?>
                                <!-- Botón Ver Periodo Actual -->
                                <a href="?Nombre=<?= $Nombre_Consulta ?>&ver=actuales"
                                    class="btn btn-primary <?= ($ver == 'actuales') ? 'active' : '' ?>">
                                    <i class="bi bi-arrow-down-square"></i> Ver Periodo Actual
                                </a>

                                <!-- Botón Ver Registros Anteriores -->
                                <a href="?Nombre=<?= $Nombre_Consulta ?>&ver=anteriores"
                                    class="btn btn-outline-secondary <?= ($ver == 'anteriores') ? 'active' : '' ?>">
                                    Ver Registros Anteriores <i class="bi bi-arrow-right-square"></i>
                                </a>
                            </div>
                            <br>
                            <?php


                            $DiasDeVacaciones_Total = 0;
                            $DiasDePermiso_Total = 0;

                            //echo "Nombre: " . $Nombre_Consulta;

                            $query = "SELECT * FROM vacaciones_solicitudes WHERE Usuario = '$Nombre_Consulta' AND Tipo_Permiso = 'Vacaciones' and Estado != 'Inactivo'";
                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0) {
                                echo '<div class="table-responsive text-center">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Días</th>
                                            <th>Fecha de Solicitud</th>
                                            <th>Tipo de Permiso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>';




                                while ($row = mysqli_fetch_assoc($result)) {
                                    //print_r($row);
                                    $Id_vacaciones = $row['Id'];
                                    $Fecha_Inicio = $row['Fecha_Inicio'];
                                    $Fecha_Fin = $row['Fecha_Fin'];
                                    $Tipo_Permiso = $row['Tipo_Permiso'];
                                    $Estado = $row['Estado'];
                                    $Fecha_Solicitud = $row['Fecha_Solicitud'];

                                    $inicioSolicitud = new DateTime($Fecha_Inicio);
                                    $finSolicitud = new DateTime($Fecha_Fin);

                                    if ($ver == 'actuales' && $inicioSolicitud <= $finPeriodo && $finSolicitud >= $inicioPeriodo) {
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $fechas);

                                        // Determine badge class based on status
                                        $badgeClass = '';
                                        if ($Estado == "Aprobada") $badgeClass = 'badge-approved';
                                        elseif ($Estado == "Pendiente") $badgeClass = 'badge-pending';
                                        elseif ($Estado == "Proceso") $badgeClass = 'badge-pending';
                                        else $badgeClass = 'badge-rejected';

                                        // Asignar clase de fondo según el año
                                        $anioSolicitud = (new DateTime($Fecha_Inicio))->format('Y');
                                        $fondoPeriodo = '';
                                        if ($anioSolicitud == 2023) {
                                            $fondoPeriodo = 'bg-2023';
                                        } elseif ($anioSolicitud == 2024) {
                                            $fondoPeriodo = 'bg-2024';
                                        } else {
                                            $fondoPeriodo = 'bg-otros'; // opcional para otros años
                                        }
                                        // Mostrar la fila de la tabla
                                        echo '<tr class="text-center">
                                            <td>' . $Fecha_Inicio . '</td>
                                            <td>' . $Fecha_Fin . '</td>
                                            <td>' . $Dias_Habiles . '</td>
                                            <td>' . $Fecha_Solicitud . '</td>
                                            <td>' . $Tipo_Permiso . '</td>
                                            <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>';

                                        // 🔧 Agrupar TODAS las acciones en un solo <td>
                                        echo '<td>';

                                        if ($_SESSION['Departamento'] == 'Recursos Humanos') {
                                            echo '<button class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editarFechasModal"
                                                    data-id-RH="' . $Id_vacaciones . '"
                                                    data-fecha-inicio-RH="' . $Fecha_Inicio . '"
                                                    data-fecha-fin-RH="' . $Fecha_Fin . '">
                                                <i class="bi bi-pencil-square"></i> Editar Vacaciones
                                            </button>';
                                            echo "&nbsp;";
                                        }

                                        // Si no está aprobada ni rechazada, permitir editar/eliminar
                                        if ($Estado != 'Aprobada' && $Estado != 'Rechazado') {
                                            echo '<button class="btn btn-sm btn-warning editarVacaciones" 
                                                        data-id="' . $Id_vacaciones . '" 
                                                        data-fecha-inicio="' . $Fecha_Inicio . '" 
                                                        data-fecha-fin="' . $Fecha_Fin . '" 
                                                        data-fecha-solicitud="' . $Fecha_Solicitud . '" 
                                                        data-tipo-permiso="' . $Tipo_Permiso . '"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarVacaciones">
                                                        <i class="bi bi-pencil-square"></i> Editar 
                                                    </button>';
                                            echo "&nbsp;";

                                            echo '<button class="btn btn-sm btn-danger eliminarVacaciones" 
                                                        data-id="' . $Id_vacaciones . '"
                                                        data-nombre="' . $Nombre_Consulta . '">
                                                        <i class="bi bi-trash3-fill"></i> Eliminar
                                                    </button>';
                                        }

                                        echo '</td>'; // ✅ Termina columna de acciones
                                        echo '</tr>';
                                        if ($Estado == "Aprobada") {
                                            if ($Tipo_Permiso == "Vacaciones") {
                                                $DiasDeVacaciones_Total += $Dias_Habiles;
                                                $Variable_Helper = $DiasDeVacaciones_Total;
                                            } elseif ($Tipo_Permiso == "Permiso") {
                                                $DiasDePermiso_Total += $Dias_Habiles;
                                            }
                                        }
                                    }
                                    /// Apartado para ver los registros de periodos anteriores
                                    elseif ($ver == 'anteriores' && $inicioSolicitud < $inicioPeriodo) {
                                        $Variable_Helper = 0;
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $fechas);

                                        // Determine badge class based on status
                                        $badgeClass = '';
                                        if ($Estado == "Aprobada") $badgeClass = 'badge-approved';
                                        elseif ($Estado == "Pendiente") $badgeClass = 'badge-pending';
                                        else $badgeClass = 'badge-rejected';

                                        $anioSolicitud = (new DateTime($Fecha_Inicio))->format('Y');
                                        $fondoPeriodo = '';
                                        if ($anioSolicitud == 2023) {
                                            $fondoPeriodo = 'bg-2023';
                                        } elseif ($anioSolicitud == 2024) {
                                            $fondoPeriodo = 'bg-2024';
                                        } else {
                                            $fondoPeriodo = 'bg-otros'; // opcional para otros años
                                        }

                                        echo '<tr class="text-center ' . $fondoPeriodo . '">
                                            <td>' . $Fecha_Inicio . '</td>
                                            <td>' . $Fecha_Fin . '</td>
                                            <td>' . $Dias_Habiles . '</td>
                                            <td>' . $Fecha_Solicitud . '</td>
                                            <td>' . $Tipo_Permiso . '</td>
                                            <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>';
                                        if ($Estado != 'Aprobada' && $Estado != 'Rechazado') {
                                            echo '
                                            <td>
                                                <button class="btn btn-sm btn-warning editarVacaciones" 
                                                    data-id="' . $Id_vacaciones . '" 
                                                    data-fecha-inicio="' . $Fecha_Inicio . '" 
                                                    data-fecha-fin="' . $Fecha_Fin . '" 
                                                    data-fecha-solicitud="' . $Fecha_Solicitud . '" 
                                                    data-tipo-permiso="' . $Tipo_Permiso . '"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarVacaciones">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </button>
                                                
                                                <!-- Botón Eliminar -->
                                                <button class="btn btn-sm btn-danger eliminarVacaciones" 
                                                    data-id="<?php echo $Id_vacaciones; ?>">
                                                    <i class="bi bi-trash3-fill"></i> Eliminar
                                                </button>
                                            </td>
                                            </tr>';
                                        }
                                        echo '<td></td>';

                                        if ($Estado == "Aprobada") {
                                            if ($Tipo_Permiso == "Vacaciones") {
                                                $DiasDeVacaciones_Total += $Dias_Habiles;
                                            } elseif ($Tipo_Permiso == "Permiso") {
                                                $DiasDePermiso_Total += $Dias_Habiles;
                                            }
                                        }
                                    }
                                }
                                echo '</tbody>
                            </table>
                        </div>';
                            } else {
                                echo '<div class="alert alert-info">No se encontraron vacaciones para este periodo. </div>';
                            }

                            if ($Variable_Helper != 0 || $Variable_Helper != null) {
                                $RESULT = $Dias_Restantes - $Variable_Helper;
                                $UPDATE = "UPDATE vacaciones_general SET Dias_Restantes = '$RESULT', Dias_Solicitados = '$Variable_Helper' WHERE Usuario = '$Nombre_Consulta'";
                                $result = mysqli_query($conn, $UPDATE);
                                if ($result) {
                                    //echo "<small>Información actualizada correctamente.</small>";
                                } else {
                                    echo "Error al actualizar la tabla: " . mysqli_error($conn);
                                }
                            } else {
                                //echo "<br> <small>Variable Helper: No Asignado</small>";
                                $Variable_Helper = 0;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjetas de Información -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card stats-card">

                        <div class="card-body text-center">
                            <h5 class="card-title">Días Correspondientes</h5>
                            <h2 class="text-primary"><?php echo $Dias_Restantes; ?></h2>
                            <p class="text-muted">Días Respectivos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Días Usados</h5>
                            <h2 class="text-warning"><?php
                                                        $DaysUsed = $DiasDeVacaciones_Total;
                                                        echo $DiasDeVacaciones_Total; ?></h2>
                            <p class="text-muted">En este periodo</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Dias Restantes</h5>
                            <h2 class="text-success"><?php
                                                        $DaysRestantes = $Dias_Restantes - $DiasDeVacaciones_Total;
                                                        echo $DaysRestantes;
                                                        ?></h2>
                            <p class="text-muted">Disponibles</p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Permisos Especiales Solicitados -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Permisos Especiales Solicitados
                        </div>
                        <div class="card-body">
                            <?php

                            $DiasDeVacaciones_Total = 0;
                            $DiasDePermiso_Total = 0;

                            $query = "SELECT * FROM vacaciones_solicitudes WHERE Usuario = '$Nombre_Consulta' AND Tipo_Permiso = 'Permiso Especial' AND Estado != 'Inactivo'";
                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0) {
                                echo '<div class="table-responsive text-center">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Días</th>
                                            <th>Fecha de Solicitud</th>
                                            <th>Tipo de Permiso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>';


                                while ($row = mysqli_fetch_assoc($result)) {
                                    $Id_vacaciones = $row['Id'];
                                    $Fecha_Inicio = $row['Fecha_Inicio'];
                                    $Fecha_Fin = $row['Fecha_Fin'];
                                    $Tipo_Permiso = $row['Tipo_Permiso'];
                                    $Estado = $row['Estado'];
                                    $Fecha_Solicitud = $row['Fecha_Solicitud'];

                                    $inicioSolicitud = new DateTime($Fecha_Inicio);
                                    $finSolicitud = new DateTime($Fecha_Fin);

                                    if ($ver == 'actuales' && $inicioSolicitud <= $finPeriodo && $finSolicitud >= $inicioPeriodo) {
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $fechas);

                                        // Determine badge class based on status
                                        $badgeClass = '';
                                        if ($Estado == "Aprobada") $badgeClass = 'badge-approved';
                                        elseif ($Estado == "Pendiente") $badgeClass = 'badge-pending';
                                        elseif ($Estado == "Proceso") $badgeClass = 'badge-pending';
                                        else $badgeClass = 'badge-rejected';

                                        // Asignar clase de fondo según el año
                                        $anioSolicitud = (new DateTime($Fecha_Inicio))->format('Y');
                                        $fondoPeriodo = '';
                                        if ($anioSolicitud == 2023) {
                                            $fondoPeriodo = 'bg-2023';
                                        } elseif ($anioSolicitud == 2024) {
                                            $fondoPeriodo = 'bg-2024';
                                        } else {
                                            $fondoPeriodo = 'bg-otros'; // opcional para otros años
                                        }
                                        // Mostrar la fila de la tabla <--- Actuales
                                        echo '<tr class="text-center">
                                        <td>' . $Fecha_Inicio . '</td>
                                        <td>' . $Fecha_Fin . '</td>
                                        <td>' . $Dias_Habiles . '</td>
                                        <td>' . $Fecha_Solicitud . '</td>
                                        <td>' . $Tipo_Permiso . '</td>
                                        <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>';

                                        if ($Estado != 'Aprobada' && $Estado != 'Rechazado') {
                                            echo '
                                        <td>
                                            <button class="btn btn-sm btn-warning editarVacaciones" 
                                                data-id="' . $Id_vacaciones . '" 
                                                data-fecha-inicio="' . $Fecha_Inicio . '" 
                                                data-fecha-fin="' . $Fecha_Fin . '" 
                                                data-fecha-solicitud="' . $Fecha_Solicitud . '" 
                                                data-tipo-permiso="' . $Tipo_Permiso . '"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarVacaciones">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </button>

                                                                                    
                                            <!-- Botón Eliminar -->
                                            <button class="btn btn-sm btn-danger eliminarVacaciones" 
                                                data-id="<?php echo $Id_vacaciones; ?>">
                                                <i class="bi bi-trash3-fill"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>';
                                        }
                                        echo "<td></td>";

                                        if ($Estado == "Aprobada") {
                                            if ($Tipo_Permiso == "Vacaciones") {
                                                $DiasDeVacaciones_Total += $Dias_Habiles;
                                            } elseif ($Tipo_Permiso == "Permiso Especial") {
                                                $DiasDePermiso_Total += $Dias_Habiles;
                                            }
                                        }
                                    }
                                    /// Apartado para ver los registros de periodos anteriores
                                    elseif ($ver == 'anteriores' && $inicioSolicitud < $inicioPeriodo) {
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $fechas);

                                        // Asignar clase de fondo según el año


                                        // Determine badge class based on status
                                        $badgeClass = '';
                                        if ($Estado == "Aprobada") $badgeClass = 'badge-approved';
                                        elseif ($Estado == "Pendiente") $badgeClass = 'badge-pending';
                                        else $badgeClass = 'badge-rejected';

                                        $anioSolicitud = (new DateTime($Fecha_Inicio))->format('Y');
                                        $fondoPeriodo = '';
                                        if ($anioSolicitud == 2023) {
                                            $fondoPeriodo = 'bg-2023';
                                        } elseif ($anioSolicitud == 2024) {
                                            $fondoPeriodo = 'bg-2024';
                                        } else {
                                            $fondoPeriodo = 'bg-otros'; // opcional para otros años
                                        }

                                        /// Mostrar la fila de la tabla <--- Anteriores
                                        echo '<tr class="text-center ' . $fondoPeriodo . '">
                                            <td>' . $Fecha_Inicio . '</td>
                                            <td>' . $Fecha_Fin . '</td>
                                            <td>' . $Dias_Habiles . '</td>
                                            <td>' . $Fecha_Solicitud . '</td>
                                            <td>' . $Tipo_Permiso . '</td>
                                            <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>';
                                        if ($Estado != 'Aprobada' && $Estado != 'Rechazado') {
                                            echo '
                                            <td>
                                                <button class="btn btn-sm btn-warning editarVacaciones" 

                                                    data-id="' . $Id_vacaciones . '" 
                                                    data-fecha-inicio="' . $Fecha_Inicio . '" 
                                                    data-fecha-fin="' . $Fecha_Fin . '" 
                                                    data-fecha-solicitud="' . $Fecha_Solicitud . '" 
                                                    data-tipo-permiso="' . $Tipo_Permiso . '"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarVacaciones">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                
                                                <!-- Botón Eliminar -->
                                                <button class="btn btn-sm btn-danger eliminarVacaciones" 
                                                    data-id="<?php echo $Id_vacaciones; ?>">
                                                    <i class="bi bi-trash3-fill"></i> Eliminar
                                                </button>
                                            </td>
                                            </tr>';
                                        }
                                        echo "<td></td>";
                                        if ($Estado == "Aprobada") {
                                            if ($Tipo_Permiso == "Vacaciones") {
                                                $DiasDeVacaciones_Total += $Dias_Habiles;
                                            } elseif ($Tipo_Permiso == "Permiso Especial") {
                                                $DiasDePermiso_Total += $Dias_Habiles;
                                            }
                                        }
                                    }
                                }
                                echo '</tbody>
                            </table>
                        </div>';
                            } else {
                                echo '<div class="alert alert-info">No se encontraron Permisos Especiales registrados para este periodo. </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal para editar vacaciones -->
            <div class="modal fade" id="modalEditarVacaciones" tabindex="-1" aria-labelledby="modalEditarVacacionesLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="formEditarVacaciones" action="../Back/Editar_Vacaciones.php?Nombre=<?php echo $Nombre_Consulta ?>" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEditarVacacionesLabel">Editar Vacaciones</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="editIdVacaciones" name="id">
                                <div class="mb-3">
                                    <label for="editFechaInicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="editFechaInicio" name="fecha_inicio">
                                </div>
                                <div class="mb-3">
                                    <label for="editFechaFin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="editFechaFin" name="fecha_fin">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php
        } else {
            echo '<div class="alert alert-danger">Información No encontrada.</div>';
        }
        $Nombre_Consulta = $_GET['Nombre'] ?? null;

        // Información General de la Persona:
        $query = "SELECT * FROM vacaciones_general WHERE Usuario = '$Nombre_Consulta'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $Usuario = $row['Usuario'];
            $Dias_Solicitados = $row['Dias_Solicitados'];
            $Antiguedad = $row['Antiguedad'];
        }
        ?>
        <div class="row">
            <!-- User Information Card -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Información Del Empleado
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?php echo  $Usuario; ?></p>
                                <p><strong>Antigüedad:</strong> <?php echo $Antiguedad; ?></p>
                                <p><strong>Años de Servicio:</strong> <?php echo $anios; ?> Años, <?php echo $meses; ?> Meses</p>
                                <p><strong>Periodo:</strong> Del <?php echo $inicioPeriodo->format('Y-m-d'); ?> al <?php echo $finPeriodo->format('Y-m-d'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Dias Restantes:</strong> <?php echo $row['Dias_Restantes']; ?></p>
                                <p><strong>Dias Solicitados:</strong> <?php echo $row['Dias_Solicitados']; ?></p>
                                <p><strong>Dias Correspondientes:</strong> <?php echo $Dias_Restantes; ?></p>
                                <p><strong>Permisos Especiales: </strong><?php echo $DiasDePermiso_Total; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    /*
    echo "Dias Correspondientes: " . $Dias_Restantes . "<br>";
    echo "Dias Usados: " . $DiasDeVacaciones_Total . "<br>";
    echo "Dias Restantes: " . ($Dias_Restantes - $DiasDeVacaciones_Total) . "<br>";
*/
    function actualizarResumenVacaciones(mysqli $conn, string $usuario, array $diasFeriados, DateTime $inicioPeriodo, DateTime $finPeriodo): void
    {
        $sql = "SELECT Fecha_Inicio, Fecha_Fin, Tipo_Permiso, Estado 
            FROM vacaciones_solicitudes 
            WHERE Usuario = ? AND Estado = 'Aprobada'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $diasVacaciones = 0;
        $diasPermiso = 0;

        while ($row = $result->fetch_assoc()) {
    $inicioSolicitud = new DateTime($row['Fecha_Inicio']);
    $finSolicitud = new DateTime($row['Fecha_Fin']);

    // Normalizamos formato (solo fecha)
    $inicioSolicitud->setTime(0, 0, 0);
    $finSolicitud->setTime(0, 0, 0);
    $inicioPeriodo->setTime(0, 0, 0);
    $finPeriodo->setTime(0, 0, 0);

   // echo "<br>------------------------------";
    //echo "<br> Solicitud: " . $inicioSolicitud->format('Y-m-d') . " -> " . $finSolicitud->format('Y-m-d');
    // echo "<br> Periodo:   " . $inicioPeriodo->format('Y-m-d') . " -> " . $finPeriodo->format('Y-m-d');

    // Debug de condición
    $cond1 = ($inicioSolicitud <= $finPeriodo);
    $cond2 = ($finSolicitud >= $inicioPeriodo);

    //echo "<br> Cond1 (inicioSolicitud <= finPeriodo): " . ($cond1 ? "✅ true" : "❌ false");
    //echo "<br> Cond2 (finSolicitud >= inicioPeriodo): " . ($cond2 ? "✅ true" : "❌ false");

    if ($cond1 && $cond2) {
        //echo "<br>👉 ENTRA en el conteo";
        $dias = contarDiasHabiles(
            max($inicioSolicitud, $inicioPeriodo)->format('Y-m-d'),
            min($finSolicitud, $finPeriodo)->format('Y-m-d'),
            $diasFeriados
        );

        if ($row['Tipo_Permiso'] == 'Vacaciones') {
            $diasVacaciones += $dias;
        } elseif ($row['Tipo_Permiso'] == 'Permiso') {
            $diasPermiso += $dias;
        }
    } else {
        //echo "<br>🚫 NO entra en el conteo";
    }
}


        // Obtener los días totales asignados
        $consulta = "SELECT Dias_Restantes FROM vacaciones_general WHERE Usuario = ?";
        $stmt2 = $conn->prepare($consulta);
        $stmt2->bind_param("s", $usuario);
        $stmt2->execute();
        $res = $stmt2->get_result()->fetch_assoc();
        $diasTotales = $res['Dias_Restantes'] ?? 0;

        $diasRestantes = $diasTotales - $diasVacaciones;

        // Actualiza resumen
        $update = "UPDATE vacaciones_general 
               SET Dias_Solicitados = ?, Dias_Restantes = ?
               WHERE Usuario = ?";
        $stmt3 = $conn->prepare($update);
        $stmt3->bind_param("iis", $diasVacaciones, $diasRestantes, $usuario);
        $stmt3->execute();
    }


    ?>

    <!-- Modal Para solicitar Vacaciones -->
    <div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1" aria-labelledby="modalSolicitarVacacionesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formVacaciones" method="POST" action="../Back/Solicitar_vacaciones.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSolicitarVacacionesLabel">Formulario de Vacaciones</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fecha_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="fechaFinal" class="form-label">Fecha Final</label>
                            <input type="date" class="form-control" id="fechaFinal" name="fecha_final" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipoPermiso" class="form-label">Tipo de Permiso</label>
                            <select class="form-select" id="tipoPermiso" name="tipo_permiso" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Permiso Especial">Permiso Especial</option>
                                <option value="Vacaciones">Vacaciones</option>
                            </select>
                        </div>
                        <?php 
                            if($HumanR){
                                // Agregar Select con estados: Proceso, Rechazada y Aprobada
                                ?>
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado (Recursos Humanos)</label>
                                    <select class="form-select" id="estado" name="estadorh">
                                        <option value="">Seleccione una opción</option>
                                        <option value="Proceso">Proceso</option>
                                        <option value="Rechazada">Rechazada</option>
                                        <option value="Aprobada">Aprobada</option>
                                    </select>
                                </div>

                                <?php
                            }
                        ?>
                        <input class="form-control" type="hidden" name="nombre_solicitante" value="<?php echo $Nombre_Consulta; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-aceptar" data-loading-text="Procesando...">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        //echo "<br> Dias Solicitados: " . $DaysUsed ;

        if ($ver == 'actuales') {
            /// Actualizar la tabla vacaciones_general con la información de Días Restantes y Dias solicitados
            $UPDATE = "UPDATE vacaciones_general SET Dias_Restantes = $DaysRestantes, Dias_Solicitados = $DaysUsed WHERE Usuario = '$Nombre_Consulta'";
            $result = mysqli_query($conn, $UPDATE);
            if ($result) {
                //echo "<small>Información actualizada correctamente.</small>";
            } else {
                echo "Error al actualizar la tabla: " . mysqli_error($conn);
            }
        }
        ?>

    </div>

    <!-- Modal para Editar Fechas (RECURSOS HUMANOS) -->
    <div class="modal fade" id="editarFechasModal" tabindex="-1" aria-labelledby="editarFechasModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editarFechasModalLabel">✏️ Editar Fechas de Permiso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../Back/actualizar_Fechas.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_vacaciones" id="modalIdVacaciones">
                        <input type="hidden" name="nombreSolicitante" value="<?php echo $Nombre_Consulta; ?>">

                        <div class="mb-3">
                            <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicioRH" name="fecha_inicio" required>
                        </div>

                        <div class="mb-3">
                            <label for="fechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFinRH" name="fecha_fin" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript para cargar los datos en el modal DE eDICIón para Recursos Humanos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editarModal = document.getElementById('editarFechasModal');

            editarModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;

                // Extraer datos del botón
                document.getElementById('modalIdVacaciones').value = button.getAttribute('data-id-RH');
                document.getElementById('fechaInicioRH').value = button.getAttribute('data-fecha-inicio-RH');
                document.getElementById('fechaFinRH').value = button.getAttribute('data-fecha-fin-RH');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Llenar el modal al dar clic en "Editar"
            document.querySelectorAll('.editarVacaciones').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('editIdVacaciones').value = this.dataset.id;
                    document.getElementById('editFechaInicio').value = this.dataset.fechaInicio;
                    document.getElementById('editFechaFin').value = this.dataset.fechaFin;
                    document.getElementById('editTipoPermiso').value = this.dataset.tipoPermiso;
                });
            });

            document.querySelectorAll('.eliminarVacaciones').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nombre = this.dataset.nombre; // 👈 obtenemos el nombre correctamente

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('../Back/Eliminar_Vacaciones.php?id=' + id + '&Nombre=' + encodeURIComponent(nombre))
                                .then(res => res.text())
                                .then(data => {
                                    console.log('Respuesta del back:', data);
                                    Swal.fire('Eliminado', 'El registro ha sido eliminado.', 'success')
                                        .then(() => location.reload());
                                })
                                .catch(err => Swal.fire('Error', 'No se pudo eliminar el registro.', 'error'));
                        }
                    });
                });
            });



        });
    </script>

    <script>
        /// Animación para evitar que se den muchos clicks en el submit:
        document.addEventListener("DOMContentLoaded", function() {
            const botonesAceptar = document.querySelectorAll(".btn-aceptar");

            botonesAceptar.forEach(boton => {
                boton.addEventListener("click", function(e) {
                    if (boton.classList.contains("disabled")) {
                        e.preventDefault(); // Evita doble click
                        return;
                    }
                    boton.classList.add("disabled");
                    boton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...`;
                });
            });
        });
    </script>
</body>

</html>

<?php
function contarDiasHabiles($fechaInicio, $fechaFin, $feriados = [])
{
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin);
    $fin->modify('+1 day');
    $intervalo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
    $diasHabiles = 0;
    foreach ($intervalo as $fecha) {
        $diaSemana = $fecha->format('N');
        $fechaStr = $fecha->format('Y-m-d');
        if ($diaSemana < 6 && !in_array($fechaStr, $feriados)) {
            $diasHabiles++;
        }
    }
    return $diasHabiles;
}
?>