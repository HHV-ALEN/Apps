<?php
require '../vendor/autoload.php';
include "../Back/config/config.php";
session_start();

$conn = connectMySQLi();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM compras_lineasabiertas WHERE Id = $id";
$resultado = $conn->query($query);
$datos = $resultado->fetch_assoc();

if (!$datos) {
    echo "<div class='container mt-5'><h4>❌ Registro no encontrado</h4></div>";
    exit;
}

//print_r($_SESSION);
// Departamento = Compras


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
        .info-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-item i {
            font-size: 1.2rem;
            min-width: 30px;
        }

        .info-item div {
            display: flex;
            flex-direction: column;
            margin-left: 10px;
        }

        .label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .value {
            font-weight: 500;
            color: #212529;
        }

        .section-header {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 20px;
        }

        .section-header i {
            font-size: 1.2rem;
        }

        .section-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card {
            border-radius: 10px;
            border: none;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>

<body>

    <?php require '../Front/navbar.php'; ?>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Detalles de la Compra #<?= $id ?></h4>
            </div>

            <div class="card-body">
                <!-- Comentarios de parte de Departamento de Compras : -->
                <div class="row mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            if ($_SESSION['Departamento'] == 'Compras') {
                            ?>
                                <form action="Back/agregarComentario.php" method="POST">
                                    <input type="hidden" name="id_compra" value="<?= $id ?>">
                                    <div class="mb-3">
                                        <label for="comentario" class="form-label">Comentario</label>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            <div>
                                <span class="label">Estatus</span>
                                <span class="value"><?php

                                                    // Si la variable $datos['Status_Comentario'] esta Vacia imprimir N/A
                                                    echo empty($datos['Status_Comentario']) ? 'N/A' : htmlspecialchars($datos['Status_Comentario']);
                                                    ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-file-earmark-check text-primary"></i>
                            <div>
                                <span class="label">Orden de Compra</span>
                                <span class="value"><?php
                                                    // Si la variable $datos['Fecha_Llegada'] esta Vacia imprimir N/A
                                                    echo empty($datos['Fecha_Llegada']) ? 'N/A' : htmlspecialchars($datos['Fecha_Llegada']);
                                                    ?></span>
                            </div>
                        </div>

                    </div>

                </div>


                <hr>


                <!-- Primera fila: Información principal -->
                <div class="row mb-4">
                    <!-- Columna 1: Datos de identificación -->
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            <div>
                                <span class="label">Orden de Venta</span>
                                <span class="value"><?= htmlspecialchars($datos['OrdenVenta']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-file-earmark-check text-primary"></i>
                            <div>
                                <span class="label">Orden de Compra</span>
                                <span class="value"><?= htmlspecialchars($datos['OrdenCompra']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-building text-success"></i>
                            <div>
                                <span class="label">Cliente</span>
                                <span class="value"><?= htmlspecialchars($datos['Cliente']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-calendar-date text-info"></i>
                            <div>
                                <span class="label">Fecha de Contabilización</span>
                                <span class="value"><?= date('d/m/Y', strtotime($datos['Fecha_Contabilizacion'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Datos de vendedor/titular -->
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-person-badge text-secondary"></i>
                            <div>
                                <span class="label">Vendedor</span>
                                <span class="value"><?= htmlspecialchars($datos['Vendedor']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-person-vcard text-secondary"></i>
                            <div>
                                <span class="label">Titular</span>
                                <span class="value"><?= htmlspecialchars($datos['Titular']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-shop text-secondary"></i>
                            <div>
                                <span class="label">Almacén</span>
                                <span class="value"><?= htmlspecialchars($datos['CodAlmacen']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-globe-americas text-secondary"></i>
                            <div>
                                <span class="label">País</span>
                                <span class="value"><?= htmlspecialchars($datos['Pais']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Segunda fila: Artículo -->
                <div class="section-header mb-3">
                    <i class="bi bi-box-seam me-2"></i>
                    <h5 class="mb-0">Información del Artículo</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-upc-scan text-warning"></i>
                            <div>
                                <span class="label">Código Item</span>
                                <span class="value"><?= htmlspecialchars($datos['CodItem']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-tag text-warning"></i>
                            <div>
                                <span class="label">Núm. Artículo</span>
                                <span class="value"><?= htmlspecialchars($datos['NumArticulo']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-card-text text-warning"></i>
                            <div>
                                <span class="label">Descripción</span>
                                <span class="value"><?= htmlspecialchars($datos['Descripcion']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-box text-warning"></i>
                            <div>
                                <span class="label">Cantidad Abierta</span>
                                <span class="value"><?= htmlspecialchars($datos['Cantidad_Abierta']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tercera fila: Costos -->
                <div class="section-header mb-3">
                    <i class="bi bi-cash-stack me-2"></i>
                    <h5 class="mb-0">Información Financiera</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-currency-dollar text-danger"></i>
                            <div>
                                <span class="label">Precio</span>
                                <span class="value">$<?= htmlspecialchars($datos['Precio']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-receipt text-danger"></i>
                            <div>
                                <span class="label">Importe</span>
                                <span class="value">$<?= htmlspecialchars($datos['Importe']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-currency-exchange text-danger"></i>
                            <div>
                                <span class="label">Precio OV</span>
                                <span class="value">$<?= htmlspecialchars($datos['PrecioOv']) ?></span>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <i class="bi bi-coin text-danger"></i>
                            <div>
                                <span class="label">Moneda</span>
                                <span class="value"><?= htmlspecialchars($datos['Moneda']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cuarta fila: Otros datos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-graph-up text-info"></i>
                            <div>
                                <span class="label">Utilidad</span>
                                <span class="value"><?= htmlspecialchars($datos['Utilidad']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <i class="bi bi-calendar-check text-info"></i>
                            <div>
                                <span class="label">Fecha de Registro</span>
                                <span class="value"><?= htmlspecialchars($datos['Fecha_Registro']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-center">
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left-circle me-2"></i>Volver al listado
                </a>
            </div>
        </div>
    </div>

    <br><br>



    <!-- Bootstrap JS (opcional si no necesitas scripts interactivos) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>