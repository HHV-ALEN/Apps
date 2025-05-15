<?php 
include "../Back/config/config.php";
session_start();
print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];
$Nombre_Usuario_Activo = $_SESSION['Name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asignar RIO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table-container {
      margin-top: 40px;
    }
    .btn-sm {
      font-size: 0.875rem;
    }
  </style>
</head>
<body class="bg-light">
 <?php include "../Front/navbar.php"; ?>
<div class="container table-container">
  <h3 class="text-center mb-4">Asignar RIO a Personal</h3>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php
          // $Nombre_Usuario_Activo = tu variable actual

          $query = "SELECT id, nombre FROM usuarios WHERE Jerarquia = '$Nombre_Usuario_Activo'";
          $resultado = mysqli_query($conn, $query);

          while ($row = mysqli_fetch_assoc($resultado)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nombre']}</td>
                    <td>
                      <button 
                        class='btn btn-primary btn-sm' 
                        data-bs-toggle='modal' 
                        data-bs-target='#modalAsignar' 
                        data-id='{$row['id']}' 
                        data-nombre='{$row['nombre']}'>
                        Asignar RIO
                      </button>
                    </td>
                  </tr>";
          }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Asignación -->
<div class="modal fade" id="modalAsignar" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="Back/asignar_rio.php" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAsignarLabel">Asignar RIO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_empleado" id="id_empleado">
        <div class="mb-3">
          <label for="nombre_empleado" class="form-label">Empleado</label>
          <input type="text" class="form-control" id="nombre_empleado" readonly>
        </div>
        <div class="mb-3">
          <label for="archivo" class="form-label">Archivo RIO</label>
          <input type="file" class="form-control" name="archivo" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="anio" class="form-label">Año</label>
            <input type="number" name="anio" class="form-control" value="<?= date('Y') ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="mes" class="form-label">Mes</label>
            <select name="mes" class="form-select" required>
              <?php
                $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                foreach ($meses as $mes) {
                  echo "<option value='$mes'>$mes</option>";
                }
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Asignar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const modalAsignar = document.getElementById('modalAsignar');
  modalAsignar.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const nombre = button.getAttribute('data-nombre');
    
    document.getElementById('id_empleado').value = id;
    document.getElementById('nombre_empleado').value = nombre;
  });
</script>

</body>
</html>
