<?php
include "../Back/config/config.php";
session_start();

$conn = connectMySQLi();

// Número de registros por página
$registros_por_pagina = 7;

// Página actual (si no hay parámetro, asume la 1)
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Desde qué registro empezar
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Consulta para contar el total
$total_query = "SELECT COUNT(*) AS total FROM vacaciones_general";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_registros = $total_row['total'];

// Calcular el total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Vacaciones</title>
    <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
</head>

<body>
    <?php include "../Front/navbar.php"; ?>
    <!-- Listado del Personal Registrado -->
    <div class="card">
        <div class="card-body">
            <div class="container mt-5">
                <h1 class="text-center">Control de Vacaciones</h1>
                <br>
                <div class="row justify-content-center mb-3">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre...">
                    </div>
                </div>



                <div class="row justify-content-center">
                    <div class="col-md-12 text-center">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Dias Restantes</th>
                                    <th>Dias Solicitados</th>
                                    <th>Antiguedad</th>
                                    <th colspan="2">Acciones</th>
                                </tr>
                            </thead>
                            
                            <tbody id="vacation-list">
                                <?php
                                // Consulta paginada y ordenada alfabéticamente
                                $query = "SELECT * FROM vacaciones_general ORDER BY Usuario ASC LIMIT $inicio, $registros_por_pagina";
                                $result = mysqli_query($conn, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $row['Usuario'] . "</td>";
                                        echo "<td>" . $row['Dias_Restantes'] . "</td>";
                                        echo "<td>" . $row['Dias_Solicitados'] . "</td>";
                                        echo "<td>" . $row['Antiguedad'] . "</td>";
                                        // Ver detalles de esa persona:
                                        echo '<td><a href="Front/detalles.php?Nombre=' . $row['Usuario'] . '" class="btn btn-primary"><i class="bi bi-search"></i> Ver Detalles</a></td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No hay vacaciones registradas.</td></tr>";
                                }
                                ?>

                            </tbody>

                        </table>
                        <?php
                        echo '<nav aria-label="Paginación de usuarios">';
                        echo '<ul class="pagination justify-content-center">';

                        for ($i = 1; $i <= $total_paginas; $i++) {
                            $active = ($i == $pagina_actual) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?pagina=' . $i . '">' . $i . '</a>';
                            echo '</li>';
                        }

                        echo '</ul>';
                        echo '</nav>';

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const busqueda = this.value;

    fetch('Back/buscar_usuarios.php?busqueda=' + encodeURIComponent(busqueda))
        .then(response => response.text())
        .then(data => {
            document.getElementById('vacation-list').innerHTML = data;
        });
});
</script>



</body>

</html>