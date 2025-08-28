<?php 
session_start();
include "../../Back/config/config.php";

$conn = connectMySQLi();

echo "<pre>";
print_r($_POST);
echo "</pre>";

$Nombre = $_POST['Nombre'] ?? '';
$periodo = $_POST['periodo'] ?? '';
/*
Array
(
    [estado] => Array
        (
            [59] => Concluido
            [60] => Conforme
        )

    [comentario] => Array
        (
            [59] => DONE
            [60] => ALMOST DONE
        )

    [id_registro] => Array
        (
            [0] => 59
            [1] => 60
        )
)
        */

// Actualizar tabla: rio_feedback donde Id_Registro tiene los valores del Campo Id de la Tabla


foreach ($_POST['id_registro'] as $id) {
    $estado = $_POST['estado'][$id] ?? '';
    $comentario = $_POST['comentario'][$id] ?? '';

    $sql = "UPDATE rio_feedback SET Estado = ?, Comentario = ? WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $estado, $comentario, $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<br> Registro con ID $id actualizado correctamente.";
    } else {
        echo "<br> No se pudo actualizar el registro con ID $id o no hubo cambios.";
    }
}

$stmt->close();
header('Location: ../Front/feedback.php?Nombre=' . urlencode($Nombre) . '&periodo=' . urlencode($periodo));

?>