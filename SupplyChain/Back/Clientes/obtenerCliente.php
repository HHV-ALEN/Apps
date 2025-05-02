<?php
include "../../../Back/config/config.php";
session_start();

if (isset($_SESSION['alerta_estado'])) {
    echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
    unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE Id_Original = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($row = $resultado->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Cliente no encontrado"]);
    }
}
?>
