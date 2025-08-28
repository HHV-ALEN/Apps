<?php 
ini_set('memory_limit', '1024M'); // 1GB
require '../../vendor/autoload.php';
include "../../Back/config/config.php";
session_start();

ob_start(); // Inicia el buffer de salida    // Mapeado de tablas de Ventas

$conn = connectMySQLi();
print_r($_POST);
// Array ( [id_compra] => 20 [comentario] => Contenido [fecha_recibimiento] => 2025-08-21 )

$id_compra = $_POST['id_compra'];
$comentario = $_POST['comentario'];
$fecha_recibimiento = $_POST['fecha_recibimiento'];

// Update a la tabla 'compras_lineasabiertas' en los campos Status = $comentario y 
// Fecha_Llegada = $fecha_recibimiento

$query = "UPDATE compras_cargaventas SET Status_Comentario = '$comentario', Fecha_Llegada = '$fecha_recibimiento' WHERE Id = $id_compra";
$resultado = $conn->query($query);
if ($resultado) {
    echo "Comentario agregado correctamente.";
} else {
    echo "Error al agregar el comentario: " . $conn->error;
}

header("Location: ../detallesVentaPestañas.php?id=$id_compra");
?>