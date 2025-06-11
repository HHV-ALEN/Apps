<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
// Mostrar todos los errores:

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conectar a la base de datos
$conn = connectMySQLi();
session_start();
print_r($_GET);

$Id_Consolidado = $_GET['id_consolidado'];
$firstname = $_SESSION['Name'];
$Fecha_Actual = date("Y-m-d H:i:s");

echo "<br> Id Consolidado: " . $Id_Consolidado;

$Sql_Consolidado = "SELECT * FROM consolidados WHERE Id = $Id_Consolidado";
$result = mysqli_query($conn, $Sql_Consolidado);
$row = mysqli_fetch_array($result);
echo "<br> Información del Consolidado: <br>";
$Id_Entrega = $row['Id_Entrega'];
$Orden_Venta = $row['Orden_Venta'];
$Id_Salida_consolidada = $row['Id_salida_consolidada'];
$Id_Salida_Base = $row['Id_Base'];

echo "<br> Id_Salida_Consolidado: " . $Id_Salida_consolidada;
echo "<br> Id_Entrega : " . $Id_Entrega;
echo "<br> Orden Venta : " . $Orden_Venta;

$sql_entrega = "SELECT * FROM entregas WHERE Id_Salida = $Id_Salida_consolidada AND Id_Entrega = $Id_Entrega AND Id_Orden_Venta = $Orden_Venta ";
$result_entrega = mysqli_query($conn, $sql_entrega);
$row_entrega = mysqli_fetch_array($result_entrega);
$Cliente_Nombre = $row_entrega['Cliente_Nombre'];
$Id = $row_entrega['Id'];

echo "<br> Nombre del CLiente: " . $Cliente_Nombre;
echo "<br> ID: " . $Id;

$CorrectFlag = TRUE;

/// Eliminar Consolidado:
$sql_delete_consolidado = "DELETE FROM consolidados WHERE Id = $Id_Consolidado";
$query_eliminar = mysqli_query($conn, $sql_delete_consolidado);
if ($query_eliminar) {
    echo "<br> Consolidado Eliminado";
} else {
    echo "<br> Consolidado NOOOOO Eliminado";
    $CorrectFlag = FALSE;
}



$sql_delete_entrega = "DELETE FROM entregas WHERE Id = $Id";
$query_eliminar = mysqli_query($conn, $sql_delete_entrega);
if ($query_eliminar) {
    echo "<br> Entrega Eliminada";
} else {
    echo "<br> Entrega NOOOOO Eliminada";
    $CorrectFlag = FALSE;
}

if ($CorrectFlag) {
    $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$Id_Salida_Base', 'Eliminación Consolidado - O.V,: $Orden_Venta - Entrega $Id_Entrega', '$Fecha_Actual', '$firstname');";
    $query_bitacora = mysqli_query($conn, $sql_bitacora);
    if ($query_bitacora) {
        echo "<br>Registro en Bitacora Exitoso";
        header("Location: ../../Front/detalles.php?id=" . $Id_Salida_Base);
    } else {
        echo "<br>Error al registrar en Bitacora";
    }
} else {
    echo "<br> No se registro, porque algo malo paso al eliminar";
}
