<?php
include "../Back/config/config.php";
require '../vendor/autoload.php'; // Asegúrate de que la ruta sea correcta

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

session_start();
$conn = connectMySQLi();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Compras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            margin-top: 10px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .upload-section {
            margin-top: 100px;
            transition: all 0.4s ease;
        }

        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .fade-in.show {
            opacity: 1;
            transform: translateY(0);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table thead th {
            background-color: #007bff;
            color: white;
            vertical-align: middle;
            white-space: nowrap;
        }

        table td input {
            min-width: 120px;
            border-radius: 6px;
            border: 1px solid #ced4da;
        }

        table td input:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .card {
            border-radius: 10px;
            border: none;
        }

        .card-header {
            padding: 1rem 1.25rem;
        }

        .module-section {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .module-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .bg-light {
            background-color: #f8fafc !important;
        }

        .btn {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress {
            border-radius: 3px;
        }
    </style>
</head>

<body>

    <?php require '../Front/navbar.php'; ?>

    <div class="container py-4">
        <div class="row g-4">
            <!-- Card izquierda - Formulario y navegación -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Carga de Archivos</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-4">
                            <h6 class="text-muted mb-3"><i class="bi bi-folder2-open me-2"></i>Navegación</h6>
                            <a href="index.php" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-search me-2"></i>Navegar en los registros
                            </a>
                        </div>
                        <?php 
                        /// Delimitar a que solo Berenice Salazar pueda actualizar el archivo:
                        if ($_SESSION['User_Id'] == 116){
                        ?>
                        <div class="mt-auto">
                            <h6 class="text-muted mb-3"><i class="bi bi-file-earmark-excel me-2"></i>Carga desde SAP</h6>
                            <p class="text-muted small">Sube tu archivo Excel exportado desde SAP para continuar con el registro.</p>

                            <form method="POST" enctype="multipart/form-data" action="Back/GetLineasAbiertas.php">
                                <div class="mb-3">
                                    <input type="file" name="archivo_excel" class="form-control" required accept=".xlsx, .xls, .csv">
                                </div>
                                <button type="submit" name="procesar" class="btn btn-success w-100 py-2">
                                    <i class="bi bi-cloud-upload me-2"></i>Procesar Archivo
                                </button>
                            </form>
                        </div>
                        <?php 
                        }
                        ?>

                    </div>
                </div>
            </div>

            <!-- Card derecha - Compras y Ventas -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Módulos</h5>
                    </div>
                    <div class="card-body">
                        <!-- Sección Compras -->
                        <div class="module-section mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="bi bi-cart4 me-2"></i>Compras
                                </h6>
                                <span class="badge bg-primary">Activo</span>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="listado.php" class="btn btn-outline-info">
                                    <i class="bi bi-list-ul me-2"></i>Mostrar Pestañas Contiguas
                                </a>
                                <!-- 
                                <a href="pestañas.php" class="btn btn-warning">
                                    <i class="bi bi-cloud-download me-2"></i>Cargar Pestañas Contiguas
                                </a> -->
                            </div>
                        </div>

                        <!-- Sección Ventas -->
                        <div class="module-section p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-success">
                                    <i class="bi bi-graph-up me-2"></i>Ventas
                                </h6>
                                 <span class="badge bg-primary">Activo</span>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="listadoVentas.php" class="btn btn-outline-info">
                                    <i class="bi bi-list-ul me-2"></i>Mostrar Pestañas (Ventanas)
                                </a>
                                <!-- 
                                <a href="pestañas_ventas.php" class="btn btn-warning">
                                    <i class="bi bi-cloud-download me-2"></i>Cargar Pestañas Contiguas (Ventas)
                                </a> -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>