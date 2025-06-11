<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // ✅ Return JSON properly

session_start();
$conn = connectMySQLi();

// Validate that the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE salidas SET Id_Status = 30, Estado = 'Eliminado' WHERE Id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Folio deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>