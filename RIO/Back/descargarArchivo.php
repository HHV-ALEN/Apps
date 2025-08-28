<?php
if (!isset($_GET['file'])) {
    exit('Archivo no especificado');
}

$archivo = basename($_GET['file']);
$ruta = "../Archivos/Evidencias/" . $archivo;

if (file_exists($ruta)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $archivo . '"');
    readfile($ruta);
    exit;
} else {
    echo "Archivo no encontrado.";
}
?>
