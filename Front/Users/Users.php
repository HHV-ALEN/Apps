<?php
include '../../Back/config/config.php';
session_start();
$conn = connectMySQLi();
// Pagination variables
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Get total records count
$count_query = "SELECT COUNT(*) as total FROM usuarios WHERE Estado = 'Activo'";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch records for current page
$query = "SELECT * FROM usuarios WHERE Estado = 'Activo' ORDER BY Id ASC LIMIT $offset, $records_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="icon" type="image/png" href="../Img/Icono-A.png" />

    <style>
        .pagination {
            margin-top: 20px;
        }

        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .page-link {
            color: #0d6efd;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    include "../navbar.php";

    if (isset($_SESSION['respuesta'])) {
        $alerta = $_SESSION['tipo_respuesta'] ?? 'info';
        echo '<div class="alert alert-' . $alerta . ' alert-dismissible fade show mt-3 mx-3" role="alert">';
        echo $_SESSION['respuesta'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        echo '</div>';
        unset($_SESSION['respuesta']);
        unset($_SESSION['tipo_respuesta']);
    }
    ?>

    <br>
    <div class="container text-center">
        <h1>Listado de Usuarios</h1>

        <!-- Button trigger: AGREGAR USUARIO -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill"></i> Registrar Usuario
        </button>


        <!-- Modal: AGREGAR USUARIO -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document"> <!-- Increased size to modal-lg -->
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header bg-primary text-white"> <!-- Added background color -->
                        <h5 class="modal-title" id="addUserModalLabel">Agregar Usuario</h5>

                        <button type="button" class="btn-close" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <form action="../../Back/Users/addUser.php" method="POST">
                            <!-- Row 1: Nombre and Username -->
                            <div class="row mb-3"> <!-- Added margin-bottom (mb-3) for spacing -->
                                <div class="form-group col-md-6">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Username">Username</label>
                                    <input type="text" class="form-control" id="Username" name="Username" required>
                                </div>
                            </div>

                            <!-- Row 2: Email and Fecha Ingreso -->
                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="Correo">Email</label>
                                    <input type="email" class="form-control" id="Correo" name="Correo" required> <!-- Changed type to email -->
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Antiguedad">Fecha Ingreso</label>
                                    <input type="date" class="form-control" id="Antiguedad" name="Antiguedad" required>
                                </div>
                            </div>

                            <!-- Row 3: Jerarquia and Rol -->
                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="Contraseña">Contraseña</label> <!-- Fixed typo -->
                                    <input type="password" class="form-control" id="Contraseña" name="Contraseña" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Rol">Rol</label>
                                    <select class="form-control" id="Rol" name="Rol" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Gerente">Gerente</option>
                                        <option value="Coordinador">Coordinador</option>
                                        <option value="Empleado">Empleado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 4: Área and Departamento -->
                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="Area">Área</label>
                                    <input type="text" class="form-control" id="Area" name="Area" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Departamento">Departamento</label>
                                    <input type="text" class="form-control" id="Departamento" name="Departamento" required>
                                </div>
                            </div>

                            <!-- Row 5: Sucursal and Estado -->
                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="Sucursal">Sucursal</label>
                                    <select class="form-control" id="Sucursal" name="Sucursal" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Guadalajara">Guadalajara</option>
                                        <option value="Tijuana">Tijuana</option>
                                        <option value="Texas">Texas</option>
                                        <option value="Miami">Miami</option>
                                        <option value="CDMX">CDMX</option>
                                        <option value="Veracruz">Veracruz</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Jerarquia">Jerarquia</label> <!-- Fixed duplicate ID -->
                                    <input type="text" class="form-control" id="Jerarquia" name="Jerarquia" required>
                                </div>
                            </div>
                    </div>
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-aceptar" data-loading-text="Procesando...">Agregar</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End of Modal -->
        <br>
        <hr>

        <!-- Tabla de Usuarios-->
        <div class="table-responsive mt-4">
            <div class="mb-3">
                <input type="text" id="buscarNombre" class="form-control" placeholder="🔍 Buscar por nombre...">
            </div>
            <div id="tablaUsuarios">
                <table class="table table-bordered table-striped text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Fecha Ingreso</th>
                            <th>Jerarquia</th>
                            <th>Área</th>
                            <th>Rol</th>
                            <th>Sucursal</th>
                            <th>Departamento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <!-- User Row -->
                            <tr>
                                <td><?php echo htmlspecialchars($row['Id']); ?></td>
                                <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Fecha_Ingreso']); ?></td>
                                <td><?php echo htmlspecialchars($row['Jerarquia']); ?></td>
                                <td><?php echo htmlspecialchars($row['Area']); ?></td>
                                <td><?php echo htmlspecialchars($row['Rol']); ?></td>
                                <td><?php echo htmlspecialchars($row['Sucursal']); ?></td>
                                <td><?php echo htmlspecialchars($row['Departamento']); ?></td>
                                <td>
                                    <!-- Botón para abrir modal -->
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $row['Id']; ?>">
                                        ✏️ Editar
                                    </button>
                                    <!-- Delete Button -->
                                    <a href="Back/delete_user.php?id=<?php echo $row['Id']; ?>"
                                        class="btn btn-danger"
                                        onclick="return sweetConfirm(event, '<?php echo $row['Nombre']; ?>')">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <!-- Modal to edit user (for each user) -->
                            <!-- Fields: Nombre, Username, Email, Fecha_Ingreso, Jerarquia, Rol, Area, Departamento, Sucursal,  -->
                            <div class="modal fade" id="editUserModal<?php echo $row['Id']; ?>" tabindex="-1" aria-labelledby="editUserLabel<?php echo $row['Id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning text-white">
                                            <h5 class="modal-title" id="editUserLabel<?php echo $row['Id']; ?>">Editar Usuario</h5> &nbsp; <i class="bi bi-people-fill"></i>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>

                                        <form action="Back/edit_user.php" method="POST">
                                            <div class="modal-body">
                                                <!-- Campos del formulario -->
                                                <input type="hidden" name="id_usuario" value="<?php echo $row['Id']; ?>">
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="Nombre<?php echo $row['Id']; ?>" class="form-label">Nombre:</label>
                                                        <input type="text" class="form-control" name="Nombre" id="Nombre<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Nombre']); ?>" required>
                                                    </div>

                                                    <div class="mb-3 col-md-6">
                                                        <label for="Correo<?php echo $row['Id']; ?>" class="form-label">Correo:</label>
                                                        <input type="email" class="form-control" name="Correo" id="Correo<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Email']); ?>" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="Fecha_Ingreso<?php echo $row['Id']; ?>" class="form-label">Fecha de Ingreso:</label>
                                                        <input type="text" class="form-control" name="Fecha_Ingreso" id="Fecha_Ingreso<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Fecha_Ingreso']); ?>" required>
                                                    </div>

                                                    <div class="mb-3 col-md-6">
                                                        <label for="Jerarquia<?php echo $row['Id']; ?>" class="form-label">Superior Asignado:</label>
                                                        <input type="text" class="form-control" name="Jerarquia" id="Jerarquia<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Jerarquia']); ?>" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="Area<?php echo $row['Id']; ?>" class="form-label">Area:</label>
                                                        <input type="text" class="form-control" name="Area" id="Area<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Area']); ?>" required>
                                                    </div>

                                                    <div class="mb-3 col-md-6">
                                                        <label for="Rol<?php echo $row['Id']; ?>" class="form-label">Rol:</label>
                                                        <input type="text" class="form-control" name="Rol" id="Rol<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Rol']); ?>" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label for="Sucursal<?php echo $row['Id']; ?>" class="form-label">Sucursal:</label>
                                                        <input type="text" class="form-control" name="Sucursal" id="Sucursal<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Sucursal']); ?>" required>
                                                    </div>

                                                    <div class="mb-3 col-md-6">
                                                        <label for="Departamento<?php echo $row['Id']; ?>" class="form-label">Departamento:</label>
                                                        <input type="text" class="form-control" name="Departamento" id="Departamento<?php echo $row['Id']; ?>" value="<?php echo htmlspecialchars($row['Departamento']); ?>" required>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Previous Button -->
                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $current_page - 1 ?>"
                        aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <li class="page-item <?= ($page == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $page ?>">
                            <?= $page ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Next Button -->
                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="?page=<?= $current_page + 1 ?>"
                        aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>

    </div>


    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 Bundle con Popper incluido -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /// Animación para evitar que se den muchos clicks en el submit:
        document.addEventListener("DOMContentLoaded", function() {
            const botonesAceptar = document.querySelectorAll(".btn-aceptar");

            botonesAceptar.forEach(boton => {
                boton.addEventListener("click", function(e) {
                    if (boton.classList.contains("disabled")) {
                        e.preventDefault(); // Evita doble click
                        return;
                    }
                    boton.classList.add("disabled");
                    boton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...`;
                });
            });
        });


        // AJAX para buscar usuarios por nombre
        document.addEventListener('DOMContentLoaded', function() {
            const inputBusqueda = document.getElementById('buscarNombre');

            inputBusqueda.addEventListener('input', function() {
                const nombre = this.value;

                // AJAX con Fetch API
                fetch('Back/search_user.php?nombre=' + encodeURIComponent(nombre))
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('tablaUsuarios').innerHTML = data;
                    })
                    .catch(error => console.error('Error al buscar usuarios:', error));
            });
        });

        // Función asyncrona para La confirmación de eliminación de usuario
        async function sweetConfirm(event, userName) {
            event.preventDefault();
            const url = event.currentTarget.href;

            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Desactivar a ${userName}?`,
                text: "El usuario cambiará a estado Inactivo",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            });

            if (isConfirmed) {
                // Second confirmation
                const {
                    isConfirmed: finalConfirm
                } = await Swal.fire({
                    title: '¿Estás absolutamente seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Confirmar desactivación',
                    cancelButtonText: 'Cancelar'
                });

                if (finalConfirm) {
                    window.location.href = url;
                }
            }
            return false;
        }
    </script>


</body>

</html>