<?php
include("../../Back/config/config.php"); // --> Conexión con Base de dato
$conn = connectMySQLi();
session_start();
//print_r($_SESSION);


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Revisión de Solicitudes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
</head>

<body>
  <?php include "../../Front/navbar.php";
  if (isset($_SESSION['mensaje_alerta'])) {
    $claseAlerta = '';

    if ($_SESSION['accion'] == 'Aprobada') {
        $claseAlerta = 'success'; // verde
    } elseif ($_SESSION['accion'] == 'Rechazada') {
        $claseAlerta = 'danger'; // rojo
    } else {
        $claseAlerta = 'info'; // por si es otro tipo
    }

    echo "<div class='alert alert-{$claseAlerta} alert-dismissible fade show' role='alert'>
    {$_SESSION['mensaje_alerta']}
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
  </div>";


    unset($_SESSION['mensaje_alerta']);
    unset($_SESSION['accion']);
  }
  ?>

  <div class="container mt-5 text-center">
    <h2 class="text-center">Listado de Solicitudes de Vacaciones</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Responsable</th>
            <th>Días Solicitados</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Fecha de Solicitud</th>
            <th>Tipo de Permiso</th>
            <th>Estado</th>
            <th colspan="2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php $NombreGerente = $_SESSION['Name'];

          // Conexión a la base de datos (asumimos que ya está hecha)

          // Consulta para obtener solicitudes del personal a cargo del gerente actual
          $query = "
    SELECT 
        u.Nombre AS Empleado,
        u.Jerarquia,
        v.Dias_Solicitados,
        v.Fecha_Inicio,
        v.Fecha_Fin,
        v.Fecha_Solicitud,
        v.Tipo_Permiso,
        v.Estado,
        v.Id
    FROM 
        usuarios u
    INNER JOIN 
        vacaciones_solicitudes v ON u.Nombre = v.Usuario
    WHERE 
        u.Jerarquia = ?
        AND u.Estado = 'Activo'
        AND v.Estado = 'Proceso'
";

          $stmt = $conn->prepare($query);
          $stmt->bind_param("s", $NombreGerente);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($result->num_rows > 0) {
            // Mostrar resultados
            while ($row = $result->fetch_assoc()) {
              echo "<tr class=text-center>";
              echo "<td>" . htmlspecialchars($row['Empleado']) . "</td>";
              echo "<td>" . htmlspecialchars($row['Dias_Solicitados']) . "</td>";
              echo "<td>" . htmlspecialchars($row['Fecha_Inicio']) . "</td>";
              echo "<td>" . htmlspecialchars($row['Fecha_Fin']) . "</td>";
              echo "<td>" . htmlspecialchars($row['Fecha_Solicitud']) . "</td>";
              echo "<td>" . htmlspecialchars($row['Tipo_Permiso']) . "</td>";
              echo "<td><span class='badge bg-warning'>" . htmlspecialchars($row['Estado']) . "</span></td>";
              echo "<td><a href='../Back/changeState.php?Id=" . htmlspecialchars($row['Id']) . "&Response=Aprobada&Fecha_Inicio=" . htmlspecialchars($row['Fecha_Inicio']) . "&Fecha_Fin=" . htmlspecialchars($row['Fecha_Fin']) . "&Nombre=" . htmlspecialchars($row['Empleado']) . "'' class='btn btn-success'><i class='bi bi-check-lg'></i> Aprobar</a></td>";
              echo "<td><a href='../Back/changeState.php?Id=" .  htmlspecialchars($row['Id']) . "&Response=Rechazado&Fecha_Inicio=" . htmlspecialchars($row['Fecha_Inicio']) . "&Fecha_Fin=" . htmlspecialchars($row['Fecha_Fin']) . "&Nombre=" . htmlspecialchars($row['Empleado']) . "'' class='btn btn-danger'><i class='bi bi-x'></i> Rechazar</a></td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='8' class='text-center'>No hay solicitudes pendientes</td></tr>";
          }

          ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>