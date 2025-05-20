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

$Responsable = $_SESSION['Name'];
$Fecha = date("Y-m-d H:i:s");

// --- Post
$Folio_Orden_Venta = $_POST['Folio_Orden'];
$Folio_Entrega = $_POST['Folio_Entrega'];
$Partida = $_POST['Partida'];
$Cliente_Nombre = $_POST['Cliente_Nombre'];
// --- Get
$Id = $_GET['Id_Relacion_Salida'];
$Id_Salida = $_GET['Id_Salida'];

echo "<br> <strong>Id Relacion Salida: </strong>" . $Id;
echo "<br> <strong>Id_Salida_Base: </strong>" . $Id_Salida;
echo "<br> <strong>Folio_Orden_Venta: </strong>" . $Folio_Orden_Venta;
echo "<br> <strong>Folio_Entrega: </strong>" . $Folio_Entrega;
echo "<br> <strong>Partida: </strong>" . $Partida;
echo "<br> <strong>Cliente_Nombre: </strong>" . $Cliente_Nombre;
// -----------------------------------------------------------------

// Obtener el Id cliente
$query_cliente = "SELECT Id FROM clientes WHERE Nombre = '$Cliente_Nombre'";
$result_cliente = $conn->query($query_cliente);
if ($result_cliente->num_rows > 0) {
    $row_cliente = $result_cliente->fetch_assoc();
    $Id_Cliente = $row_cliente['Id'];
} else {
    echo "No se encontró el cliente.";
}

echo "<br> <strong>Id_Cliente: </strong>" . $Id_Cliente;



// Actualizar Tabla entregas en donde Id_Salida = $Id 
$query_entregas_Update = "UPDATE entregas SET Id_Orden_Venta = '$Folio_Orden_Venta', Id_Entrega = '$Folio_Entrega', Partida = '$Partida', Id_Cliente = '$Id_Cliente', Cliente_Nombre = '$Cliente_Nombre' WHERE Id_Salida = '$Id' ";
if ($conn->query($query_entregas_Update) === TRUE) {
    echo "<br> <strong>Datos actualizados correctamente en la tabla entregas</strong>";
} else {
    echo "<br> <strong>Error al actualizar los datos en la tabla entregas: </strong>" . $conn->error;
}
// Actualizar la tabla consolidados
$query_update_consolidados = "UPDATE consolidados SET Id_cliente = '$Id_Cliente', Nombre_Cliente = '$Cliente_Nombre', Orden_Venta = '$Folio_Orden_Venta', Id_Entrega = '$Folio_Entrega', Partida = '$Partida' WHERE Id_salida_consolidada = '$Id' AND Id_Base = '$Id_Salida'";
if ($conn->query($query_update_consolidados) === TRUE) {
    echo "<br> <strong>Datos actualizados correctamente en la tabla consolidados</strong>";
} else {
    echo "<br> <strong>Error al actualizar los datos en la tabla consolidados: </strong>" . $conn->error;
}

header("Location: ../../Front/detalles.php?id=$Id_Salida");
/*

$Query_update_entregas = "UPDATE entregas SET Id_Orden_Venta = '$Folio_Orden_Venta', Id_Entrega = '$Folio_Entrega', Partida = '$Partida', Id_Cliente = '$Id_Cliente', Cliente_Nombre = '$Cliente_Nombre' WHERE Id = '$Id' ";
if ($conn->query($Query_update_entregas) === TRUE) {
    echo "<br> <strong>Datos actualizados correctamente en la tabla entregas</strong>";
} else {
    echo "<br> <strong>Error al actualizar los datos en la tabla entregas: </strong>" . $conn->error;
}

/// Actualizar la tabla etiquetas_fusionadas
$query_update_etiquetas_fusionadas = "UPDATE etiquetas_fusionadas SET Cliente_Id = '$Id_Cliente', Cliente_Nombre = '$Cliente_Nombre', Orden_Venta = '$Folio_Orden_Venta', Entrega = '$Folio_Entrega', Partida = '$Partida' WHERE Id_Relacion_Salida = '$Id' AND Salida_Base = '$Id_Salida'";
if ($conn->query($query_update_etiquetas_fusionadas) === TRUE) {
    echo "<br> <strong>Datos actualizados correctamente en la tabla etiquetas_fusionadas</strong>";
} else {
    echo "<br> <strong>Error al actualizar los datos en la tabla etiquetas_fusionadas: </strong>" . $conn->error;
}


*/


?>