<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$filtro_salida = isset($_POST['numero_salida']) ? $_POST['numero_salida'] : '';
$filtro_cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
$filtro_orden = isset($_POST['orden_venta']) ? $_POST['orden_venta'] : '';
$filtro_sucursal = isset($_POST['sucursal']) ? $_POST['sucursal'] : '';

$query = "SELECT salidas.*, entregas.Id_Orden_Venta 
          FROM salidas
          LEFT JOIN entregas ON salidas.Id = entregas.Id_Salida
          WHERE 1"; // Para que los filtros se agreguen dinámicamente


if (!empty($filtro_salida)) {
    $query .= " AND salidas.Id LIKE '%$filtro_salida%'";
}
if (!empty($filtro_cliente)) {
    $query .= " AND salidas.Nombre_Cliente LIKE '%$filtro_cliente%'";
}
if (!empty($filtro_orden)) {
    $query .= " AND entregas.Id_Orden_Venta LIKE '%$filtro_orden%'";
}
if (!empty($filtro_sucursal)) {
    $query .= " AND salidas.Sucursal LIKE '%$filtro_sucursal%'";
}

$query .= " ORDER BY Id DESC LIMIT 50"; // Solo muestra los 50 primeros resultados

$result = mysqli_query($conn, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
