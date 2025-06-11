<?php 
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

/// Actualizar el Chofer de la entrega
print_r($_POST);
echo "<br>";

$Id_Salida = $_POST['Id_Salida'];
$nuevo_chofer = $_POST['nuevo_chofer'];
$Paqueteria = $_POST['Paqueteria'];
$otroPaqueteria = $_POST['otroPaqueteria'] ?? NULL;
$Tipo_Flete = $_POST['Tipo_Flete'];
$Metodo_Pago = $_POST['Metodo_Pago'];


echo "<br>DebugShit: ";
echo "<br> Nuevo_Chofer : " . $nuevo_chofer;
echo "<br> Paqueteria " . $Paqueteria;
echo "<br> otroPaqueteria " . $otroPaqueteria;
echo "<br> Tipo_Flete " . $Tipo_Flete;
echo "<br> Metodo_Pago " . $Metodo_Pago;

// Consultar nombre del nuevo chofer
$query = "SELECT Nombre FROM usuarios WHERE Id = $nuevo_chofer";
$response = $conn->query($query);
if ($response->num_rows > 0) {
    $row = $response->fetch_assoc();
    $nombre_chofer = $row['Nombre'];
} else {
    echo json_encode(['error' => 'No se encontró el chofer']);
    exit;
}

echo "<br> Nombre del nuevo chofer: " . $nombre_chofer;


/// Actualizar La información de la preguia

$update_query = "UPDATE preguia SET Chofer = '$nombre_chofer', Paqueteria = '$Paqueteria', Tipo_Flete = '$Tipo_Flete',
Metodo_Pago = '$Metodo_Pago' WHERE Id_Salida = $Id_Salida";
if ($conn->query($update_query) === TRUE) {
      $_SESSION['success_message'] = "El chofer de la salida $Id_Salida ha sido actualizado a: $nombre_chofer";
} else {
    $_SESSION['error_message'] = "❌ Error al actualizar el chofer: " . $conn->error;
}

// Redirigir al index
header("Location: ../index.php");

?>