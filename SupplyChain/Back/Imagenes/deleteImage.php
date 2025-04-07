

<?php
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

echo "<h1>Información de la Session</h1>";
$id_salida = $_GET['id_salida'];
$Nombre_Archivo = $_GET['Nombre_Archivo'];
$Nombre_Usuario = $_SESSION['Name'];
$Fecha = date("Y-m-d");
echo "<strong>Id Salida: </strong>" . $id_salida;
echo "<br><strong>Nombre del Archivo: </strong>". $Nombre_Archivo;

/// Eliminar Archivo
/// Eliminar Registro en BD
//  Regisrar En bitacora? 

$dir = "../Files/img/";
$ruta = $dir . $Nombre_Archivo;

echo "<br> <strong>Archivo:</strong> ".$Nombre_Archivo;

// Verificar si el archivo existe en la carpeta

if (file_exists($ruta)) {
    unlink($ruta);
    echo "<br> Archivo eliminado correctamente";
    
} else {
    echo "<br> El archivo no existe";
}

$query = "DELETE FROM imagen WHERE id_salida = $id_salida AND nombre = '$Nombre_Archivo'";
$result = mysqli_query($conn, $query);
if ($result) {
    echo "<br> Registro eliminado correctamente";
    // Registro en Bitacora
    $Bitacora_Query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$id_salida', 'Eliminación de Archivo: $Nombre_Archivo', '$Fecha', '$Nombre_Usuario')";
    $Bitacora_Result = mysqli_query($conn, $Bitacora_Query);
    if (!$Bitacora_Result) {
        die('Query Failed Bitacora.');
    } else {
        echo "Se insertó correctamente en la tabla Bitacora<br>";
    }
}

header("Location: ../../Front/detalles.php?id=".$id_salida);

?>