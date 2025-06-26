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

if (!empty($_FILES['fotoEntrega']['name'])) {
  $tmp  = $_FILES['fotoEntrega']['tmp_name'];
  $name = basename($_FILES['fotoEntrega']['name']);
  move_uploaded_file($tmp, "Files/img/$name");
  // Guarda la ruta en BD si lo necesitas
}

echo "Folio: $id_salida <br>";
echo "Estado Entrega: $EstadoEntrega <br>";
echo "Comentario: $Comentario <br>";
echo "<br> Imagen Agregada: " . $name;

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

$sql_image = "INSERT INTO imagen (id_salida, nombre, status, Responsable)
VALUES ('$id_salida', '$name', 'Activo', '$Nombre_Usuario')";
$query_result = mysqli_query($conn, $sql_image);
if (!$query_result) {
    echo "Error: " . $sql_image . "<br>" . mysqli_error($conn);
} else {
    echo "<br> - Registro insertado en la tabla 'imagen' correctamente";
}



echo "<br>- Id_Salida: $id_salida <br>";


header("Location: ../../Front/detalles.php?id=$id_salida");

?>