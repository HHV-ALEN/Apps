<?php
require '../vendor/autoload.php';
include "../Back/config/config.php";
session_start();

$conn = connectMySQLi();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM compras_cargaventas WHERE Id = $id";
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
    <meta charset="utf-8" />
    <title>Detalle de Compra</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap‑icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        /* --- Estilos extra --- */
        body {
            background: #f4f7fc;
        }

        .detalle-card {
            max-width: 720px;
            margin: auto;
        }

        .detalle-label {
            font-weight: 500;
        }

        .detalle-icon {
            font-size: 1.1rem;
            color: #0d6efd;
        }

        .valor-vacio {
            color: #999;
            font-style: italic;
        }

        /* Deja que la descripción se ajuste bien en móvil */
        @media (max-width:576px) {
            .descripcion {
                white-space: normal !important;
            }
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .detail-section {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 1.25rem;
        }

        .section-title {
            color: #3a7bd5;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .detail-item i {
            font-size: 1.25rem;
            min-width: 30px;
        }

        .detail-label {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            display: block;
            font-weight: 500;
            color: #212529;
        }

        .description-text {
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .btn-primary {
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>

<body>
    <?php require '../Front/navbar.php'; ?>

    <div class="container py-4">
        <!-- Tarjeta principal -->
        <div class="card border-0 shadow-lg">
            <!-- Encabezado con gradiente -->
            <div class="card-header bg-gradient-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Detalles de la Venta #<?= htmlspecialchars($datos['Id']) ?>
                    </h5>
                    <span class="badge bg-light text-dark">
                        <?= htmlspecialchars($datos['Estado'] ?? 'Activo') ?>
                    </span>
                </div>
            </div>


            <!-- Cuerpo de la tarjeta -->
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            if ($_SESSION['Departamento'] == 'Compras') {
                            ?>
                                <form action="Back/agregarComentarioVenta.php" method="POST">
                                    <input type="hidden" name="id_compra" value="<?= $id ?>">
                                    <div class="mb-3">
                                        <label for="comentario" class="form-label">Comentario del estado:</label>
                                        <input class="form-control" id="comentario" name="comentario" value="<?php echo $datos['Status_Comentario']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fecha_recibimiento" class="form-label">Fecha de Recibimiento</label>
                                        <input type="date" class="form-control" id="fecha_recibimiento" name="fecha_recibimiento" value="<?php echo $datos['Fecha_Llegada']; ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Agregar Comentario</button>
                                </form>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <hr>

                <!-- Columna izquierda -->
                <div class="detail-section mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                <div>
                                    <span class="detail-label">Comentario del Estado:</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['Status_Comentario']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                <div>
                                    <span class="detail-label">Fecha de Llegada</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['Fecha_Llegada']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Sección 1: Información principal -->
                <div class="detail-section mb-4">
                    <div class="row g-3">
                        <!-- Columna izquierda -->
                        <div class="col-md-6">

                            <div class="detail-item">
                                <i class="bi bi-building text-primary"></i>
                                <div>
                                    <span class="detail-label">Cliente</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['Cliente']) ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <div>
                                    <span class="detail-label">País</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['Pais']) ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-shop text-primary"></i>
                                <div>
                                    <span class="detail-label">Almacén</span>
                                    <span class="detail-value">
                                        <?php

                                        switch ($datos['Almacen']) {
                                            case 1:
                                                echo "Guadalajara";
                                                break;
                                            case 2:
                                                echo "Veracruz";
                                                break;
                                            case 3:
                                                echo "CDMX";
                                                break;
                                            case 4:
                                                echo "Tijuana";
                                                break;
                                            case 5:
                                                echo "5";
                                                break;
                                            case 6:
                                                echo "Internacional";
                                                break;
                                            case 13:
                                                echo "Ingeniería";
                                                break;

                                            default:
                                                echo "<small>No Asignado</small>";
                                                break;
                                        }
                                        ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-calendar-date text-primary"></i>
                                <div>
                                    <span class="detail-label">Fecha Contabilización</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['FechaContabilizacion']) ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="bi bi-calendar-check text-primary"></i>
                                <div>
                                    <span class="detail-label">Fecha de Registro</span>
                                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($datos['Fecha_Registro'])) ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="bi bi-calendar-check text-primary"></i>
                                <div>
                                    <span class="detail-label">Fecha de Vencimiento</span>
                                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($datos['FechaVencimiento'])) ?></span>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Línea divisoria -->
                <hr class="my-4">

                <!-- Sección 2: Información del artículo -->
                <div class="detail-section mb-4">
                    <h6 class="section-title mb-3">
                        <i class="bi bi-box-seam me-2"></i>Información del Artículo
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <i class="bi bi-upc-scan text-warning"></i>
                                <div>
                                    <span class="detail-label">Número de Artículo</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['NumArticulo']) ?></span>
                                </div>
                            </div>

                            <div class="detail-item mt-3">
                                <i class="bi bi-card-text text-warning"></i>
                                <div>
                                    <span class="detail-label">Descripción</span>
                                    <span class="detail-value description-text"><?= htmlspecialchars($datos['Descripcion']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item mt-3">
                                <i class="bi bi-card-text text-warning"></i>
                                <div>
                                    <span class="detail-label">Orden de Compra</span>
                                    <span class="detail-value description-text"><?= htmlspecialchars($datos['OrdenCompra']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Línea divisoria -->
                <hr class="my-4">

                <!-- Sección 3: Información financiera -->
                <div class="detail-section">
                    <h6 class="section-title mb-3">
                        <i class="bi bi-cash-stack me-2"></i>Información Financiera
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-box text-danger"></i>
                                <div>
                                    <span class="detail-label">Cantidad</span>
                                    <span class="detail-value"><?= htmlspecialchars($datos['Cantidad_Abierta']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-currency-dollar text-danger"></i>
                                <div>
                                    <span class="detail-label">Precio Unitario</span>
                                    <span class="detail-value">$<?= number_format($datos['Precio'], 2) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-person-vcard text-danger"></i>
                                <div>
                                    <span class="detail-label">Titular</span>
                                    <span class="detail-value"><?= $datos['Titular'] ? htmlspecialchars($datos['Titular']) : '<span class="text-muted">No asignado</span>' ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <i class="bi bi-person-vcard text-danger"></i>
                                <div>
                                    <span class="detail-label">Moneda</span>
                                    <span class="detail-value"><?= $datos['Moneda'] ? htmlspecialchars($datos['Moneda']) : '<span class="text-muted">No asignado</span>' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-center mt-5 gap-3">
                    <a href="listadoVentas.php" class="btn btn-outline-primary px-4 rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i>Volver al listado
                    </a>
                    <!-- 
                <button class="btn btn-primary px-4 rounded-pill">
                    <i class="bi bi-printer me-2"></i>Imprimir
                </button> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>