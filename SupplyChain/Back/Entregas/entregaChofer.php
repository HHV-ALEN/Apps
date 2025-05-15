<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$Nombre_Usuario = $_SESSION['Name'];

$id_salida = $_POST['Folio'];
$EstadoEntrega = $_POST['EstadoEntrega'];
$Comentario = $_POST['Comentario'];
$Fecha = date('Y-m-d H:i:s');
echo "Folio: $id_salida <br>";
echo "Estado Entrega: $EstadoEntrega <br>";
echo "Comentario: $Comentario <br>";


/// Insertar en la tabla 
$sql = "INSERT INTO entrega_chofer (Id_Salida, Estado, Fecha, Responsable)
VALUES ('$id_salida', '$EstadoEntrega', '$Fecha', '$Nombre_Usuario')";
$query = mysqli_query($conn, $sql);
if (!$query) {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
} else {
    echo "<br>- Registro insertado en la tabla 'chofer_entrega' correctamente";
}

// si comentarios no está vacío, insertar en la tabla "comentarios"
if ($Comentario != "") {
    $sql = "INSERT INTO comentarios (Id_Salida, Comentario, Fecha, Responsable)
    VALUES ('$id_salida', '$Comentario', '$Fecha', '$Nombre_Usuario')";
    $query = mysqli_query($conn, $sql);
    if (!$query) {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    } else {
        echo "Registro insertado en la tabla 'comentarios' correctamente";
    }
}

// Actualizar Id_Status = 26 y "Estado" = "Envios" en la tabla "salida_refactor"
$sql = "UPDATE salidas SET Id_Status = 26, Estado = 'Envios' WHERE Id = '$id_salida'";
$query = mysqli_query($conn, $sql);
if (!$query) {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
} else {
    echo "<br> - Registro actualizado en la tabla 'salida_refactor' correctamente";
}

// Insertar en la tabla "actualizaciones_bitacora_nueva"
$sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
VALUES ('$id_salida', 'Se Entrego con estado: $EstadoEntrega', '$Fecha', '$Nombre_Usuario')";
$query = mysqli_query($conn, $sql);
if (!$query) {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
} else {
    echo "<br> - Registro insertado en la tabla 'actualizaciones_bitacora_nueva' correctamente";
}
echo "<br>- Id_Salida: $id_salida <br>";

header("Location: ../../Front/detalles.php?id=$id_salida");
?>