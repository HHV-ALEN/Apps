<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$nombre = $_POST['nombre'] ?? '';

$response = [];

if ($nombre) {
    $query = "SELECT Calle, Colonia, Ciudad, Estado, CP FROM clientes WHERE Nombre = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $nombre);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $response = $row;
    }
}


echo json_encode($response);
