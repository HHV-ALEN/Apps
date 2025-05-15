<?php 
include "../../Back/config/config.php";
require '../../vendor/autoload.php'; // Asegúrate de que la ruta sea correcta

session_start();
$conn = connectMySQLi();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM supply_compras WHERE Id = $id";
$resultado = $conn->query($query);
$datos = $resultado->fetch_assoc();

if (!$datos) {
    echo "<div class='container mt-5'><h4>❌ Registro no encontrado</h4></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de Compra #<?= $id ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .detalle-card {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .detalle-icon {
            font-size: 1.5rem;
            color: #0d6efd;
        }
        .detalle-label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php require_once("../../Front/navbar.php"); ?>

<div class="container mt-5">
    <div class="card detalle-card p-4">
        <h4 class="text-center mb-4">📦 Detalles de la Compra #<?= $id ?></h4>

        <!-- Sección de identificación -->
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-receipt me-2 text-primary"></i>
            <strong class="me-2">Orden de Venta:</strong> <?= htmlspecialchars($datos['OrdenVenta']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
            <strong class="me-2">Orden de Compra:</strong> <?= htmlspecialchars($datos['OrdenCompra']) ?>
        </div>

        <!-- Cliente y fechas -->
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-person-circle me-2 text-success"></i>
            <strong class="me-2">Cliente:</strong> <?= htmlspecialchars($datos['NombreCliente']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-calendar-event me-2 text-info"></i>
            <strong class="me-2">Fecha de Entrega:</strong> <?= date('d/m/Y', strtotime($datos['FechaEntregaCliente'])) ?>
        </div>

        <!-- Información del artículo -->
        <hr>
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-cpu me-2 text-warning"></i>
            <strong class="me-2">Artículo:</strong> <?= htmlspecialchars($datos['NoDeArticulo']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 text-warning"></i>
            <strong class="me-2">Descripción:</strong> <?= htmlspecialchars($datos['Descripcion']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-boxes me-2 text-warning"></i>
            <strong class="me-2">Cantidad Abierta:</strong> <?= htmlspecialchars($datos['CantidadAbiertaRestante']) ?>
        </div>

        <!-- Costos -->
        <hr>
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-currency-dollar me-2 text-danger"></i>
            <strong class="me-2">Precio:</strong> $<?= htmlspecialchars($datos['Precio']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-cash-stack me-2 text-danger"></i>
            <strong class="me-2">Importe Pendiente:</strong> $<?= htmlspecialchars($datos['ImportePendiente']) ?>
        </div>

        <!-- Titular y estado -->
        <hr>
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-person-badge me-2 text-secondary"></i>
            <strong class="me-2">Titular:</strong> <?= htmlspecialchars($datos['Titular']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-calendar-check me-2 text-secondary"></i>
            <strong class="me-2">Fecha Titular:</strong> <?= htmlspecialchars($datos['Fecha_Titular']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-clock-history me-2 text-secondary"></i>
            <strong class="me-2">Fecha de Registro:</strong> <?= htmlspecialchars($datos['FechaDeRegistro']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-info-square me-2 text-secondary"></i>
            <strong class="me-2">Estado:</strong> <?= htmlspecialchars($datos['Estado']) ?>
        </div>

        <!-- Botón -->
        <div class="text-center mt-4">
            <a href="compras.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Regresar al listado
            </a>
        </div>
    </div>
</div>


<!-- Bootstrap JS (opcional si no necesitas scripts interactivos) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>