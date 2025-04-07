<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
$id_salida = $_GET['id_salida'];

$query = "SELECT * FROM comentarios WHERE Id_Salida = '$id_salida' ORDER BY Fecha DESC";
$result = mysqli_query($conn, $query);

$comentarios = "";
while ($row = mysqli_fetch_assoc($result)) {
    /// Quitar la hora de la fecha, solo mostrar la fecha y la hora y minutos
    $Comentario = $row['Comentario'];
    $Fecha = $row['Fecha'];
    $Fecha = substr($Fecha, 0, 16);
    $row['Fecha'] = $Fecha;
    $comentarios .= "<p><strong>{$row['Responsable']} ({$Fecha}):</strong> {$row['Comentario']}</p>";
}


echo $comentarios;
?>