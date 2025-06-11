<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
// Mostrar todos los errores:

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conectar a la base de datos
$conn = connectMySQLi();
session_start();
$FechaHoy = date('Y-m-d H:i:s');

print_r($_GET);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$urgencia = $_GET['urgencia'] ?? 'Nada';

echo "<br> Id: " . $id;
echo "<br> Urgencia: " . $urgencia;

if ($id > 0 && in_array($urgencia, ['Urgente', 'Nada'])) {
    $query = "UPDATE salidas SET Urgencia = ? WHERE Id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $urgencia, $id);

    if (mysqli_stmt_execute($stmt)) {
        if($urgencia == 'Nada'){
            $urgencia = 'Sin Urgencia';
            
        }
        echo "<br> Actualización de Urgencia a : " . $urgencia;

        $_SESSION['urgencia_msg'] = "La salida <strong>$id</strong> fue marcada como <strong>$urgencia</strong> correctamente.";
        
        /// Registro en bitacora
        $Bitacora_Query ="INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
        VALUES ($id, 'Cambio de estado: $urgencia', '$FechaHoy', '" . $_SESSION['Name'] . "')";
        $result = mysqli_query($conn, $Bitacora_Query);

        if ($result) {
            echo "<br>-> Bitacora Registrada Correctamente<br>";
        } else {
            echo "<h1>Error al registrar la bitacora</h1>";
        }

    } else {
        $_SESSION['urgencia_msg'] = "⚠️ Error al actualizar la urgencia.";
    }
}

header("Location: ../../index.php"); // Regresa al listado
exit;
