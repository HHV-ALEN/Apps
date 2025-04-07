<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos

$conn = connectMySQLi();
session_start();
print_r($_SESSION);
echo "<hr>";
$Fecha = date('Y-m-d H:i:s');
$Cliente_Actual = $_POST['Cliente_Actual'];
$id_salida_actual = $_POST['id_salida_actual'];
$Salida_Anidada_Id = $_POST['Salida_Anidada_Id'];
$Nombre_Responsable = $_SESSION['Name'];
echo "<br> Información del Envio Actual: <br>";
echo "Responsable: $Nombre_Responsable <br>";
echo "Cliente Actual: $Cliente_Actual <br>";
echo "Id Salida Actual: $id_salida_actual <br>";
echo "Salida Anidada Id: $Salida_Anidada_Id <br>";
echo "Fecha: $Fecha <br>";
echo "<br>--------------------------------------------------<br>";


/// dATOS PARA LA INSERCIÓN
/*
Salida_Base, Id_Relacion_Salida, Cliente_Id, Cliente_Nombre, Responsable, Fecha
*/

// Obtener datos del cliente actual
$query = "SELECT * FROM clientes WHERE Nombre = '$Cliente_Actual'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
echo "<br> Información del Cliente Actual: <br>";
$Id_Cliente = $row['Id'];
$nombre_cliente = $row['Nombre'];
echo "Id Cliente: $Id_Cliente <br>";
echo "Nombre Cliente: $nombre_cliente <br>";
echo "<br>--------------------------------------------------<br>";

// Salida Base = id_salida_actual
// Id_Relacion_Salida = Salida_Anidada_Id
// Cliente_Id = Id_Cliente
// Cliente_Nombre = nombre_cliente
// Responsable = firstname
// Fecha = Fecha

$Fusion_Query = "INSERT INTO etiquetas_fusionadas (Salida_Base, Id_Relacion_Salida, Cliente_Id, Cliente_Nombre, Responsable, Fecha)
 VALUES ('$id_salida_actual', '$Salida_Anidada_Id', '$Id_Cliente', '$nombre_cliente', '$Nombre_Responsable', '$Fecha')";
$Fusion_Result = mysqli_query($conn, $Fusion_Query);
if (!$Fusion_Result) {
    die('Query Failed fusion_etiquetas.');
} else {
    echo "Se insertó correctamente en la tabla etiquetas_fusionadas <br>";
    // Actualizar Bitacora
    $Bitacora_Query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida_actual', 'Base: $id_salida_actual - Fusionado con: $Salida_Anidada_Id', '$Fecha', '$Nombre_Responsable')";
    $Bitacora_Result = mysqli_query($conn, $Bitacora_Query);
    if (!$Bitacora_Result) {
        die('Query Failed Bitacora.');

    } else {
        echo "Se insertó correctamente en la tabla Bitacora<br>";
        header("Location: ../../Front/detalles.php?id=$id_salida_actual");
    }
}

?>