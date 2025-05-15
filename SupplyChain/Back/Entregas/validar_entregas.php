<?php
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
header('Content-Type: application/json');

$folio = $_POST['folio_entrega'] ?? '';

$response = ['existe' => false];

if ($folio !== '') {
  $query = "SELECT Id_Salida FROM entregas WHERE Id_Entrega = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('s', $folio);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($idSalida);
    $stmt->fetch();
    $response = [
      'existe' => true,
      'id_salida' => $idSalida
    ];
  }
}

echo json_encode($response);