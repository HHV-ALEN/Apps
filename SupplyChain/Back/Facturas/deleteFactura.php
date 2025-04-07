<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
//print_r($_POST);
$Fecha_Hoy = date("Y-m-d H:i:s");
$Responsable = $_SESSION['Name'];
$Id_Salida = $_GET['Id_Salida'];
$Id_Factura = $_GET['Id_Factura'];
$Archivo = $_GET['Archivo'];

$dir = '../Files/Facturas/';

echo "<strong>Id Salida: </strong>" . $Id_Salida ;
echo "<br> <strong>Id Factura: </strong>" . $Id_Factura;
echo "<br> <strong>Archivo: </strong>" . $Archivo;
/// Limpiar los Campos "Id_Factura" y "Archivo" de la tabla entregas

$sql = "UPDATE entregas SET Id_Factura = NULL, Archivo = 'N/A' WHERE Id_Salida = '$Id_Salida' AND Id_Factura = '$Id_Factura'";
$query = mysqli_query($conn, $sql);
if ($query) {
    echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
    // Registrar en la bitacora
    $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la factura $Id_Factura', '$Fecha_Hoy', '$Responsable')";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se registró correctamente en la tabla de bitácora";

       
        if ( unlink($dir. $Archivo)) {
            echo "<br>El archivo se eliminó correctamente";
        } else {
            echo "<br>No se pudo eliminar el archivo";
        }




        // Regresar al Listado 
        header("Location: ../../Front/detalles.php?id=".$Id_Salida);




    } else {
        echo "<br>No se pudo registrar en la tabla de bitácora";
    }
} else {
    echo "<br>No se pudieron limpiar los campos Id_Factura y Archivo de la tabla entregas";
}


?>