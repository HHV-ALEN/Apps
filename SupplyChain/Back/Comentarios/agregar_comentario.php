<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_salida = $_POST['id_salida'];
    $usuario = $_POST['usuario'];
    $comentario = $_POST['comentario'];
    $fecha = date("Y-m-d H:i:s");

    $query = "INSERT INTO comentarios (Id_Salida, Comentario, Fecha, Responsable)
              VALUES ('$id_salida', '$comentario', '$fecha', '$usuario')";

    if (mysqli_query($conn, $query)) {
        echo "Comentario agregado";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>