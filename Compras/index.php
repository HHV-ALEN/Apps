<?php
ini_set('memory_limit', '1024M'); // 1GB
require '../vendor/autoload.php';
include "../Back/config/config.php";
session_start();

$conn = connectMySQLi();$conn = connectMySQLi();

// Parámetros de filtro
$filtroOrdenCompra = isset($_GET['orden_compra']) ? trim($_GET['orden_compra']) : '';
$filtroNumArticulo = isset($_GET['num_articulo']) ? trim($_GET['num_articulo']) : '';
$filtroOrdenVenta = isset($_GET['orden_venta']) ? trim($_GET['orden_venta']) : '';
$filtroCodItem = isset($_GET['cod_item']) ? trim($_GET['cod_item']) : '';

// Configuración de paginación
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Construcción de la consulta con filtros
$where = [];
$params = [];
$types = '';

if (!empty($filtroOrdenCompra)) {
    $where[] = "OrdenCompra LIKE ?";
    $params[] = '%'.$filtroOrdenCompra.'%';
    $types .= 's';
}

if (!empty($filtroNumArticulo)) {
    $where[] = "NumArticulo LIKE ?";
    $params[] = '%'.$filtroNumArticulo.'%';
    $types .= 's';
}

if (!empty($filtroOrdenVenta)) {
    $where[] = "OrdenVenta LIKE ?";
    $params[] = '%'.$filtroOrdenVenta.'%';
    $types .= 's';
}

if (!empty($filtroCodItem)) {
    $where[] = "CodItem LIKE ?";
    $params[] = '%'.$filtroCodItem.'%';
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Consulta principal con filtros
$sql = "SELECT SQL_CALC_FOUND_ROWS * 
        FROM compras_lineasabiertas 
        $whereClause
        ORDER BY Fecha_Registro DESC 
        LIMIT ? OFFSET ?";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
if ($whereClause !== '') {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$resul = $stmt->get_result();

// Obtener total de registros
$total = $conn->query("SELECT FOUND_ROWS() AS total")->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Líneas Abiertas - Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filter-header {
            cursor: pointer;
        }
        .filter-section {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>

<?php require '../Front/navbar.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="get" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="orden_compra" class="form-label">Orden Compra</label>
                        <input type="text" class="form-control" id="orden_compra" name="orden_compra" 
                               value="<?= htmlspecialchars($filtroOrdenCompra) ?>" placeholder="Buscar por OC">
                    </div>
                    <div class="col-md-3">
                        <label for="num_articulo" class="form-label">Núm. Artículo</label>
                        <input type="text" class="form-control" id="num_articulo" name="num_articulo" 
                               value="<?= htmlspecialchars($filtroNumArticulo) ?>" placeholder="Buscar por artículo">
                    </div>
                    <div class="col-md-3">
                        <label for="orden_venta" class="form-label">Orden Venta</label>
                        <input type="text" class="form-control" id="orden_venta" name="orden_venta" 
                               value="<?= htmlspecialchars($filtroOrdenVenta) ?>" placeholder="Buscar por OV">
                    </div>
                    <div class="col-md-3">
                        <label for="cod_item" class="form-label">Código Item</label>
                        <input type="text" class="form-control" id="cod_item" name="cod_item" 
                               value="<?= htmlspecialchars($filtroCodItem) ?>" placeholder="Buscar por código">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                        <a href="?" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Líneas Abiertas – Compras</h5>
            <span class="badge bg-light text-dark">Total: <?= number_format($total) ?> registros</span>
        </div>

        <div class="card-body p-0 text-center">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Orden Compra</th>
                            <th>Orden Venta</th>
                            <th>Artículo</th>
                            <th>Código Item</th>
                            <th>Vendedor</th>
                            <th>Cantidad</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resul->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['OrdenCompra']) ?></td>
                            <td><?= htmlspecialchars($row['OrdenVenta']) ?></td>
                            <td><?= htmlspecialchars($row['NumArticulo']) ?></td>
                            <td><?= htmlspecialchars($row['CodItem']) ?></td>
                            <td><?= htmlspecialchars($row['Vendedor']) ?></td>
                            <td><?= number_format($row['Cantidad_Abierta'], 2) ?></td>
                            <td><a href="detallesCompra.php?id=<?= $row['Id'] ?>"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Detalles
                                    </a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación con filtros -->
            <nav class="p-3">
                <ul class="pagination justify-content-center mb-0 flex-wrap">
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" aria-label="Anterior">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Mostrar siempre la primera página -->
                    <li class="page-item <?= 1 == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    </li>

                    <?php
                    // Mostrar rango alrededor de la página actual
                    $start = max(2, $page - 2);
                    $end = min($totalPages - 1, $page + 2);
                    
                    // Mostrar puntos suspensivos si hay un gap después de la página 1
                    if($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Mostrar puntos suspensivos si hay un gap antes de la última página -->
                    <?php if($end < $totalPages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Mostrar siempre la última página si hay más de 1 página -->
                    <?php if($totalPages > 1): ?>
                    <li class="page-item <?= $totalPages == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" aria-label="Siguiente">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>