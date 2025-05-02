<?php
include "../../../Back/config/config.php";
session_start();

if (isset($_SESSION['alerta_estado'])) {
    echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
    unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();

$busqueda = $_POST['busqueda'] ?? '';
$pagina = $_POST['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$params = [];
$sql = "SELECT * FROM clientes WHERE Status = 'Activo'";
$sql_count = "SELECT COUNT(*) as total FROM clientes WHERE Status = 'Activo'";

if (!empty($busqueda)) {
    $sql .= " AND nombre LIKE ?";
    $sql_count .= " AND nombre LIKE ?";
    $params[] = "%" . $busqueda . "%";
}

// Total de resultados
$stmt_count = $conn->prepare($sql_count);
if (!empty($busqueda)) {
    $stmt_count->bind_param("s", $params[0]);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total = $result_count->fetch_assoc()['total'];

$totalPaginas = ceil($total / $limite);

// Consulta principal con paginación
$sql .= " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($busqueda)) {
    $stmt->bind_param("sii", $params[0], $limite, $offset);
} else {
    $stmt->bind_param("ii", $limite, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Construir tabla
$html = '';
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$row['Id_Original']} </td>
        <td>{$row['Nombre']} ({$row['Clave_Sap']}) </td>
        <td>{$row['RFC']} </td>
        <td>{$row['Calle']}</td>
        <td>{$row['Ciudad']}</td>
        <td>{$row['Telefono']}</td>
        <td>
        <div class='row'>
            <div class='col-md-6'>
                <button class='btn btn-sm btn-warning btn-abrir-modal-editar' 
                        data-id='{$row['Id_Original']}' 
                        data-bs-toggle='modal' 
                        data-bs-target='#modalEditar'>

                <i class='bi bi-pencil-fill'></i> Editar
                </button>
            </div>
            <div class='col-md-6'>
                <button class='btn btn-sm btn-danger' onclick='eliminarCliente({$row['Id_Original']})' >
                    <i class='bi bi-eraser-fill'></i> Eliminar
                </button>
                </div>
            </div>
        </td>
    </tr>";
}


if ($total == 0) {
    $html = "<tr><td colspan='7' class='text-center'>No se encontraron resultados.</td></tr>";
}

// Construir paginación
// Construir paginación con rangos acotados
$paginacion = '';

// Botón "anterior"
if ($pagina > 1) {
    $paginacion .= "<li class='page-item'><a href='#' class='page-link pagina-link' data-pagina='" . ($pagina - 1) . "'>&laquo;</a></li>";
}

$mostrar = 2; // Cantidad de páginas antes y después de la actual

for ($i = 1; $i <= $totalPaginas; $i++) {
    if (
        $i == 1 || // Siempre mostrar la primera página
        $i == $totalPaginas || // Siempre mostrar la última página
        ($i >= $pagina - $mostrar && $i <= $pagina + $mostrar) // Páginas cercanas a la actual
    ) {
        $activo = ($i == $pagina) ? 'active' : '';
        $paginacion .= "<li class='page-item $activo'><a href='#' class='page-link pagina-link' data-pagina='$i'>$i</a></li>";
    } elseif (
        // Mostrar puntos suspensivos donde hay salto
        ($i == $pagina - $mostrar - 1 || $i == $pagina + $mostrar + 1)
    ) {
        $paginacion .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }
}

// Botón "siguiente"
if ($pagina < $totalPaginas) {
    $paginacion .= "<li class='page-item'><a href='#' class='page-link pagina-link' data-pagina='" . ($pagina + 1) . "'>&raquo;</a></li>";
}


// Respuesta
echo json_encode([
    'html' => $html,
    'paginacion' => $paginacion
]);
