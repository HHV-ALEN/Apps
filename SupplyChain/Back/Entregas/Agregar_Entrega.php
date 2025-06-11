<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$Nombre_Usuario = $_SESSION['Name'];
$Fecha_Hoy = date("Y-m-d H:i:s");
$id_salida_original = $_POST['id_salida_original'];
$id_salida = $_POST['id_salida'];
$id_orden_venta = $_POST['id_orden_venta'];
$id_entrega = $_POST['id_entrega'];
$partida1 = $_POST['partida1'];
$partida2 = $_POST['partida2'];
$cliente = $_POST['cliente'];
$id_cliente = $_POST['id_cliente'];

echo "<br> Información de la entrega : ";
echo "<br><strong>Id Salida Original: </strong>" . $id_salida_original;
echo "<br><strong>Id Orden Venta: </strong>" . $id_orden_venta;
echo "<br><strong>Id Entrega: </strong>" . $id_entrega;
echo "<br><strong>Partida 1: </strong>" . $partida1;
echo "<br><strong>Partida 2: </strong>" . $partida2;

$Partida = $partida1 . " - " . $partida2;

// Insertar Información en la tabla entregas
$sql_entrega = "INSERT INTO entregas (Id_Salida, Id_Orden_Venta, Id_Entrega, Partida, Id_Cliente, Cliente_Nombre)
VALUES ('$id_salida_original', '$id_orden_venta', '$id_entrega', '$Partida', '$id_cliente', '$cliente')";
$query_entrega = mysqli_query($conn, $sql_entrega);
if ($query_entrega) {
    echo "<br>-> Entrega registrada correctamente.";
} else {
    echo "<br>Error al registrar la entrega: " . mysqli_error($conn);
}

/// Guardar en bitacora
 $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$id_salida_original', 'Registro de Entrega Extra: $id_entrega', '$Fecha_Hoy', '$Nombre_Usuario')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>-> Bitácora registrada correctamente.";
        } else {
            echo "<br>Error al registrar en la bitácora: " . mysqli_error($conn);
        }

header("Location: ../../Front/detalles.php?id=" . $id_salida_original);


?>