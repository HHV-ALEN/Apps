<?php
include("../../Back/config/config.php"); // --> Conexión con Base de datos
session_start(); // --> Iniciar sesión
$conn = connectMySQLi();
$conn = connectMySQLi();
$Nombre_Consulta = $_GET['Nombre'] ?? null;
$ver = isset($_GET['ver']) ? $_GET['ver'] : 'actuales';
/// Actualizar Información de la tabla vacaciones_general
$NombreEncoded = urlencode($Nombre_Consulta); // por si tiene espacios o acentos

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
        
        // Información General de la Persona:
        
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
            } 
            elseif ($anios >= 6 && $anios <= 10) {
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

                                if ($_SESSION['Name'] == $Nombre_Consulta) {
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
                            $Dias_Feriados = [
                                "2025-01-01",
                                "2025-05-01",
                                "2025-12-25",
                            ];

                            $DiasDeVacaciones_Total = 0;
                            $DiasDePermiso_Total = 0;

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
                                    $Id_vacaciones = $row['Id'];
                                    $Fecha_Inicio = $row['Fecha_Inicio'];
                                    $Fecha_Fin = $row['Fecha_Fin'];
                                    $Tipo_Permiso = $row['Tipo_Permiso'];
                                    $Estado = $row['Estado'];
                                    $Fecha_Solicitud = $row['Fecha_Solicitud'];

                                    $inicioSolicitud = new DateTime($Fecha_Inicio);
                                    $finSolicitud = new DateTime($Fecha_Fin);

                                    if ($ver == 'actuales' && $inicioSolicitud <= $finPeriodo && $finSolicitud >= $inicioPeriodo) {
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $Dias_Feriados);

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
                                        <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>
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
                                                data-id="' . $Id_vacaciones . '"
                                                data-nombre="' . $Nombre_Consulta . '">
                                                <i class="bi bi-trash3-fill"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>';
                                    
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
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $Dias_Feriados);

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
                                            <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>
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
                                $UPDATE = "UPDATE vacaciones_general SET Dias_Restantes = '$RESULT', Dias_Solicitados = '$Variable_Helper' WHERE Usuario = 'Nombre_Consulta'";
                                $result = mysqli_query($conn, $UPDATE);
                                if ($result) {
                                    echo "<small>Información actualizada correctamente.</small>";
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
                                                        echo $Dias_Restantes - $DiasDeVacaciones_Total;

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
                            $Dias_Feriados = [
                                "2025-01-01",
                                "2025-05-01",
                                "2025-12-25",
                            ];

                            $DiasDeVacaciones_Total = 0;
                            $DiasDePermiso_Total = 0;

                            $query = "SELECT * FROM vacaciones_solicitudes WHERE Usuario = 'Nombre_Consulta' AND Tipo_Permiso = 'Permiso Especial' AND Estado != 'Inactivo'";
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
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $Dias_Feriados);

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
                                        <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>
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
                                        $Dias_Habiles = contarDiasHabiles($Fecha_Inicio, $Fecha_Fin, $Dias_Feriados);

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
                                            <td><span class="badge ' . $badgeClass . '">' . $Estado . '</span></td>
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
    //echo "Dias Correspondientes: " . $Dias_Restantes . "<br>";
    //echo "Dias Usados: " . $DiasDeVacaciones_Total . "<br>";
    //echo "Dias Restantes: " . ($Dias_Restantes - $DiasDeVacaciones_Total) . "<br>";

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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-aceptar" data-loading-text="Procesando...">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    btn.addEventListener('click', function () {
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