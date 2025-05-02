<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$Nombre = $_GET['Nombre'];
$Id = $_POST['id'];
$FechaInicio = $_POST['fecha_inicio'];
$FechaFin = $_POST['fecha_fin'];

echo "<h3>Datos de la Solicitud: </h3>";
echo "<br><strong>- Nombre</strong>: " . $Nombre;
echo "<br><strong>- ID</strong>: " . $Id;
echo "<br><strong>- Fecha Inicio</strong>: " . $FechaInicio;
echo "<br><strong>- Fecha Fin</strong>: " . $FechaFin;


// Actualizar información de la solicitud:
$sql_update = "UPDATE vacaciones_solicitudes SET Fecha_Inicio = '$FechaInicio', Fecha_Fin = '$FechaFin' WHERE ID = $Id";
$result_update = $conn->query($sql_update);

if ($result_update) {
    echo "<br><strong>Solicitud actualizada correctamente.</strong>";
    $_SESSION['Mensaje'] = "Solicitud actualizada correctamente.";
    $_SESSION['Tipo_Mensaje'] = "success";

} else {
    echo "<br><strong>Error al actualizar la solicitud:</strong> " . $conn->error;
    $_SESSION['Mensaje'] = "Error al actualizar la solicitud: " . $conn->error;
    $_SESSION['Tipo_Mensaje'] = "error";
}

header ("Location: ../Front/detalles.php?Nombre=$Nombre");





?>