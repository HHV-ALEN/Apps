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
        
        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-receipt detalle-icon me-2"></i>
            <span class="detalle-label me-2">Orden de Compra:</span> <?= htmlspecialchars($datos['OrdenCompra']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-person-circle detalle-icon me-2"></i>
            <span class="detalle-label me-2">Cliente:</span> <?= htmlspecialchars($datos['NombreCliente']) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-calendar-event detalle-icon me-2"></i>
            <span class="detalle-label me-2">Fecha:</span> <?= date('d/m/Y', strtotime($datos['Fecha'])) ?>
        </div>

        <div class="mb-3 d-flex align-items-center">
            <i class="bi bi-box-seam detalle-icon me-2"></i>
            <span class="detalle-label me-2">Código de Ítem:</span> <?= htmlspecialchars($datos['CodigoItem']) ?>
        </div>

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