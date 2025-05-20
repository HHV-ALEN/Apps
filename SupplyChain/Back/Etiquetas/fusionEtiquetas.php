<?php
date_default_timezone_set('America/Mexico_City');
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
echo "<br>Id Salida <strong>Base</strong>: $id_salida_actual <br>";
echo "Id Salida Que se va a Fusionar: $Salida_Anidada_Id <br>";
echo "Cliente Actual: $Cliente_Actual <br>";

echo "<br> --------------------------------------------------<br>";
echo "Fecha: $Fecha <br>";
echo "Responsable: $Nombre_Responsable <br>";
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

// Obtener información de la entrega relacionada a la salida a fusionar
$query = "SELECT * FROM entregas WHERE Id_Salida = '$Salida_Anidada_Id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
$Id_Orden_Venta = $row['Id_Orden_Venta'];
$Id_Entrega = $row['Id_Entrega'];
$Partida = $row['Partida'];
$Id_Factura = $row['Id_Factura'];
$Archivo = $row['Archivo'];
echo "<br> Información de la Entrega: <br>";
echo "Id Orden Venta: $Id_Orden_Venta <br>";
echo "Id Entrega: $Id_Entrega <br>";
echo "Partida: $Partida <br>";
echo "Id Factura: $Id_Factura <br>";
echo "Archivo: $Archivo <br>";
echo "<br>--------------------------------------------------<br>";

$Fusion_Query = "INSERT INTO etiquetas_fusionadas (Salida_Base, Id_Relacion_Salida, Cliente_Id, Cliente_Nombre, Responsable, Fecha, Orden_Venta, Entrega, Partida, Id_Factura, Archivo)
    VALUES ('$id_salida_actual', '$Salida_Anidada_Id', '$Id_Cliente', '$nombre_cliente', '$Nombre_Responsable', '$Fecha', '$Id_Orden_Venta', '$Id_Entrega', '$Partida', '$Id_Factura', '$Archivo')";
$Fusion_Result = mysqli_query($conn, $Fusion_Query);
if (!$Fusion_Result) {
    die('Query Failed fusion_etiquetas.');
} else {
    echo "Se insertó correctamente en la tabla etiquetas_fusionadas <br>";
}

// Eliminar la Salida Original
$Delete_Query = "DELETE FROM salidas WHERE Id = '$Salida_Anidada_Id'";
$Delete_Result = mysqli_query($conn, $Delete_Query);
if (!$Delete_Result) {
    die('Query Failed delete_salidas.');
} else {
    echo "Se eliminó correctamente la salida anidada <br>";
}

$Bitacora_Query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$id_salida_actual', 'Fusion de la Salida: $Salida_Anidada_Id', '$Fecha', '$Nombre_Responsable')";
$Bitacora_Result = mysqli_query($conn, $Bitacora_Query);
if (!$Bitacora_Result) {
    die('Query Failed bitacora.');
} else {
    echo "Se insertó correctamente en la tabla bitacora <br>";
}

header("Location: ../../Front/detalles.php?id=" . $id_salida_actual);
