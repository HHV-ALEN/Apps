<?php
require_once("../config/db.php"); //Contiene las variables de configuracion para conectar a la base de datos
require_once("../config/conexion.php"); //Contiene funcion que conecta a la base de datos
ini_set('session.gc_maxlifetime', 3600); // 1 hour
session_set_cookie_params(3600); // Set cookie lifetime to match
session_start();
$Tipo_Usuario = $_SESSION['tipo'];
$Id_usuario = $_SESSION['user_id'];
//echo "Id Usuario: " . $Id_usuario;
//print_r($_SESSION);


/// ----------------------------- PAGINATION STUFF ----------------------------- ///

// Step 1: Define pagination variables
$perpage = 5; // Number of records per page
$Actual_Page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Current page, default is 1

// Ensure the current page is not less than 1
if ($Actual_Page < 1) {
    $Actual_Page = 1;
}

// Calculate the starting point for the query
$start = max(0, ($Actual_Page - 1) * $perpage);

// Step 2: Fetch total number of records
$query_total = "SELECT COUNT(*) AS total FROM salida_refactor";
$result_total = mysqli_query($con, $query_total);
$data_total = mysqli_fetch_assoc($result_total);
$total = $data_total['total']; // Total number of records

// Calculate total number of pages
$total_pages = ceil($total / $perpage);

// Ensure the current page does not exceed the total number of pages
if ($Actual_Page > $total_pages) {
    $Actual_Page = $total_pages;
}

// Step 3: Fetch data for the current page
$query = "SELECT * FROM salida_refactor ORDER BY Id DESC LIMIT $start, $perpage";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado General</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your Custom CSS -->
    <link rel="stylesheet" href="css/index.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    include("navbar.php");
    ?>
    <br>

    <div class="container">
        <div class="row text-center">
            <div class="col-md-12">
                <h2>Listado General</h2>
                <?php
                if ($Tipo_Usuario == "entrega" || $Id_usuario == 97) {
                    ?>
                    <hr>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarModal">
                        Agregar Nueva Etiqueta de Salida
                    </button>

                    <?php
                }
                ?>
            </div>
        </div>
        <br>
        <hr>
        <!-- Filtros -->

        <!-- Filtros -->
        <div>
            <label for="filter_id">Buscar por ID:</label>
            <input type="text" id="filter_id" placeholder="Ingrese ID">
            <label for="filter_status">Estado:</label>
            <select id="filter_status">
                <option value="">Todos</option>
                <option value="Empaque">Empaque</option>
                <option value="Logistica">Logistica</option>
                <option value="Facturacion">Facturacion</option>
                <option value="Entrega">Entrega</option>
                <option value="A Ruta">A Ruta</option>
                <option value="Completado">Completado</option>

            </select>
            <button id="btn_filtrar">Filtrar</button>
        </div>
        <br>

        <!-- Tabla donde se mostrarán los resultados -->
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Cliente</th>
                        <th>Estado</th>
                        <th colspan="2">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla_resultados">
                    <!-- Aquí se insertan los resultados dinámicos -->
                </tbody>
            </table>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("btn_filtrar").addEventListener("click", function () {
                    let id_salida = document.getElementById("filter_id").value;
                    let estado = document.getElementById("filter_status").value;

                    console.log("⏳ Enviando datos al backend...");
                    console.log("ID Salida:", id_salida);
                    console.log("Estado:", estado);

                    let formData = new FormData();
                    formData.append("query_salida", id_salida);
                    formData.append("query_status", estado);

                    fetch("filtros.php", {
                        method: "POST",
                        body: formData
                    })
                        .then(response => response.text())
                        .then(data => {
                            console.log("✅ Respuesta recibida del backend:");
                            console.log(data);
                            document.getElementById("tabla_resultados").innerHTML = data;
                        })
                        .catch(error => console.error("❌ Error en la petición:", error));
                });
            });

        </script>


        <br>
        <hr>

        <!--- Tabla Listado General -->
        <div class="container">
            <div class="table-responsive">
                <table class="table table-striped table-bordered custom-table">
                    <thead>
                        <tr>
                            <th>No. Salida</th>
                            <th>Nombre Cliente</th>
                            <th>Estado</th>
                            <th>Sucursal</th>
                            <th colspan="2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch and display records for the current page
                        while ($row = mysqli_fetch_array($result)) {
                            $id_salida = $row['Id'];
                            $id_cliente = $row['Id_Cliente'];
                            $Nombre_Cliente = $row['Nombre_Cliente'];
                            $Id_Status = $row['Id_Status'];
                            $Estado = $row['Estado'];
                            $Id_Sucursal = $row['Id_Sucursal'];
                            $Sucursal = $row['Sucursal'];
                            ?>
                            <tr>
                                <td><?php echo $id_salida; ?></td>
                                <td><?php echo $Nombre_Cliente; ?></td>
                                <td><?php echo $Estado; ?></td>
                                <td><?php echo $Sucursal; ?></td>
                                <td>
                                    <a href="detalles2.php?id_salida=<?php echo $id_salida; ?>"
                                        class="btn btn-primary">Detalles</a>
                                    <?php
                                    if ($Id_Status == 21 && $Tipo_Usuario == "Empaque") {
                                        ?>
                                        <a href="Back/changeState.php?id_salida=<?php echo $id_salida; ?>&Estado=Empaque"
                                            class="btn btn-warning">Recibir Pedido (Empaque)</a>
                                        <?php
                                    } elseif ($Id_Status == 22 && $Tipo_Usuario == "Facturacion") {
                                        ?>
                                        <a href="Back/changeState.php?id_salida=<?php echo $id_salida; ?>&Estado=Facturacion"
                                            class="btn btn-warning">Recibir Pedido (Facturación)</a>
                                        <?php
                                    } elseif ($Id_Status == 24 && $Tipo_Usuario == "Logistica") {
                                        ?>
                                        <button type="button" class="btn btn-info logistica-btn" data-bs-toggle="modal"
                                            data-bs-target="#logisticaModal" data-id_salida="<?php echo $id_salida; ?>">
                                            Logística
                                        </button>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Previous button
                        if ($Actual_Page > 1) {
                            ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $Actual_Page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php
                        }

                        // Page numbers
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $Actual_Page) {
                                ?>
                                <li class="page-item active">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php
                            } else {
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php
                            }
                        }

                        // Next button
                        if ($Actual_Page < $total_pages) {
                            ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $Actual_Page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    </div>

    <!--     Zona de Modales     -->
    <!-- Modal de Logística - Pre guia-->
    <div class="modal fade" id="logisticaModal" tabindex="-1" aria-labelledby="logisticaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logisticaModalLabel">Formulario de Pre Guía</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de logística -->
                    <form action="Back/PreGuia.php" method="POST" id="form_logistica">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">

                                    <input type="text" class="form-control" id="modal_id_salida" name="modal_id_salida"
                                        readonly>
                                    <label for="modal_id_salida">ID Salida</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="id_cliente" name="id_cliente"
                                        value="<?php echo $Nombre_Cliente; ?>" readonly>
                                    <label for="id_cliente">Cliente</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <select class="form-select" name="Tipo_Doc" id="Tipo_Doc" required>
                                        <option value="">Selecciona una opción</option>
                                        <option value="Directo">Directo</option>
                                        <option value="Reembarque">Reembarque</option>
                                        <option value="Ruta">Ruta</option>
                                    </select>
                                    <label for="Tipo_Doc">Tipo de Documento</label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Sección dinámica -->
                        <div id="extraFields"></div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
