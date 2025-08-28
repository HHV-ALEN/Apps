<?php
session_start();
include "../../Back/config/config.php";
$conn = connectMySQLi();

print_r($_POST);

$archivo = $_POST['archivo'];
$NombreUsuario = $_POST['NombreUsuario'];
$ArchivoAntiguo = $_POST['archivoOld'];

echo "<br> Nombre del Archivo: " . $archivo;
echo "<br> Nombre del Usuario: " . $NombreUsuario;
echo "<br> Archivo Antiguo: " . $ArchivoAntiguo;

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $nombreArchivo = $_FILES['archivo']['name'];
    $rutaTemporal = $_FILES['archivo']['tmp_name'];

    // Dividir el nombre completo en partes
    $partesNombre = array_filter(explode(' ', trim($NombreUsuario)));
    $nombre = isset($partesNombre[0]) ? $partesNombre[0] : '';
    $apellido1 = isset($partesNombre[1]) ? $partesNombre[1] : '';
    $apellido2 = isset($partesNombre[2]) ? $partesNombre[2] : '';

    // Construir el nuevo nombre del archivo
    $nuevoNombre = "RIO_{$nombre}_{$apellido1}" . ($apellido2 ? "_{$apellido2}" : "") . ".xlsx";
    echo "<br> Nombre original del Archivo: " . $nombreArchivo;
    echo "<br> Nuevo nombre del Archivo: " . $nuevoNombre;

    // Ruta donde se guardarán los archivos
    $directorio = "../Archivos/";

    // Eliminar el archivo antiguo si existe
    if (!empty($ArchivoAntiguo) && file_exists($directorio . $ArchivoAntiguo)) {
        if (unlink($directorio . $ArchivoAntiguo)) {
            echo "<br> Archivo antiguo eliminado correctamente: " . $ArchivoAntiguo;
        } else {
            echo "<br> Error al eliminar el archivo antiguo.";
        }
    }

    // Subir el nuevo archivo
    $destino = $directorio . $nuevoNombre;
    if (move_uploaded_file($rutaTemporal, $destino)) {
        echo "<br> Archivo subido correctamente con el nombre: " . $nuevoNombre;
    } else {
        echo "<br> Error al mover el archivo.";
    }
} else {
    echo "<br> No se recibió archivo o hubo un error.";
}

header("Location: ../index.php");
exit();