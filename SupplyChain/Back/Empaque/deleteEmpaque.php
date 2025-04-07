<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
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

/// Eliminar de la base de datos 

//$sql_eliminar = "DELETE FROM contenido WHERE Id_Salida = $Id_Salida AND Id = $Id_Contenido";


$sql_eliminar = "Delete from  contenido where Id = $Id_Contenido";

$query_eliminar = mysqli_query($conn, $sql_eliminar);

if($query_eliminar){
    echo "<br>Contenido Eliminado Correctamente";
    // Registro en Bitacora 
    $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$Id_Salida', 'Eliminación de Contenido . $Id_Contenido', '$Fecha_Actual', '$firstname');";
    $query_bitacora = mysqli_query($conn, $sql_bitacora);
    if($query_bitacora){
        echo "<br>Registro en Bitacora Exitoso";
        header("Location: ../../Front/detalles.php?id=".$Id_Salida );
    } else {
        echo "<br>Error al registrar en Bitacora";
    }
}

header("Location: ../../Front/detalles.php?id=".$Id_Salida);

?>