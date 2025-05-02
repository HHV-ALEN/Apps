<?php 
include '../../../Back/config/config.php';
$conn = connectMySQLi();
session_start(); // ¡No olvides esto!

$id = $_GET['id'];
echo "<strong>Id: </strong>" . $id . "<br>";

// Desactivar el usuario en la base de datos
$sql = "UPDATE usuarios SET Estado='Inactivo' WHERE Id='$id'";
$result = $conn->query($sql);

if ($result) {
    echo "Usuario desactivado correctamente.";
    $_SESSION['respuesta'] = "✅ El usuario fue desactivado correctamente.";
    $_SESSION['tipo_respuesta'] = "success";
} else {
    echo "Error al desactivar el usuario: " . $conn->error;
    $_SESSION['respuesta'] = "❌ Error al desactivar el usuario. Intenta nuevamente.";
    $_SESSION['tipo_respuesta'] = "danger";
}

header ("Location: ../Users.php");



?>