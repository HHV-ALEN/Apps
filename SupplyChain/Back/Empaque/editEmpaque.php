<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
print_r($_POST);

$Id_Salida = $_POST['Id_Salida'];
$Id_Contenido = $_POST['Id_Contenido'];
$firstname = $_SESSION['Name'];
$contenedor = $_POST['contenedor'];
$otro_contenedor = $_POST['otro_contenedor'];
$Cantidad = $_POST['Cantidad'];
$Id_Salida = $_POST['Id_Salida'];

$Fecha_Actual = date('Y-m-d H:i:s');

echo "<br>Información General --------------------";
echo "<br> Nombre: ". $firstname;
echo "<br> Id Contenido: ". $Id_Contenido;
echo "<br> <strong>Información del formulario: </strong>";
echo "<br> - Contenedor: " . $contenedor;
echo "<br> - Otro Contenedor: " . $otro_contenedor;
echo "<br> - Cantidad: " . $Cantidad;

/* Actualizar tabla contenido
donde Id = $Id_Contenido, Id_Salida = $Id_Salida, Actualizar Cantidad = $Cantidad y Contenedor = $contenedor o  $otro_contenedor
cuando la variable $otro_contenedor no esté vacía
*/

if ($contenedor == 'Otro') {
    echo "<br> Actualizacion con difente Contenedor a los listados: ";
    $sql_actualizar = "UPDATE contenido SET Cantidad = $Cantidad, Contenedor = '$otro_contenedor' WHERE Id = $Id_Contenido AND Id_Salida = $Id_Salida";
} else {
    echo "<br> Actualizacion con Contenedor en los listados: ";
    $sql_actualizar = "UPDATE contenido SET Cantidad = $Cantidad, Contenedor = '$contenedor' WHERE Id = $Id_Contenido AND Id_Salida = $Id_Salida";
}

$query_actualizar = mysqli_query($conn, $sql_actualizar);

if($query_actualizar){
    echo "<br>Contenido Actualizado Correctamente";
    // Registro en Bitacora
    $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$Id_Salida', 'Actualización de Contenido . $Id_Contenido', '$Fecha_Actual', '$firstname');";
    $query_bitacora = mysqli_query($conn, $sql_bitacora);
    if($query_bitacora){
        echo "<br>Registro en Bitacora Exitoso";
         header("Location: ../../Front/detalles.php?id=".$Id_Salida );
    } else {
        echo "<br>Error al registrar en Bitacora";
    }
} else {
    echo "<br>Error al actualizar contenido";
}

header("Location: ../../Front/detalles.php?id=".$Id_Salida);

?>