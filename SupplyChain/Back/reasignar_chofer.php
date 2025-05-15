<?php 
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

/// Actualizar el Chofer de la entrega
print_r($_POST);
$Id_Salida = $_POST['Id_Salida'];
$nuevo_chofer = $_POST['nuevo_chofer'];

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

/// Actualizar el chofer en la tabla 
$update_query = "UPDATE preguia SET Chofer = '$nombre_chofer' WHERE Id_Salida = $Id_Salida";
if ($conn->query($update_query) === TRUE) {
      $_SESSION['success_message'] = "El chofer de la salida $Id_Salida ha sido actualizado a: $nombre_chofer";
} else {
    $_SESSION['error_message'] = "❌ Error al actualizar el chofer: " . $conn->error;
}

// Redirigir al index
header("Location: ../index.php");

?>