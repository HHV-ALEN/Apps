<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$filtro_salida = $_POST['numero_salida'] ?? '';
$filtro_cliente = $_POST['cliente'] ?? '';
$filtro_orden = $_POST['orden_venta'] ?? '';
$filtro_estado = $_POST['estado'] ?? '';
$filtro_entrega = $_POST['id_entrega'] ?? '';

$filtro_factura = $_POST['id_factura'] ?? '';

$query = "SELECT 
            salidas.*, 
            entregas.Id_Orden_Venta,
            entregas.Id_Entrega,
            (
              SELECT COUNT(*) 
              FROM entregas e 
              WHERE e.Id_Salida = salidas.Id AND e.Archivo != '0'
            ) AS Factura_Registrada,
            (
              SELECT COUNT(*) 
              FROM imagen i 
              WHERE i.id_salida = salidas.Id
            ) AS Imagenes_Registradas
          FROM salidas
          LEFT JOIN entregas ON salidas.Id = entregas.Id_Salida
          WHERE 1";


// ----------------------------- Filtros -----------------------------
if (!empty($filtro_salida)) {
  $query .= " AND salidas.Id = " . intval($filtro_salida);
}

if (!empty($filtro_cliente)) {
  $query .= " AND salidas.Nombre_Cliente LIKE '%" . mysqli_real_escape_string($conn, $filtro_cliente) . "%'";
}

if (!empty($filtro_orden)) {
  $orden = mysqli_real_escape_string($conn, $filtro_orden);
  $subqueries = [];

  $subqueries[] = "salidas.Id IN (
    SELECT Id_Salida FROM entregas WHERE Id_Orden_Venta LIKE '%$orden%')";

  $subqueries[] = "salidas.Id IN (
    SELECT Salida_Base FROM etiquetas_fusionadas WHERE Orden_Venta LIKE '%$orden%')";

  $subqueries[] = "salidas.Id IN (
    SELECT Id_Base FROM consolidados WHERE Orden_Venta LIKE '%$orden%')";

  $query .= " AND (" . implode(" OR ", $subqueries) . ")";
}

if (!empty($filtro_estado)) {
  $query .= " AND salidas.Estado LIKE '%" . mysqli_real_escape_string($conn, $filtro_estado) . "%'";
}

if (!empty($filtro_entrega)) {
  $entrega = mysqli_real_escape_string($conn, $filtro_entrega);
  $subqueries = [];

  $subqueries[] = "salidas.Id IN (
    SELECT Id_Salida FROM entregas WHERE Id_Entrega = '$entrega')";
  $subqueries[] = "salidas.Id IN (
    SELECT Salida_Base FROM etiquetas_fusionadas WHERE Entrega = '$entrega')";
  $subqueries[] = "salidas.Id IN (
    SELECT Id_Base FROM consolidados WHERE Id_Entrega = '$entrega')";

  $query .= " AND (" . implode(" OR ", $subqueries) . ")";
}

if (!empty($filtro_factura)) {
    $factura   = mysqli_real_escape_string($conn, $filtro_factura);
    $sub       = [];

    // ▸ Entrega “normal”
    $sub[] = "salidas.Id IN (
                SELECT Id_Salida
                FROM entregas
                WHERE Id_Factura LIKE '%$factura%'
              )";

    // ▸ Fusionada (si guardas Id_Factura ahí)
    $sub[] = "salidas.Id IN (
                SELECT Salida_Base
                FROM etiquetas_fusionadas
                WHERE Id_Factura LIKE '%$factura%'
              )";

    // ▸ Consolidado (si también lo guardas)
    $sub[] = "salidas.Id IN (
                SELECT Id_Base
                FROM consolidados
                WHERE Id_Factura LIKE '%$factura%'
              )";

    $query .= " AND (" . implode(' OR ', $sub) . ")";
}


$query .= " ORDER BY salidas.Id DESC LIMIT 50";

$result = mysqli_query($conn, $query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
  $data[] = [
    'Id' => $row['Id'],
    'Nombre_Cliente' => $row['Nombre_Cliente'],
    'Estado' => $row['Estado'],
    'Sucursal' => $row['Sucursal'],
    'Urgencia' => $row['Urgencia'],
    'Id_Orden_Venta' => $row['Id_Orden_Venta'],
    'Id_Entrega' => $row['Id_Entrega'],
    'Factura_Registrada' => $row['Factura_Registrada'],
    'Imagenes_Registradas' => $row['Imagenes_Registradas'],
    // otros campos si los necesitas
  ];
}

echo json_encode($data);

?>
