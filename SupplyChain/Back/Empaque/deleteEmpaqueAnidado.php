<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$firstname = $_SESSION['Name'];
$id_salida = $_GET['Id_Salida'];
$id_Contenido = $_GET['Id_Contenido'];
$Fecha_Actual = date("Y-m-d H:i:s");


echo "<br><strong>Información del form: </strong>";
echo "<br> Id Salida: " . $id_salida;
echo "<br> Id Contenido: " . $id_Contenido;
echo "<br> Nombre: " . $firstname;
echo "<br> Fecha Actual: " . $Fecha_Actual;
echo "<br><strong>Información de la preguía:</strong>";

// Eliminar el contenido 
$delete_query = "DELETE FROM contenido WHERE Id = $id_Contenido AND Id_Salida = $id_salida";
$result = mysqli_prepare($conn, $delete_query);
mysqli_stmt_execute($result);
if ($result) {
    echo "<br>Contenido eliminado correctamente.";
} else {
    echo "<br>Error al eliminar el contenido: " . mysqli_error($conn);
}

// Registrar eliminación en bitacora
$bitacora_query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
VALUES ('$id_salida', 'Eliminación de Empaque', '$Fecha_Actual', '$firstname')";
$result_bitacora = mysqli_prepare($conn, $bitacora_query);
mysqli_stmt_execute($result_bitacora);
if ($result_bitacora) {
    echo "<br>Registro de eliminación en bitácora exitoso.";
} else {
    echo "<br>Error al registrar la eliminación en bitácora: " . mysqli_error($conn);
}


header("Location: ../../Front/detalles.php?id=$id_salida");




?>