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
$Id = $_GET['Id_Contenido'];
$Id_Salida = $_GET['Id_Salida'];

echo "<br> <strong>Id_Entrega: </strong>" . $Id;
echo "<br> <strong>Id_Salida: </strong>" . $Id_Salida;
echo "<br> <strong>Folio_Orden_Venta: </strong>" . $Folio_Orden_Venta;
echo "<br> <strong>Folio_Entrega: </strong>" . $Folio_Entrega;
echo "<br> <strong>Partida: </strong>" . $Partida;
echo "<br> <strong>Cliente_Nombre: </strong>" . $Cliente_Nombre;
// ----------------------------------------------------------------------

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

/// Verificar si el nombre del cliente es igual al Nombre_Cliente de la tabla salidas, si no es igual, se actualiza
$query_cliente_salida = "SELECT Nombre_Cliente FROM salidas WHERE Id = '$Id_Salida'";
$result_cliente_salida = $conn->query($query_cliente_salida);
if ($result_cliente_salida->num_rows > 0) {
    $row_cliente_salida = $result_cliente_salida->fetch_assoc();
    $Nombre_Cliente_Salida = $row_cliente_salida['Nombre_Cliente'];
} else {
    echo "No se encontró el cliente en la tabla salidas.";
}

if ($Nombre_Cliente_Salida != $Cliente_Nombre) {
    // Actualizar el nombre del cliente en la tabla salidas
    $query_update_cliente = "UPDATE salidas SET Nombre_Cliente = '$Cliente_Nombre', Id_Cliente = '$Id_Cliente' WHERE Id = '$Id_Salida'";
    if ($conn->query($query_update_cliente) === TRUE) {
        echo "<br> <strong>Nombre de cliente actualizado correctamente en la tabla salidas</strong>";
    } else {
        echo "<br> <strong>Error al actualizar el nombre de cliente en la tabla salidas: </strong>" . $conn->error;
    }
} else {
    echo "<br> <strong>El nombre del cliente es igual al de la tabla salidas</strong>";
}

$Update_query = "UPDATE entregas SET Id_Orden_Venta = '$Folio_Orden_Venta', Id_Entrega = '$Folio_Entrega', Partida = '$Partida', Id_Cliente = '$Id_Cliente', Cliente_Nombre = '$Cliente_Nombre' WHERE Id = '$Id' AND Id_Salida = '$Id_Salida'";
if ($conn->query($Update_query) === TRUE) {
    echo "<br> <strong>Actualización exitosa</strong>";
    /// Insertar en la bitacora 
    $query_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$Id_Salida', 'Actualizacion de Entrega', '$Fecha', '$Responsable')";
    $result_bitacora = mysqli_query($conn, $query_bitacora);
    if ($result_bitacora) {
        echo "<br> <strong>Bitacora actualizada correctamente</strong>";
    } else {
        echo "<br> <strong>Error al actualizar la bitacora: </strong>" . $conn->error;
    }
} else {
    echo "<br> <strong>Error al actualizar: </strong>" . $conn->error;
}


header("Location: ../../Front/detalles.php?id=$Id_Salida");
$conn->close();



?>