<?php
include "../../../Back/config/config.php";
$conn = connectMySQLi();
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
ob_start();

$ordenFiltro   = $conn->real_escape_string($_POST['orden'] ?? '');
$clienteFiltro = $conn->real_escape_string($_POST['cliente'] ?? '');
$itemFiltro    = $conn->real_escape_string($_POST['item'] ?? '');
$pagina        = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;

$por_pagina = 10;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina - $por_pagina) : 0;

// WHERE dinámico
$where = [];
if ($ordenFiltro !== '')   $where[] = "OrdenCompra LIKE '%$ordenFiltro%'";
if ($clienteFiltro !== '') $where[] = "NombreCliente LIKE '%$clienteFiltro%'";
if ($itemFiltro !== '')    $where[] = "CodigoItem LIKE '%$itemFiltro%'";
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total
$sql_total = "SELECT COUNT(*) AS total FROM supply_compras $where_sql";
$total_resultado = $conn->query($sql_total);
$total_filas = $total_resultado->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $por_pagina);

// Datos
$sql_data = "SELECT * FROM supply_compras $where_sql ORDER BY Id DESC LIMIT $inicio, $por_pagina";
$resultado = $conn->query($sql_data);

// Tabla
echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-hover align-middle text-center">';
echo '<thead class="table-dark"><tr>
        <th>ID</th>
        <th>Orden de Compra</th>
        <th>Nombre del Cliente</th>
        <th>Código del Ítem</th>
        <th>Fecha</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr></thead><tbody>';

while ($row = $resultado->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row['Id'] . '</td>';
    echo '<td>' . $row['OrdenCompra'] . '</td>';
    echo '<td>' . $row['NombreCliente'] . '</td>';
    echo '<td>' . $row['CodigoItem'] . '</td>';
    echo '<td>' . date('d/m/Y', strtotime($row['Fecha'])) . '</td>';
    echo '<td>' . $row['Estado'] . '</td>';
    echo '<td> <a class="btn btn-outline-primary" href="detallesCompra.php?id='. $row['Id'] .'">🔎 Detalles </a>  </td>';
    echo '</tr>';
}

echo '</tbody></table></div>';

// Paginación
echo '<nav><ul class="pagination justify-content-center">';
for ($i = 1; $i <= $total_paginas; $i++) {
    echo '<li class="page-item"><a href="#" class="page-link pagina" data-pagina="' . $i . '">' . $i . '</a></li>';
}
echo '</ul></nav>';
?>
