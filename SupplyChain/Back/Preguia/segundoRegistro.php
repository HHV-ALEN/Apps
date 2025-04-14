<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$id_salida = $_GET['id_salida'];
$Tipo_Doc = $_POST['Tipo_Doc'];
$Costo = $_POST['Costo'] ?? 0;
$Fecha_Doc = $_POST['Fecha_Doc'];
$Id_Guia = $_POST['Id_Guia'];

echo "<br>Id Salida: $id_salida</br>";
echo "<br>Tipo Documento: $Tipo_Doc</br>";
echo "<br>Costo: $Costo</br>";
echo "<br>Fecha Documento: $Fecha_Doc</br>";
echo "<br>Id Guia: $Id_Guia</br>";
// Actualizar doc_preguia
$sql_update_preguia = "UPDATE doc_preguia SET Costo_Reembarque = '$Costo', Fecha_Final = '$Fecha_Doc', 
Guia_Reembarque = '$Id_Guia'WHERE Id_Salida = $id_salida AND Tipo_Doc = 'Reembarque'";
$result_update_preguia = mysqli_query($conn, $sql_update_preguia);

if ($result_update_preguia) {
    echo "<br>Actualización de doc_preguia exitosa</br>";
    // Actualizar a Completado
    $sql_update_salida = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27' WHERE Id = $id_salida";
    $result_update_salida = mysqli_query($conn, $sql_update_salida);
    if ($result_update_salida) {
        echo "<br>Actualización de salida exitosa</br>";
        // Registrar en bitacora
        $sql_insert_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES 
        ('$id_salida', 'Se agregaron los Costos del envio del Reembarque', '$Fecha_Doc', '$Responsable')";
        $result_insert_bitacora = mysqli_query($conn, $sql_insert_bitacora);
        if ($result_insert_bitacora) {
            echo "<br>Bitacora registrada correctamente</br>";
            header("Location: ../../index.php");

        } else {
            echo "<br>Error: " . $sql_insert_bitacora . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "<br>Error: " . $sql_update_salida . "<br>" . mysqli_error($conn);
    }
} else {
    echo "<br>Error: " . $sql_update_preguia . "<br>" . mysqli_error($conn);
}


?>