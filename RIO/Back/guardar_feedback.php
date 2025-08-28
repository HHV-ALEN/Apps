<?php
session_start();
include "../../Back/config/config.php";

$Periodo = $_GET['Periodo'];
$Evaluado = $_GET['Evaluado'];
$Evaluador = $_SESSION['Name'];
$Fecha_hoy = date("Y-m-d H:i:s");

echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<br> Periodo: " . htmlspecialchars($Periodo);
echo "<br> Evaluado: " . htmlspecialchars($Evaluado);
echo "<br> Evaluador: " . htmlspecialchars($Evaluador);
echo "<br> Fecha: " . htmlspecialchars($Fecha_hoy);

$conn = connectMySQLi();

// Preparar la consulta SQL
$stmt = $conn->prepare("INSERT INTO rio_feedback (
    Nombre_Encuestado, 
    Nombre_Encuestador, 
    Estado, 
    Periodo, 
    Feedback, 
    Comentario, 
    Fecha_Registro
) VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

$stmt->bind_param("sssssss", $Evaluado, $Evaluador, $estado, $Periodo, $objetivo, $comentario, $Fecha_hoy);

// Recorremos los IDs que vienen en el array asociativo
foreach ($_POST['Id'] as $id => $valor_id) {
    // Obtener los valores correspondientes a este ID
    $estado = $_POST['estado'][$id] ?? null;
    $comentario = $_POST['comentario'][$id] ?? '';
    $objetivo = $_POST['Objetivo'][$id] ?? null;
    
    // Validación básica
    if (empty($objetivo)) {
        echo "<br> Objetivo vacío para ID $id - Saltando registro";
        continue;
    }
    
    if (empty($estado)) {
        echo "<br> Estado vacío para ID $id - Saltando registro";
        continue;
    }
    
    // Ejecutar la inserción
    if (!$stmt->execute()) {
        echo "<br> Error al insertar registro ID $id: " . $stmt->error;
        // Puedes decidir si continuar o salir del bucle
    } else {
        echo "<br> Registro insertado correctamente (ID: $id)";
    }
}

$stmt->close();
$conn->close();

header('Location: ../index.php');
?>
