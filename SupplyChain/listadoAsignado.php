<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$nombreUsr = trim($_SESSION['Name']);        // "Georgina Reynoso Espinoza"
//echo "<br> Noombre de usuario: $nombreUsr <br>";
if ($nombreUsr == 'Karina De San Juan Pulido De La Cruz') {
  $nombreUsr = 'Karina Pulido';
}
function armarPatronLike($nombreCompleto)
{
  // ▸ Convierte "Georgina Reynoso Espinoza"
  //   en  "Georgina%Reynoso"
  $partes = explode(' ', $nombreCompleto, 3);   // quédamos con 1er y 2o token
  $patron = $partes[0] . '%' . $partes[1];      // Georgina%Reynoso
  return '%' . $patron . '%';                   // %Georgina%Reynoso%
}

$like = armarPatronLike($nombreUsr);
$sql_clientes = "
  SELECT Nombre_Cliente
  FROM   clientes_asignados
  WHERE  encargado LIKE ?
";

$stmt = mysqli_prepare($conn, $sql_clientes);
mysqli_stmt_bind_param($stmt, 's', $like);
mysqli_stmt_execute($stmt);
$result_clientes = mysqli_stmt_get_result($stmt);

$clientes_asignados = [];
while ($row = mysqli_fetch_assoc($result_clientes)) {
  $clientes_asignados[] = $row['Nombre_Cliente'];
}
/* 1) Verificar que hay clientes */
if (!$clientes_asignados) {
  echo "<p>No hay clientes asignados a este usuario.</p>";
  exit;
}

// 1) Convertir el array de clientes a una cadena adecuada para SQL
$clientes_escaped = array_map(function ($cliente) use ($conn) {
  return "'" . mysqli_real_escape_string($conn, $cliente) . "'";
}, $clientes_asignados);

$clientes_str = implode(',', $clientes_escaped);


/* Configuración de paginación */
$porPagina = 15; // Número de registros por página
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual
$offset = ($pagina - 1) * $porPagina;

// Inicializa condiciones WHERE adicionales
$condiciones = [];
$params = [];

// Filtro por Cliente
if (!empty($_POST['filtro_cliente'])) {
  $condiciones[] = "s.Nombre_Cliente = '" . mysqli_real_escape_string($conn, $_POST['filtro_cliente']) . "'";
}

// Filtro por ID Salida
if (!empty($_POST['filtro_id'])) {
  $condiciones[] = "s.Id = " . (int)$_POST['filtro_id'];
}

// Filtro por Orden de Venta
if (!empty($_POST['filtro_orden_venta'])) {
  $condiciones[] = "e.Id_Orden_Venta LIKE '%" . mysqli_real_escape_string($conn, $_POST['filtro_orden_venta']) . "%'";
}

// Filtro por Orden de entrega:
if (!empty($_POST['filtro_orden_entrega'])) {
  $condiciones[] = "e.Id_Entrega LIKE '%" . mysqli_real_escape_string($conn, $_POST['filtro_orden_entrega']) . "%'";
}

// Filtro por Id Factura:
if (!empty($_POST['filtro_Factura'])) {
  $condiciones[] = "e.Id_Factura LIKE '%" . mysqli_real_escape_string($conn, $_POST['filtro_Factura']) . "%'";
}

// Combina con los clientes asignados originales
$where = "s.Nombre_Cliente IN ($clientes_str)";
if (!empty($condiciones)) {
  $where .= " AND " . implode(" AND ", $condiciones);
}

// Consulta final
$sql_salidas = "
  SELECT s.*, e.Id_Orden_Venta, e.Id_Entrega, e.Id_Factura
  FROM salidas AS s
  LEFT JOIN entregas AS e ON e.Id_Salida = s.Id
  WHERE $where
  ORDER BY s.Id DESC
  LIMIT $porPagina OFFSET $offset
";

// 4) Consultar con mysqli 
$result_sql_salidas = mysqli_query($conn, $sql_salidas);
$salidas = [];
while ($fila = mysqli_fetch_assoc($result_sql_salidas)) {
  $salidas[] = $fila;
}


// Consulta para contar el total de registros
$sql_total = "
  SELECT COUNT(*) as total 
  FROM salidas AS s
  LEFT JOIN entregas AS e ON e.Id_Salida = s.Id
  WHERE s.Nombre_Cliente IN ($clientes_str)
";

