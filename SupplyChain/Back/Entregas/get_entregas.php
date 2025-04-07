<?php
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
header('Content-Type: application/json');

if (!empty($_POST['id_salida'])) {
    $id_salida = mysqli_real_escape_string($conn, $_POST['id_salida']);

    $query = "SELECT *
              FROM entregas 
              WHERE Id_Salida = '$id_salida'";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo json_encode(["error" => "Error en la consulta: " . mysqli_error($conn)]);
        exit;
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No se recibió un ID válido"]);
}
?>