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

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $query = "UPDATE clientes SET Status = 'Inactivo' WHERE Id_Original = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
  
    if ($stmt->execute()) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => $conn->error]);
    }
  
    $stmt->close();
    $conn->close();
  } else {
    echo json_encode(['success' => false, 'error' => 'No ID']);
  }
?>