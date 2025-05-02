<?php


include '../../../Back/config/config.php';
$conn = connectMySQLi();
session_start(); // ¡No olvides esto!


$nombre = $_GET['nombre'] ?? '';

// Filtra los usuarios si hay búsqueda, si no, muestra todos
if (!empty($nombre)) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE CONVERT(Nombre USING utf8mb4) COLLATE utf8mb4_general_ci LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $nombre);
} else {
    $stmt = $conn->prepare("SELECT * FROM usuarios");
}
$stmt->execute();
$result = $stmt->get_result();

// Aquí copias el código que genera tu tabla
?>
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
            <tr>
                <td><?= htmlspecialchars($row['Id']) ?></td>
                <td><?= htmlspecialchars($row['Nombre']) ?></td>
                <td><?= htmlspecialchars($row['Username']) ?></td>
                <td><?= htmlspecialchars($row['Email']) ?></td>
                <td><?= htmlspecialchars($row['Fecha_Ingreso']) ?></td>
                <td><?= htmlspecialchars($row['Jerarquia']) ?></td>
                <td><?= htmlspecialchars($row['Area']) ?></td>
                <td><?= htmlspecialchars($row['Rol']) ?></td>
                <td><?= htmlspecialchars($row['Sucursal']) ?></td>
                <td><?= htmlspecialchars($row['Departamento']) ?></td>
                <td>
                    <!-- Botón para abrir modal -->
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $row['Id']; ?>">
                        ✏️ Editar
                    </button>
                    <!-- Delete Button -->
                    <a href="../../Back/usuarios/eliminar_usuario.php?id=<?php echo $row['Id']; ?>" class="btn btn-danger">Eliminar</a>
                </td>
            </tr>
            <div class="modal fade" id="editUserModal<?php echo $row['Id']; ?>" tabindex="-1" aria-labelledby="editUserLabel<?php echo $row['Id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title" id="editUserLabel<?php echo $row['Id']; ?>">Editar Usuario</h5> &nbsp; <i class="bi bi-people-fill"></i>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <form action="edit_user.php" method="POST">
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
            <!-- Modal para editar usuario -->
        <?php endwhile; ?>
    </tbody>
</table>