$result_total = mysqli_query($conn, $sql_total);
$total_registros = mysqli_fetch_assoc($result_total)['total'];
$total_paginas = ceil($total_registros / $porPagina);

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Salidas asignadas</title>
  <link rel="icon" href="../Front/Img/Icono-A.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>
  <?php include "../Front/navbar.php"; ?>
  <br>
  <div class="container mb-4 p-3 bg-light rounded shadow-sm">
    <form method="POST" action="?" class="row g-3">
      <!-- Filtro por Cliente -->
      <div class="col-md-3">
        <label class="form-label">Cliente:</label>
        <select name="filtro_cliente" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach ($clientes_asignados as $cliente): ?>
            <option value="<?= htmlspecialchars($cliente) ?>"
              <?= isset($_POST['filtro_cliente']) && $_POST['filtro_cliente'] == $cliente ? 'selected' : '' ?>>
              <?= htmlspecialchars($cliente) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filtro por ID -->
      <div class="col-md-2">
        <label class="form-label">ID Salida:</label>
        <input type="number" name="filtro_id" class="form-control form-control-sm"
          value="<?= htmlspecialchars($_POST['filtro_id'] ?? '') ?>">
      </div>

      <!-- Filtro por Orden de Venta -->
      <div class="col-md-2">
        <label class="form-label">Orden Venta:</label>
        <input type="text" name="filtro_orden_venta" class="form-control form-control-sm"
          value="<?= htmlspecialchars($_POST['filtro_orden_venta'] ?? '') ?>">
      </div>

      <!-- Filtro por Orden de Entrega -->
      <div class="col-md-2">
        <label class="form-label">Orden Entrega:</label>
        <input type="text" name="filtro_orden_entrega" class="form-control form-control-sm"
          value="<?= htmlspecialchars($_POST['filtro_orden_entrega'] ?? '') ?>">
      </div>

      <!-- Filtro por Id Factura -->
      <div class="col-md-2">
        <label class="form-label">Id Factura:</label>
        <input type="text" name="filtro_Factura" class="form-control form-control-sm"
          value="<?= htmlspecialchars($_POST['id_factura'] ?? '') ?>">
      </div>

      <!-- Botones -->
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" name="aplicar_filtros" class="btn btn-primary btn-sm me-2">
          <i class="bi bi-funnel"></i> Filtrar
        </button>
        <a href="?" class="btn btn-danger btn-sm">
          <i class="bi bi-arrow-counterclockwise"></i> Limpiar
        </a>
      </div>
    </form>
  </div>

  <div class="container my-4">
    <h2 class="mb-3">Salidas de tus clientes asignados</h2>

    <?php if (!$salidas): ?>
      <div class="alert alert-info">No hay salidas registradas para tus clientes.</div>
    <?php else: ?>
      <table class="table table-striped table-sm">
        <thead class="table-dark">
          <tr class="text-center">
            <th>:D</th>
            <th>Folio</th>
            <th>Cliente</th>
            <th>Sucursal</th>
            <th>Status</th>
            <th>Orden De Venta</th>
            <th>Orden De Entrega</th>
            <th>Id Factura</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">

          <?php
          foreach ($salidas as $s):
            $idSalida = (int)$s['Id']; ?>
            <tr>
              <!-- Botón para abrir modal -->
              <td>
                <!-- Botón en la fila -->
                <button class="btn btn-outline-primary btn-sm btn-abrir-modal"
                  data-id="<?= $s['Id_Cliente'] ?>"
                  data-cliente="<?= htmlspecialchars($s['Nombre_Cliente']) ?>"
                  data-bs-toggle="modal"
                  data-bs-target="#modalAsignar">
                  Re‑asignar
                </button>
              </td>

              <!-- resto de columnas -->
              <td><?= htmlspecialchars($s['Id']) ?></td>
              <td><?= htmlspecialchars($s['Nombre_Cliente']) ?></td>
              <td><?= htmlspecialchars($s['Sucursal']) ?></td>
              <td><?= htmlspecialchars($s['Estado']) ?></td>
              <td><?= htmlspecialchars($s['Id_Orden_Venta'] ?? '') ?></td>
              <td><?= htmlspecialchars($s['Id_Entrega'] ?? '') ?></td>
              <td><?= $s['Id_Factura'] ? htmlspecialchars($s['Id_Factura']) : 'N/A' ?></td>
              <td>
                <a class="btn btn-warning btn-sm" href="Front/detalles.php?id=<?= $idSalida ?>">
                  <i class="bi bi-file-earmark-medical"></i> Detalles
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if ($total_paginas > 1): ?>
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <!-- Botón Anterior -->
            <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="?pagina=<?= $pagina - 1 ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>

            <!-- Números de página -->
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
              <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <!-- Botón Siguiente -->
            <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
              <a class="page-link" href="?pagina=<?= $pagina + 1 ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Un solo modal al final -->
  <div class="modal fade" id="modalAsignar" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" action="Back/Clientes/reasignar_cliente.php" method="POST">
        <!-- Inputs ocultos con datos -->
        <input type="hidden" name="Id_Salida" id="modalIdSalida">
        <input type="hidden" name="NombreCliente" id="modalNombreCliente">

        <div class="modal-body">
          <div class="row align-items-center mb-3">
            <!-- Cliente actual -->
            <div class="col">
              <label class="form-label">Cliente actual:</label>
              <div class="form-control-plaintext fw-bold nombre-cliente">
                <!-- Aquí se insertará el nombre con JS -->
              </div>
            </div>

            <!-- Flecha -->
            <div class="col-auto">
              <i class="bi bi-arrow-right"></i>
            </div>

            <!-- Nuevo responsable -->
            <div class="col">
              <label class="form-label">Nuevo responsable:</label>
              <select name="personal_admin" class="form-select">
                <?php
                $consulta = "SELECT ID, Nombre FROM usuarios WHERE Area = 'Administración' ORDER BY Nombre";
                $resultado = mysqli_query($conn, $consulta);

                if (mysqli_num_rows($resultado) > 0) {
                  while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo '<option value="' . htmlspecialchars($fila['Nombre']) . '">' . htmlspecialchars($fila['Nombre']) . '</option>';
                  }
                } else {
                  echo '<option disabled>No hay personal disponible</option>';
                }
                ?>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-abrir-modal').forEach(btn => {
      btn.addEventListener('click', () => {

        const modal = document.getElementById('modalAsignar');
        modal.querySelector('input[name="NombreCliente"]').value = btn.dataset.cliente;

        const clienteText = modal.querySelector('.nombre-cliente');
        if (clienteText) {
          clienteText.textContent = btn.dataset.cliente;
        }

        document.getElementById('modalIdSalida').value = btn.dataset.id;
        document.getElementById('modalNombre').value = btn.dataset.name;
        document.getElementById('modalNombreCliente').textContent = btn.dataset.cliente;
      });
    });
  </script>



  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Bundle JS (incluye Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>