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
  <?php
  $NombreGerente = $_SESSION['Name'];

  // Consulta
  $query = "SELECT 
    u.Nombre AS Empleado,
    u.Jerarquia,
    v.Dias_Solicitados,
    v.Fecha_Inicio,
    v.Fecha_Fin,
    v.Fecha_Solicitud,
    v.Tipo_Permiso,
    v.Estado,
    v.Id,
    vg.Dias_Restantes,
    vg.Dias_Solicitados AS Dias_Usados,
    vg.Antiguedad
  FROM 
    usuarios u
  INNER JOIN 
    vacaciones_solicitudes v ON u.Nombre = v.Usuario
  LEFT JOIN 
    vacaciones_general vg ON u.Nombre = vg.Usuario
  WHERE 
    u.Jerarquia = ?
    AND u.Estado = 'Activo'
    AND v.Estado = 'Proceso'";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $NombreGerente);
  $stmt->execute();
  $result = $stmt->get_result();

  // Guarda los resultados en un array
  $datos = [];
  while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
  }
  ?>
  <div class="container mt-5">
    <h2 class="text-center">Listado de Solicitudes de Vacaciones</h2>
    <hr>

    <?php if (count($datos) > 0): ?>
      <div class="row">
        <?php foreach ($datos as $row): ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
              <div class="card-header bg-primary text-white fw-bold">
                <?= htmlspecialchars($row['Empleado']) ?>
              </div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-6">
                    <small class="text-muted">Días solicitados</small><br>
                    <strong><?= $row['Dias_Solicitados'] ?></strong>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">Tipo de permiso</small><br>
                    <strong><?= $row['Tipo_Permiso'] ?></strong>
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-6">
                    <small class="text-muted">Fecha inicio</small><br>
                    <strong><?= $row['Fecha_Inicio'] ?></strong>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">Fecha fin</small><br>
                    <strong><?= $row['Fecha_Fin'] ?></strong>
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-6">
                    <small class="text-muted">Fecha solicitud</small><br>
                    <strong><?= $row['Fecha_Solicitud'] ?></strong>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">Estado</small><br>
                    <span class="badge bg-warning"><?= $row['Estado'] ?></span>
                  </div>
                </div>
                <hr>
                <div class="row text-muted mb-2">
                  <div class="col-6">
                    <small>Días Restantes:</small><br>
                    <strong><?= $row['Dias_Restantes'] ?? '—' ?></strong>
                  </div>
                  <div class="col-6">
                    <small>Días Usados:</small><br>
                    <strong><?= $row['Dias_Usados'] ?? '—' ?></strong>
                  </div>
                </div>
                <div class="row text-muted mb-2">
                  <div class="col-12">
                    <small>Antigüedad:</small><br>
                    <strong><?= $row['Antiguedad'] ?? '—' ?></strong>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-light d-flex justify-content-between">
                <a href="../Back/changeState.php?Id=<?= $row['Id'] ?>&Response=Aprobada&Fecha_Inicio=<?= $row['Fecha_Inicio'] ?>&Fecha_Fin=<?= $row['Fecha_Fin'] ?>&Nombre=<?= $row['Empleado'] ?>" class="btn btn-success btn-sm w-48">
                  <i class="bi bi-check-lg"></i> Aprobar
                </a>
                <a href="../Back/changeState.php?Id=<?= $row['Id'] ?>&Response=Rechazado&Fecha_Inicio=<?= $row['Fecha_Inicio'] ?>&Fecha_Fin=<?= $row['Fecha_Fin'] ?>&Nombre=<?= $row['Empleado'] ?>" class="btn btn-danger btn-sm w-48">
                  <i class="bi bi-x"></i> Rechazar
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info text-center mt-4">No hay registros por mostrar.</div>
    <?php endif; ?>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>