<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
print_r($_GET);

$Id_Salida = $_GET['Id_Salida'];
$Id_Contenido = $_GET['Id_Contenido'];
$firstname = $_SESSION['Name'];
$Fecha_Actual = date('Y-m-d H:i:s');

echo "<br>Información General --------------------";
echo "<br> Nombre: ". $firstname;
echo "<br> Id Salida: ". $Id_Salida;
echo "<br> Id Contenido: ". $Id_Contenido;
//convertir a int
$int_id_contenido = intval($Id_Contenido);

/// Eliminar de la base de datos 

$sql_eliminar = "DELETE FROM contenido WHERE Id = $int_id_contenido";
$query_eliminar = mysqli_query($conn, $sql_eliminar);

if($query_eliminar){
    echo "<br>Contenido Eliminado Correctamente";
    echo "<br>Filas afectadas: " . mysqli_affected_rows($conn);

    // Registro en Bitacora 
    $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$Id_Salida', 'Eliminación de Contenido', '$Fecha_Actual', '$firstname');";
    $query_bitacora = mysqli_query($conn, $sql_bitacora);
    if($query_bitacora){
        echo "<br>Registro en Bitacora Exitoso";

        header ("Location: ../../Front/detalles.php?id=".$Id_Salida);
    }else{
        echo "<br>Error al Registrar en Bitacora";
    }

}else{
    echo "<br>Error al Eliminar el Contenido";
}



?>