<?php 
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$Id = $_GET['id'];
$Nombre = $_GET['Nombre'];
echo "<br><strong>- ID</strong>: " . $Id;
echo "<br><strong>- Nombre</strong>: " . $Nombre;

// Cambiar el estado a 'Inactivo' en lugar de eliminar el registro
$sql = "UPDATE vacaciones_solicitudes SET Estado = 'Inactivo' WHERE Id = $Id";
$result = $conn->query($sql);
if ($result) {
    echo "<br><strong>Registro actualizado correctamente.</strong>";
} else {
    echo "<br><strong>Error al actualizar el registro:</strong> " . $conn->error;
}

//header ("Location: ../Front/detalles.php?Nombre=$Nombre");


?>