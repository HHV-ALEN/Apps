<?php
session_start();
include "../../Back/config/config.php";
$conn = connectMySQLi();

print_r($_POST);

$archivo = $_POST['archivo'];
$NombreUsuario = $_POST['NombreUsuario'];
echo "<br> Nombre del Archivo: " . $archivo;
echo "<br> Nombre del Usuario: " . $NombreUsuario;


if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $nombreArchivo = $_FILES['archivo']['name'];
    $rutaTemporal = $_FILES['archivo']['tmp_name'];

    // Dividir el nombre completo en partes
    $partesNombre = array_filter(explode(' ', trim($NombreUsuario)));
    $nombre = isset($partesNombre[0]) ? $partesNombre[0] : '';
    $apellido1 = isset($partesNombre[1]) ? $partesNombre[1] : '';
    $apellido2 = isset($partesNombre[2]) ? $partesNombre[2] : '';

    // Si no hay segundo apellido, dejar vacío o repetir el primero (depende de tus necesidades)
    $nuevoNombre = "RIO_{$nombre}_{$apellido1}" . ($apellido2 ? "_{$apellido2}" : "") . ".xlsx";
    echo "<br> Nombre original del Archivo: " . $nombreArchivo;
    echo "<br> Nuevo nombre del Archivo: " . $nuevoNombre;

    // Ejemplo de guardado:
    $destino = "../Archivos/" . $nuevoNombre;
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