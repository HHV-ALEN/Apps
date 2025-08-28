<?php 

include '../../Back/config/config.php';
$conn = connectMySQLi();
session_start(); // ¡No olvides esto!


// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consulta para obtener los nombres de usuarios
$sql = "SELECT Nombre FROM usuarios ORDER BY Nombre";
$result = $conn->query($sql);

$usuarios = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $usuarios[] = $row["Nombre"];
    }
}

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($usuarios);

$conn->close();
?>