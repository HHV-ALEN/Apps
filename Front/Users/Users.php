<?php
include '../../Back/config/config.php';
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
      <link rel="icon" type="image/png" href="Img/Icono-A.png" />
   
</head>

<body>
    <?php
    include "../navbar.php";
    ?>

    <br>
    <div class="container text-center">
        <h1>Listado de Usuarios</h1>

        <!-- Button trigger: AGREGAR USUARIO -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
            Registrar Usuario
        </button>
        <br>
        <hr>

        <!-- Tabla de Usuarios-->
        <div class="table-responsive mt-4">
            <?php
            $conn = connectMySQLi();
            $query = "SELECT * FROM usuarios";
            $result = $conn->query($query);
            ?>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Username</th>
                        <th>Área</th>
                        <th>Puesto</th>
                        <th>Jerarquia</th>
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
                            <td><?php echo htmlspecialchars($row['Area']); ?></td>
                            <td><?php echo htmlspecialchars($row['Puesto']); ?></td>
                            <td><?php echo htmlspecialchars($row['Jerarquia']);?></td>
                            <td>
                                <!-- Edit Button (Triggers Collapse) -->
                                <button class="btn btn-warning" data-bs-toggle="collapse" data-bs-target="#editForm-<?php echo $row['Id']; ?>">Editar</button>
                                <!-- Delete Button -->
                                <a href="../../Back/usuarios/eliminar_usuario.php?id=<?php echo $row['Id']; ?>" class="btn btn-danger">Eliminar</a>
                            </td>
                        </tr>

                        <!-- Collapsible Edit Form Row -->
                        <tr class="collapse" id="editForm-<?php echo $row['Id']; ?>">
                            <td colspan="6">
                                <form action="../../Back/usuarios/actualizar_usuario.php" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $row['Id']; ?>">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($row['Nombre']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="fecha_ingreso" class="form-label">Fecha Ingreso</label>
                                        <input type="date" class="form-control" name="fecha_ingreso" value="<?php echo htmlspecialchars($row['Fecha_Ingreso']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="area" class="form-label">Área</label>
                                        <input type="text" class="form-control" name="area" value="<?php echo htmlspecialchars($row['Area']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="puesto" class="form-label">Puesto</label>
                                        <input type="text" class="form-control" name="puesto" value="<?php echo htmlspecialchars($row['Puesto']); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal: AGREGAR USUARIO -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document"> <!-- Increased size to modal-lg -->
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header bg-primary text-white"> <!-- Added background color -->
                        <h5 class="modal-title" id="addUserModalLabel">Agregar Usuario</h5>

                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
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
                                        <option value="Control">Control</option>
                                        <option value="Empleado">Empleado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 4: Área and Puesto -->
                            <div class="row mb-3">
                                <div class="form-group col-md-6">
                                    <label for="Area">Área</label>
                                    <input type="text" class="form-control" id="Area" name="Area" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="Puesto">Puesto</label>
                                    <input type="text" class="form-control" id="Puesto" name="Puesto" required>
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

                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Add smooth scroll to the opened form
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
      button.addEventListener('click', function() {
        const target = document.querySelector(button.getAttribute('data-bs-target'));
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    });
  });
</script>

</body>

</html>