<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();


$Id_Salida = $_POST['Id_Salida'] ?? null;
error_log("🛠️ DEBUG - ID de salida recibido: " . $Id_Salida);

//echo json_encode(['recibido' => $Id_Salida]);

// Obtener el chofer asignado actualmente
$queryChoferActual = "SELECT Chofer FROM preguia WHERE Id_Salida = $Id_Salida LIMIT 1";
$resultActual = mysqli_query($conn, $queryChoferActual);
$rowActual = mysqli_fetch_assoc($resultActual);
$choferActual = $rowActual ? $rowActual['Chofer'] : 'No asignado';

// Obtener todos los choferes disponibles
$queryChoferes = "SELECT Id, Nombre FROM usuarios WHERE Departamento = 'Chofer' ORDER BY Nombre";
$resultChoferes = mysqli_query($conn, $queryChoferes);

$choferes = [];
while ($row = mysqli_fetch_assoc($resultChoferes)) {
    $choferes[] = $row;
}

// Respuesta en JSON
echo json_encode([
    'chofer_actual' => $choferActual,
    'choferes' => $choferes
]);


