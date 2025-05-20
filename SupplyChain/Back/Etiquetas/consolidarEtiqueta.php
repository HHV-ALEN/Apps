<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
print_r($_SESSION);
echo "<hr>";

$Id_Salida_Base = $_GET['id_salida'];
$Nombre = $_SESSION['Name'];
$fecha = date("Y-m-d H:i:s");
$Salida = $_POST['Salida_Destino'];
$Destino = $_POST['Destino'];

echo "<br> Información del formulario ";
echo "<br><strong>Id_Salida_Base</strong> " . $Id_Salida_Base;
echo "<br><strong>Nombre</strong> " . $Nombre;
echo "<br><strong>Fecha</strong> " . $fecha;
echo "<br><strong>Salida</strong> " . $Salida;
echo "<br><strong>Destino</strong> " . $Destino;

echo "<br>--------------------------------------------------------------- <br>";
// Obtener información de la entrega relacionada
$sql = "SELECT * FROM entregas WHERE Id_Salida = '$Salida'";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($query);
$Id_Orden_Venta = $row['Id_Orden_Venta'];
$Id_Entrega = $row['Id_Entrega'];
$Partida = $row['Partida'];
$Id_Factura = $row['Id_Factura'];
$Archivo = $row['Archivo'];
$Id_Cliente = $row['Id_Cliente'];
$Cliente_Nombre = $row['Cliente_Nombre'];
echo "<br> Información de la Entrega: <br>";
echo "Id Orden Venta: $Id_Orden_Venta <br>";
echo "Id Entrega: $Id_Entrega <br>";
echo "Partida: $Partida <br>";
echo "Id Factura: $Id_Factura <br>";
echo "Archivo: $Archivo <br>";
echo "Id Cliente: $Id_Cliente <br>";
echo "Nombre Cliente: $Cliente_Nombre <br>";

echo "<br>--------------------------------------------------------------- <br>";

// Insertar en la tabla de consolidados
$sql_consolidado = "INSERT INTO consolidados (Id_Base, Id_salida_consolidada, Destino, Id_Cliente, Nombre_Cliente, Id_Entrega, Estado, Fecha, Orden_Venta, Partida, Id_Factura, Archivo)
VALUES ('$Id_Salida_Base', '$Salida', '$Destino', '$Id_Cliente', '$Cliente_Nombre', '$Id_Entrega', 'Pendiente', '$fecha', '$Id_Orden_Venta', '$Partida', '$Id_Factura', '$Archivo')";
$query_consolidado = mysqli_query($conn, $sql_consolidado);
if ($query_consolidado) {
    echo "<br><strong> Registro en tabla consolidados para el Id_Entrega</strong> " . $Id_Entrega . " <strong>Exitoso</strong>";
} else {
    echo "<br><strong> Registro en tabla consolidados para el Id_Entrega</strong> " . $Id_Entrega . " <strong>Fallido</strong>";
}

// Now build the query with proper concatenation
$sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) 
        VALUES ('$Id_Salida_Base', 'Consolidación de la Salida $Salida', '$fecha', '$Nombre')";
$query_Bitacora = mysqli_query($conn, $sql_Bitacora);
if ($query_Bitacora) {
    echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Exitoso</strong>";
} else {
    echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Fallido</strong>";
}

// Eliminar la Salida Original 
$Delete_Query = "DELETE FROM salidas WHERE Id = '$Salida'";
$Delete_Result = mysqli_query($conn, $Delete_Query);
if ($Delete_Result) {
    echo "<br><strong> Eliminación de la Salida Original</strong> " . $Salida . " <strong>Exitosa</strong>";
} else {
    echo "<br><strong> Eliminación de la Salida Original</strong> " . $Salida . " <strong>Fallida</strong>";
}

// Now build the query with proper concatenation
$sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) 
        VALUES ('$Id_Salida_Base', 'Consolidación de la Salida $Salida', '$fecha', '$Nombre')";
$query_Bitacora = mysqli_query($conn, $sql_Bitacora);
if ($query_Bitacora) {
    echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Exitoso</strong>";
} else {
    echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Fallido</strong>";
}



 header("Location: ../../Front/detalles.php?id=" . $Id_Salida_Base);
