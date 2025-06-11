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
        <?php 
        $colorRow = 'white';
        
        
        while ($row = $result->fetch_assoc()): 
            if ($row['Estado'] == 'Inactivo'){
                $colorRow = '	 #8c0604; color:white'; // Cambia el color de fondo para usuarios inactivos
            } else {
                $colorRow = 'white'; // Color por defecto para usuarios activos
            }
            ?>
            
            <tr style="background-color: <?= $colorRow ?>;">
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
                    <!-- Botón para abrir el modal y pasar datos -->
                    <button class="btn btn-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#editUserModal"
                        data-id="<?php echo $row['Id']; ?>"
                        data-nombre="<?php echo htmlspecialchars($row['Nombre']); ?>"
                        data-email="<?php echo htmlspecialchars($row['Email']); ?>"
                        data-nombre="<?php echo htmlspecialchars($row['Nombre']); ?>"
                        data-email="<?php echo htmlspecialchars($row['Email']); ?>"
                        data-antiguedad="<?php echo htmlspecialchars($row['Fecha_Ingreso']); ?>"
                        data-area="<?php echo htmlspecialchars($row['Area']); ?>"
                        data-rol="<?php echo htmlspecialchars($row['Rol']); ?>"
                        data-sucursal="<?php echo htmlspecialchars($row['Sucursal']); ?>"
                        data-departamento="<?php echo htmlspecialchars($row['Departamento']); ?>"
                        data-jerarquia="<?php echo htmlspecialchars($row['Jerarquia']); ?>">
                        ✏️ Editar
                    </button>

                    <!-- Delete Button --><a href="Back/delete_user.php?id=<?php echo $row['Id']; ?>"
                        class="btn btn-danger"
                        onclick="return sweetConfirm(event, '<?php echo $row['Nombre']; ?>')">
                        <i class="bi bi-trash"></i> Eliminar
                    </a>
                </td>
            </tr>

            <!-- Modal para editar usuario -->
        <?php endwhile; ?>
    </tbody>
</table>