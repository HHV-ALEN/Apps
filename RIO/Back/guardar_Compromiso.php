<?php
$total_final = 0;
session_start();
include "../../Back/config/config.php";

$conn = connectMySQLi();

echo "<pre>";
print_r($_POST);
echo "</pre>";

$resultados = [];

$Nombre_Encuestado = $_GET['Evaluado'];
echo "<hr>";
echo "<br> <strong>Nombre Encuestado:</strong> " . $Nombre_Encuestado;

$periodo = $_GET['Periodo'];
echo "<br><strong>Periodo: </strong>" . $periodo;

// Asumimos que ya tienes la conexión a la BD ($conn)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $compromisos = $_POST['compromiso'] ?? [];
    $fechas = $_POST['fecha_compromiso'] ?? [];
    $feedbacks = $_POST['feedback'] ?? [];

    // Preparar la consulta SQL (usando consultas preparadas para seguridad)
    $query = "UPDATE rio_feedback SET 
              Compromiso_Encuestado = ?, 
              Fecha_Compromiso = ? 
              WHERE Feedback = ?
              AND Periodo = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $compromiso, $fecha, $feedback, $periodo);

    $registros_actualizados = 0;

    // Recorremos los compromisos (array principal)
    foreach ($compromisos as $id => $compromiso) {
        // Verificamos si existe en los otros arrays
        if (isset($fechas[$id]) && isset($feedbacks[$id])) {
            $fecha = $fechas[$id];
            $feedback = $feedbacks[$id];
            
            // Validación básica
            if (!empty($compromiso) && !empty($fecha)) {
                if ($stmt->execute()) {
                    $registros_actualizados++;
                } else {
                    echo "Error al actualizar ID $id: " . $stmt->error . "<br>";
                }
            }
        }
    }

    $stmt->close();

    // Feedback al usuario
    if ($registros_actualizados > 0) {
        echo "<div class='alert alert-success'>
                <i class='fas fa-check-circle'></i> Se actualizaron $registros_actualizados registros correctamente.
              </div>";
    } else {
        echo "<div class='alert alert-warning'>
                <i class='fas fa-exclamation-triangle'></i> No se actualizó ningún registro.
              </div>";
    }
} else {
    echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle'></i> Método no permitido.
          </div>";
}



header('Location: ../Front/vistaAsignado.php');

?>