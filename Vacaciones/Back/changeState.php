<?php 
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
$Id = $_GET['Id'];
$Response = $_GET['Response'];
$Fecha_Inicio = $_GET['Fecha_Inicio'];
$Fecha_Fin = $_GET['Fecha_Fin'];
$Nombre = $_GET['Nombre'];


echo "<br><strong>- Id</strong>: " . $Id;
echo "<br><strong>- Respuesta</strong>: " . $Response;

if ($Response == "Aprobada") {
    $sql_update_vacaciones = "UPDATE vacaciones_solicitudes SET Estado = 'Aprobada' WHERE Id = '$Id'";
    $accion = "Aprobada";
} elseif ($Response == "Rechazado") {
    $sql_update_vacaciones = "UPDATE vacaciones_solicitudes SET Estado = 'Rechazado' WHERE Id = '$Id'";
    $accion = "Rechazada";
} else {
    echo "<br><strong>Respuesta no válida.</strong>";
    exit;
}

$result_update_vacaciones = $conn->query($sql_update_vacaciones);
if ($result_update_vacaciones) {
    $_SESSION['mensaje_alerta'] = "📄 La solicitud #$Id con Fecha de Salida: $Fecha_Inicio y Fecha de Fin: $Fecha_Fin solicitada por $Nombre ha sido <strong>$Response</strong>";
    $_SESSION['accion'] = $accion;
    header("Location: ../Front/listado_revision.php");
    
    exit;
} else {
    echo "<br><strong>Error al actualizar la solicitud de vacaciones: " . $conn->error . "</strong>";
}



?>