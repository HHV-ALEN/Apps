<?php
session_start();
include "../../Back/config/config.php";
$conn = connectMySQLi();


$Responsable = $_GET['Evaluado'];
$Periodo = $_GET['Periodo'];
$Archivos = [];
$sql_Evidencias = "SELECT Nombre_Archivo FROM rio_evidencias WHERE Responsable = '$Responsable' AND Periodo = '$Periodo'";
$result = $conn->query($sql_Evidencias);

while ($row = $result->fetch_assoc()) {
    $Archivos[] = $row;
}

if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $zip_name = "Evidencias_" . $Responsable. "_" . $Periodo . ".zip";
    $zip_path = sys_get_temp_dir() . '/' . $zip_name;

    if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
        $ruta_base = '../Archivos/Evidencias/';
        
        foreach ($Archivos as $key => $archivo) {
            if (is_array($archivo)) { // Ignorar metadatos del array
                $ruta_completa = $ruta_base . $archivo['Nombre_Archivo'];
                if (file_exists($ruta_completa)) {
                    $zip->addFile($ruta_completa, $archivo['Nombre_Archivo']);
                }
            }
        }
        
        $zip->close();
    
        // Forzar descarga del ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_name . '"');
        readfile($zip_path);
        unlink($zip_path); // Eliminar el ZIP temporal
        exit;
    } else {
        die("Error al crear el ZIP.");
    }
} else {
    die("La extensión ZipArchive no está instalada en el servidor.");
}
?>