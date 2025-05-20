<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$filtro_salida = isset($_POST['numero_salida']) ? $_POST['numero_salida'] : '';
$filtro_cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
$filtro_orden = isset($_POST['orden_venta']) ? $_POST['orden_venta'] : '';
$filtro_estado = isset($_POST['estado']) ? $_POST['estado'] : '';
$filtro_entrega = isset($_POST['id_entrega']) ? $_POST['id_entrega'] : '';

$query = "SELECT 
            salidas.*, 
            entregas.Id_Orden_Venta,
            entregas.Id_Entrega,
            (
              SELECT COUNT(*) 
              FROM entregas e 
              WHERE e.Id_Salida = salidas.Id AND e.Archivo != '0'
            ) AS Factura_Registrada
          FROM salidas
          LEFT JOIN entregas ON salidas.Id = entregas.Id_Salida
          WHERE 1";

if (!empty($filtro_salida)) {
  $query .= " AND salidas.Id = " . intval($filtro_salida);
}
if (!empty($filtro_cliente)) {
    $query .= " AND salidas.Nombre_Cliente LIKE '%$filtro_cliente%'";
}
if (!empty($filtro_orden)) {

  // Escape para seguridad
  $orden = mysqli_real_escape_string($conn, $filtro_orden);

  $subqueries = [];

  // Buscar en entregas
  $subqueries[] = "salidas.Id IN (
    SELECT Id_Salida FROM entregas WHERE Id_Orden_Venta LIKE '%$orden%'
  )";

  // Buscar en etiquetas_fusionadas
  $subqueries[] = "salidas.Id IN (
    SELECT Salida_Base FROM etiquetas_fusionadas WHERE Orden_Venta LIKE '%$orden%'
  )";

  // Buscar en consolidados
  $subqueries[] = "salidas.Id IN (
    SELECT Id_Base FROM consolidados WHERE Orden_Venta LIKE '%$orden%'
  )";

  $query .= " AND (" . implode(" OR ", $subqueries) . ")";
}

if (!empty($filtro_estado)) {
    $query .= " AND salidas.Estado LIKE '%$filtro_estado%'";
}
if (!empty($filtro_entrega)) {

  // Preparar escape para seguridad
  $entrega = mysqli_real_escape_string($conn, $filtro_entrega);

  // Buscar en entregas normales
  $subqueries = [];

  $subqueries[] = "salidas.Id IN (
      SELECT Id_Salida FROM entregas WHERE Id_Entrega = '$entrega'
  )";

  // Buscar en etiquetas_fusionadas
  $subqueries[] = "salidas.Id IN (
      SELECT Salida_Base FROM etiquetas_fusionadas WHERE Entrega = '$entrega'
  )";

  // Buscar en consolidados
  $subqueries[] = "salidas.Id IN (
      SELECT Id_Base FROM consolidados WHERE Id_Entrega = '$entrega'
  )";

  // Unir todas las condiciones con OR
  $query .= " AND (" . implode(" OR ", $subqueries) . ")";
}


$query .= " ORDER BY Id DESC LIMIT 50"; // Solo muestra los 50 primeros resultados

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
    'Factura_Registrada' => $row['Factura_Registrada'], // 👈 ¡aquí llega!
    // ... otros campos
  ];
}

echo json_encode($data);
?>
