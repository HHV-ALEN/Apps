<?php
session_start();
include "../../Back/config/config.php";
$conn = connectMySQLi();

$Evaluado = $_GET['Evaluado'];
$Periodo = $_GET['Periodo'];
echo "<br> Evaluado " . $Evaluado;
echo "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['id_registro'])) {
        foreach ($_POST['id_registro'] as $id) {
            $comentario = isset($_POST['comentario'][$id]) ? trim($_POST['comentario'][$id]) : '';
            $estado     = isset($_POST['estado'][$id]) ? trim($_POST['estado'][$id]) : '';

            $sql_update = "UPDATE rio_feedback SET Comentario = ?, Estado = ? WHERE Id = ? AND Nombre_Encuestado = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "ssis", $comentario, $estado,  $id, $Evaluado);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "✅ Comentario: $comentario actualizado para el registro ID: $id con nuevo Estado: $estado<br>";
            } else {
                echo "⚠️ No se actualizó el comentario para el registro ID: $id o no hubo cambios.<br>";
            }
        }
    }

    echo "✅ Actualización completada";
}


/// Regresar a la página anterior

header("Location: ../Front/feedback.php?NombreEvaluado=$Evaluado&Periodo=$Periodo");
exit;

?>