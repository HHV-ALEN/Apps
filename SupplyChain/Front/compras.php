<?php
include "../../Back/config/config.php";
require '../../vendor/autoload.php'; // Asegúrate de que la ruta sea correcta

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
            margin-top: 100px;
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
    </style>
</head>

<body>

    <?php require_once("../../Front/navbar.php"); ?>

    <div class="container text-center">
        <h2 class="mb-4">📦 Líneas Abiertas</h2>
        <p class="lead">Sube tu archivo Excel exportado desde SAP para continuar con el registro.</p>

        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <input type="file" name="archivo_excel" class="form-control mb-3" required>
            <button type="submit" name="procesar" class="btn btn-success">📤 Procesar Archivo</button>
        </form>
    </div>

    <?php
    if (isset($_POST['procesar'])) {
        $archivoTmp = $_FILES['archivo_excel']['tmp_name'];
        $spreadsheet = IOFactory::load($archivoTmp);
        $hoja = $spreadsheet->getActiveSheet();
        $datos = $hoja->toArray(null, true, true, true);

        // Solo estas columnas deseamos
        $columnasDeseadas = ['A', 'B', 'C', 'J'];

        echo "<form method='post' action='../Back/Compras/guardar_compras.php'>";
        echo '<div class="container mt-5">';
        echo "<h4 class='mb-4'>📄 Vista previa de datos cargados</h4>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-hover align-middle text-center'>";

        // Encabezados fijos
        echo "<thead class='table-primary'><tr>";
        foreach ($columnasDeseadas as $col) {
            $titulo = $datos[1][$col] ?? $col;
            echo "<th>" . htmlspecialchars($titulo) . "</th>";
        }
        echo "</tr></thead><tbody>";

        // Filas de datos desde la fila 2
        foreach ($datos as $index => $filaData) {
            if ($index === 1) continue; // Saltar encabezado

            echo "<tr>";
            foreach ($columnasDeseadas as $col) {
                $valor = $filaData[$col] ?? '';
                $extraClass = ($col === 'B') ? 'form-control-lg' : '';

                // Si es la columna J (fecha), intenta convertir
                if ($col === 'J') {
                    if (is_numeric($valor)) {
                        try {
                            $valor = Date::excelToDateTimeObject($valor)->format('Y-m-d');
                        } catch (Exception $e) {
                            $valor = ''; // En caso de error
                        }
                    }
                    echo "<td><input type='date' name='data[$index][$col]' value='$valor' class='form-control'></td>";
                } else {
                    $inputValue = htmlspecialchars($valor);
                    echo "<td><input type='text' name='data[$index][$col]' value='$inputValue' class='form-control $extraClass'></td>";
                }
            }
            echo "</tr>";
        }

        echo "</tbody></table></div>";
        echo "<div class='text-center mt-3'><button type='submit' name='guardar' class='btn btn-success btn-lg'>💾 Guardar en BD</button></div>";
        echo "</div></form>";
    }
    ?>

    <!--------------------------------------------------------------------------------------------------------------------------------------------->
    <hr>
    <br>

    <?php
    // Configuración de paginación
    $por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina > 1) ? ($pagina * $por_pagina - $por_pagina) : 0;

    // Obtener filtros desde GET
    $ordenFiltro   = $conn->real_escape_string($_GET['orden'] ?? '');
    $clienteFiltro = $conn->real_escape_string($_GET['cliente'] ?? '');
    $itemFiltro    = $conn->real_escape_string($_GET['item'] ?? '');

    // Construir cláusula WHERE dinámica
    $where = [];
    if ($ordenFiltro !== '')   $where[] = "OrdenCompra LIKE '%$ordenFiltro%'";
    if ($clienteFiltro !== '') $where[] = "NombreCliente LIKE '%$clienteFiltro%'";
    if ($itemFiltro !== '')    $where[] = "CodigoItem LIKE '%$itemFiltro%'";

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Obtener total con filtro
    $sql_total = "SELECT COUNT(*) AS total FROM supply_compras $where_sql";
    $total_resultado = $conn->query($sql_total);
    $total_filas = $total_resultado->fetch_assoc()['total'];
    $total_paginas = ceil($total_filas / $por_pagina);

    // Obtener datos con filtro + paginación
    $sql_data = "SELECT * FROM supply_compras $where_sql ORDER BY Id DESC LIMIT $inicio, $por_pagina";
    $resultado = $conn->query($sql_data);


    ?>

    <div class="container mt-5">
        <h3 class="mb-4">📝 Compras Registradas</h3>
        <div class="table-responsive">

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" id="filtroOrden" class="form-control" placeholder="Orden de Compra">
                </div>
                <div class="col-md-3">
                    <input type="text" id="filtroCliente" class="form-control" placeholder="Nombre del Cliente">
                </div>
                <div class="col-md-3">
                    <input type="text" id="filtroItem" class="form-control" placeholder="Código del Ítem">
                </div>
            </div>

            <div id="tabla-compras"></div>

        </div>
    </div>





    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cargarTabla(pagina = 1) {
            let orden = $('#filtroOrden').val();
            let cliente = $('#filtroCliente').val();
            let item = $('#filtroItem').val();

            $.ajax({
                url: '../Back/Compras/comprasFiltro.php',
                method: 'POST',
                data: {
                    orden: orden,
                    cliente: cliente,
                    item: item,
                    pagina: pagina
                },
                success: function(response) {
                    $('#tabla-compras').html(response);
                }
            });
        }

        // Al cargar la página
        $(document).ready(function() {
            cargarTabla();

            // Escuchar inputs
            $('#filtroOrden, #filtroCliente, #filtroItem').on('input', function() {
                cargarTabla();
            });

            // Delegar evento de paginación dinámica
            $(document).on('click', '.pagina', function(e) {
                e.preventDefault();
                let pagina = $(this).data('pagina');
                cargarTabla(pagina);
            });
        });
    </script>


    <script>
        // Animaciones iniciales
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.fade-in').forEach((el, i) => {
                setTimeout(() => el.classList.add('show'), i * 200);
            });
        });

        // Mostrar cargando
        const form = document.getElementById('uploadForm');
        form.addEventListener('submit', () => {
            document.getElementById('loading').classList.remove('d-none');
        });
    </script>

</body>

</html>