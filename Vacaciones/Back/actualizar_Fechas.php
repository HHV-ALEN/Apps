<?php 
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$id_vacaciones = $_POST['id_vacaciones'];
$nombreSolicitante = $_POST['nombreSolicitante'];
$Fecha_Inicio = $_POST['fecha_inicio'];
$Fecha_Fin = $_POST['fecha_fin'];

echo "<h1>Información del formulario: </h1>";
echo "<br><strong>Id Vacaciones: </strong>" . $id_vacaciones;
echo "<br><strong>nombre Solicitante: </strong>" . $nombreSolicitante;
echo "<br><strong>Fecha Inicio: </strong>" . $Fecha_Inicio;
echo "<br><strong>Fecha Fin: </strong>" . $Fecha_Fin;

/// Actualizar tabla vacaciones_solicitudes en donde el Id sea = $id_vacaciones y
/// Actualizar los campos Fecha_Inicio y Fecha_Fin
// Query directo

$sql = "UPDATE vacaciones_solicitudes 
        SET Fecha_Inicio = '$Fecha_Inicio', 
            Fecha_Fin = '$Fecha_Fin' 
        WHERE Id = $id_vacaciones";

if(mysqli_query($conn, $sql)){
    $_SESSION['Mensaje'] = "Fechas actualizadas";
    $_SESSION['Tipo_Mensaje'] = "success";
} else {
    $_SESSION['Mensaje'] = "Error al actualizar";
    $_SESSION['Tipo_Mensaje'] = "danger";
}


header ("Location: ../Front/detalles.php?Nombre=$nombreSolicitante");



?>