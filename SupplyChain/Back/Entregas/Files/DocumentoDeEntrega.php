<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
$id_salida = $_GET['id_salida'] ?? null;

echo "<h1>Documento de Entrega:</h1>";
echo "<br><p><strong>Id Salida: </strong> $id_salida </p>";

$dir = "Docs/";
$path = $dir . basename($_FILES["DocumentoDeEntrega"]["name"]);
$file_name = $_FILES["DocumentoDeEntrega"]["name"];
echo "<br><p><strong>Archivo: </strong> " . htmlspecialchars($_FILES["DocumentoDeEntrega"]["name"]) . "</p>";


/// Subir el archivo
if (isset($_FILES["DocumentoDeEntrega"]) && $_FILES["DocumentoDeEntrega"]["error"] == UPLOAD_ERR_OK) {
    if (move_uploaded_file($_FILES["DocumentoDeEntrega"]["tmp_name"], $path)) {
        echo "<p>El archivo ha sido subido exitosamente.</p>";
    } else {
        echo "<p>Error al subir el archivo.</p>";
    }
} else {
    echo "<p>No se ha seleccionado ningún archivo o ha ocurrido un error.</p>";
}

//// Actualizar la tabla entregas - Atributo DocumentoDeEntrega 
$Update_Doc = "UPDATE entregas SET DocumentoDeEntrega = '$file_name' WHERE id_salida = $id_salida";
if(mysqli_query($conn, $Update_Doc)){
    echo "<br> - Documento Registrado con exito!";
}else {
    echo "<br> - Error al registrar el documento: " . mysqli_error($conn);
}




?>