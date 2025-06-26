<?php
include "../Back/config/config.php";
session_start();

if (isset($_SESSION['alerta_estado'])) {
  echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
  unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}

$isGerente = ($_SESSION['User_Id'] == 34 || $_SESSION['User_Id'] == 1 ||  $_SESSION['User_Id'] == 29  || $_SESSION['User_Id'] == 7);

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();

// Pagination logic
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get total records count
$total_records_query = "SELECT COUNT(*) as total FROM salidas";
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_assoc($total_records_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch records for current page
$query_salida = "SELECT 
    s.*, 
    e.Id_Orden_Venta, 
    e.Id_Entrega
FROM salidas s
LEFT JOIN (
    SELECT *
    FROM entregas e1
    WHERE e1.Id IN (
        SELECT MAX(e2.Id)
        FROM entregas e2
        GROUP BY e2.Id_Salida
    )
) e ON s.Id = e.Id_Salida
WHERE s.Id_Status != 30
GROUP BY s.Id
ORDER BY s.Id DESC
LIMIT $offset, $records_per_page";

$result_salida = mysqli_query($conn, $query_salida);
?>

<script>
  const currentUserDept = "<?php echo $_SESSION['Departamento']; ?>";
  const currentUserId = "<?php echo $_SESSION['User_Id']; ?>";
  console.log("User Department:", currentUserDept);
  console.log("User ID:", currentUserId);
</script>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    #listaClientes.show {
      display: block !important;
      max-height: 200px;
      overflow-y: auto;
      z-index: 1000;
    }


    #listaClientes {
      position: absolute;
      z-index: 1000;
      width: 100%;
      /* Se adapta al input */
      max-height: 200px;
      /* Evita que sea muy largo */
      overflow-y: auto;
      /* Agrega scroll si hay muchas opciones */
    }

    #listaClientes .dropdown-item {
      white-space: nowrap;
      /* Evita que el texto se divida en varias líneas */
      overflow: hidden;
      text-overflow: ellipsis;
      /* Si es muy largo, pone "..." */
      max-width: 100%;
      /* No se pasa del input */
    }

    .alerta {
      position: fixed;
      top: 10px;
      left: 50%;
      transform: translateX(-50%);
      background: #f0f9ff;
      color: #0c5460;
      border: 1px solid #bee5eb;
      padding: 15px 20px;
      border-radius: 10px;
      font-weight: 500;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      z-index: 9999;
      opacity: 0.95;
      transition: opacity 0.5s ease-in-out;
    }
  </style>
</head>

<body>
  <?php include "../Front/navbar.php"; ?>

  <!--- Mensaje despues de hacer el cambio de urgencia -->
  <?php if (isset($_SESSION['urgencia_msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $_SESSION['urgencia_msg']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['urgencia_msg']); ?>
  <?php endif; ?>

  <?php
  if ($_SESSION['Departamento'] == 'Chofer') {
  ?>
    <div class="container mt-5 text-center">
      <div class="card shadow-lg border-0">
        <div class="card-header">
          <h2>Listado de Pedidos en Ruta</h2>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <?php
            $_SESSION['Name'];

            $query_salida = "SELECT 
    p.Id, 
    p.Id_Salida, 
    p.Cliente, 
    p.Tipo_Doc, 
    p.Paqueteria,
    s.Estado 
FROM preguia p
INNER JOIN salidas s ON p.Id_Salida = s.Id
WHERE s.Estado = 'A Ruta' 
  AND p.Chofer = '{$_SESSION['Name']}'
  AND p.Id = (
      SELECT MAX(p2.Id)
      FROM preguia p2
      WHERE p2.Id_Salida = p.Id_Salida
  )
ORDER BY p.Id DESC ";

            $result_salida = mysqli_query($conn, $query_salida);

            if (mysqli_num_rows($result_salida) > 0) {
            ?>
              <table class="table table-striped table-hover text-center">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Paquetería</th>
                    <th colspan="2">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result_salida)) { ?>
                    <tr>
                      <td><?php echo $row['Id_Salida']; ?></td>
                      <td><?php echo $row['Cliente']; ?></td>
                      <td><?php echo $row['Tipo_Doc']; ?></td>
                      <td><?php echo $row['Paqueteria']; ?></td>
                      <td>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#etiquetaModal" data-id="<?php echo $row['Id_Salida']; ?>" data-cliente="<?php echo $row['Cliente']; ?>">
                          <i class="bi bi-truck"></i> Completar Entrega
                        </button>
                        <!-- Boton para ir a los detales -->
                        <a class='btn btn-warning' href='Front/detalles.php?id=<?= $row['Id_Salida'] ?>'>
                          <i class="bi bi-file-earmark-medical"></i> Detalles
                        </a>

                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php
            } else {
              echo "<p class='alert alert-warning text-center mt-3'>🚚 En este momento no tienes entregas registradas.</p>";
            }
            ?>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal de entrega de pedido -->
    <div class="modal fade" id="etiquetaModal" tabindex="-1" aria-labelledby="etiquetaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="etiquetaModalLabel">Etiqueta de Salida</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- FORMULARIO EN EL LUGAR CORRECTO -->
          <form action="Back/Entregas/entregaChofer.php" method="POST" id="form_agregar_empaque" enctype="multipart/form-data">
            <div class="modal-body">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="FolioSalida" class="form-label">Folio:</label>
                  <input type="text" class="form-control" id="FolioSalida" name="Folio" readonly>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="Cliente" class="form-label">Cliente:</label>
                  <input type="text" class="form-control" id="Cliente" name="Cliente" readonly>
                </div>
              </div>
              <hr>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="Entrega" class="form-label">Estado de la Entrega:</label>
                  <select class="form-select" name="EstadoEntrega" id="Entrega" style="cursor: pointer;">
                    <option value="Entrega A Cliente">Entrega A Cliente</option>
                    <option value="Entrega A Transporte">Entrega A Transporte</option>
                    <option value="No Entregado">No Entregado</option>
                  </select>
                </div>
                <div class="col-md-12 mb-3">
                  <label for="Comentario" class="form-label">Comentario:</label>
                  <input type="text" class="form-control" id="Comentario" name="Comentario" placeholder="Agrega un comentario...">
                </div>
                <div class="col-md-12 mb-3">
                  <label for="fotoEntrega" class="form-label">Foto de evidencia:</label>
                  <input
                    type="file"
                    id="fotoEntrega"
                    name="fotoEntrega"
                    accept="image/*"
                    capture="environment"
                    class="form-control">
                </div>
              </div>
              <img id="previewImg" class="img-fluid my-2 d-none">
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Guardar</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.getElementById('fotoEntrega').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const imgPrev = document.getElementById('previewImg');
        imgPrev.src = URL.createObjectURL(file);
        imgPrev.classList.remove('d-none');
      });
      document.addEventListener("DOMContentLoaded", function() {
        // Capturar el modal cuando se abre
        var etiquetaModal = document.getElementById("etiquetaModal");

        etiquetaModal.addEventListener("show.bs.modal", function(event) {
          // Obtener el botón que activó el modal
          var button = event.relatedTarget;

          // Extraer los valores de los atributos data-
          var idFolio = button.getAttribute("data-id");
          var cliente = button.getAttribute("data-cliente");

          // Asignar los valores a los inputs dentro del modal
          document.getElementById("FolioSalida").value = idFolio;
          document.getElementById("Cliente").value = cliente;
        });
      });
    </script>
  <?php
    exit();
  }

  if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            {$_SESSION['success_message']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['success_message']);
  }

  if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            {$_SESSION['error_message']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['error_message']);
  }
  ?>

  <!-- Listado General -->
  <div class="container mt-5 text-center">
    <div class="card shadow-lg border-0">
      <div class="card-header">
        <div class="row text-center">
          <div class="col-md-12">
            <h2>Listado General</h2>
            <?php
            //print_r($_SESSION);
            //echo "<h5>Bienvenido " . $_SESSION['User_Id'] . "</h5>";
            // Permitil que el usuario 'Raquel Cabrales' con User_Id = 34 PUEDA acceder a los botones de todos los procesosl sin importar que no tenga el mismo 'Departamento'


            if ($_SESSION['Departamento'] == 'Entrega y Surtido' || $_SESSION['User_Id'] == 34) {
              // Mostrar botón para agregar nueva etiqueta de salida
            ?>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarModal">
                <i class="bi bi-plus-lg"></i> Agregar Nueva Etiqueta de Salida
              </button>
            <?php

            }

            ?>
          </div>

          <!-- Filtros para los querys -->
          <div class="col-md-12">
            <div class="row">
              <div class="col-md-4">
                <label for="buscar_salida" class="form-label">Número de Salida:</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="buscar_salida" placeholder="Presiona Enter o la lupa...">
                  <button class="btn btn-outline-primary" type="button" id="btn_buscar_salida">
                    <i class="bi bi-search"></i>
                  </button>
                </div>
              </div>

              <div class="col-md-4">
                <label for="buscar_cliente" class="form-label">Cliente:</label>
                <select class="form-select" id="buscar_cliente">
                  <option value="">Todos</option>
                  <?php
                  $q = mysqli_query($conn, "SELECT DISTINCT Nombre_Cliente FROM salidas ORDER BY Nombre_Cliente");
                  while ($r = mysqli_fetch_assoc($q)) {
                    echo '<option value="' . htmlspecialchars($r['Nombre_Cliente']) . '">' .
                      htmlspecialchars($r['Nombre_Cliente']) .
                      '</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-4">
                <label for="buscar_factura" class="form-label">Folio de Factura:</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="buscar_factura" placeholder="Buscar...">
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <label for="buscar_entrega" class="form-label">Folio Entrega:</label>
                <input type="text" class="form-control" id="buscar_entrega" placeholder="Buscar...">
              </div>
              <div class="col-md-4">
                <label for="buscar_orden" class="form-label">Orden de Venta:</label>
                <input type="text" class="form-control" id="buscar_orden" placeholder="Buscar...">
              </div>
              <div class="col-md-4">
                <label for="buscar_estado" class="form-label">Buscar por Estado:</label>
                <select class="form-select" id="buscar_estado">
                  <option value="">Todos</option>
                  <option value="Entrega">Entrega</option>
                  <option value="Empaque">Empaque</option>
                  <option value="Facturación">Facturación</option>
                  <option value="Logistica">Logística</option>
                  <option value="A Ruta">A Ruta</option>
                  <option value="Envios">Envíos</option>
                  <option value="Completado">Completado</option>
                </select>
              </div>
            </div>
            <br>
          </div>
        </div>
        <br>
      </div>
      <hr>

      <div class="card-body">
        <!-- Results Table -->
        <div class="table-responsive">
          <table class="table table-striped table-hover text-center">
            <thead class="table-dark">
              <tr>
                <th>Urgencia</th>
                <th>ID</th>
                <th>Nombre Cliente</th>
                <th>Orden De Venta</th>
                <th>Folio Entrega</th>
                <th>Estado</th>
                <th>Sucursal</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla_resultados">
              <?php while ($fila = mysqli_fetch_assoc($result_salida)):
                $Id_Orden_Venta = $fila['Id_Orden_Venta'] ?? 'N/A';

                $esUrgente = ($fila['Urgencia'] === 'Urgente');
                $textoBoton = $esUrgente ? 'Quitar Urgencia' : 'Marcar como Urgente';
                $colorBoton = $esUrgente ? 'btn-danger' : 'btn-outline-primary';
                $nuevaUrgencia = $esUrgente ? 'Nada' : 'Urgente';
              ?>

                <tr class="<?= $fila['Urgencia'] == 'Urgente' ? 'table-danger' : 'table-success' ?>">
                  <td><a href="Back/Etiquetas/changeUrgencia.php?id=<?= $fila['Id'] ?>&urgencia=<?= $nuevaUrgencia ?>"
                      class="btn <?= $colorBoton ?>">
                      <?= $textoBoton ?>
                    </a></td>
                  <td><?= htmlspecialchars($fila['Id']) ?></td>
                  <td><?= htmlspecialchars($fila['Nombre_Cliente']) ?></td>
                  <td><?php echo  $Id_Orden_Venta  ?></td> <!-- Manejo de valores nulos -->
                  <td><?php echo $fila['Id_Entrega']; ?></td>
                  <td><?= htmlspecialchars($fila['Estado']) ?></td>
                  <td><?= htmlspecialchars($fila['Sucursal']) ?></td>

                  <td>
                    <a class='btn btn-primary' href='Front/detalles.php?id=<?= $fila['Id'] ?>'>
                      <i class="bi bi-file-earmark-medical"></i> Detalles
                    </a>
                    <?php if ($isGerente): ?>
                      <button type="button"
                        class="btn btn-danger btn-sm btn-eliminar-folio"
                        data-id="<?= $fila['Id'] ?>"
                        data-cliente="<?= htmlspecialchars($fila['Nombre_Cliente']) ?>">
                        <i class="bi bi-trash3"></i> Eliminar Folio
                      </button>
                    <?php endif; ?>
                    <?php
                    // Empaque puede recibir cuando el estado es 'Entrega'
                    if (($isGerente || $_SESSION['Departamento'] == 'Empaque') && $fila['Estado'] == 'Entrega') {
                      echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Empaque' class='btn btn-warning btn-sm'>
                        <i class='bi bi-box-seam me-sm-2'></i> 
                        <span class='d-none d-sm-inline'>Recibir Entrega (<strong>Empaque</strong>)</span>
                      </a>";
                    }

                    /// Para recibir de estado Empaque a Facturación:
                    /// Permitir a Raquel Cabrales recibir de Empaque a Facturación, pero que se muestre solo cuando el estado esta en Empaque, pero no en otros estados
                    /// Para recibir de estado Empaque a Facturación:

                    if (($isGerente || $_SESSION['Departamento'] == 'Facturación') && $fila['Estado'] == 'Empaque') {

                      // Consultar si hay imágenes asociadas al folio
                      $id_salida = $fila['Id'];
                      $query_img = "SELECT COUNT(*) AS total FROM imagen WHERE id_salida = $id_salida";
                      $result_img = mysqli_query($conn, $query_img);
                      $row_img = mysqli_fetch_assoc($result_img);

                      // Si hay imágenes, mostrar el botón de cambio de estado
                      if ($row_img['total'] > 0) {
                        echo "<a href='Back/changeState.php?id=" . $id_salida . "&estado=Facturación' class='btn btn-warning'>
                <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Facturación)
              </a>";
                      } else {
                        // Mostrar aviso de que no hay evidencias
                        echo "<small class='text-danger'><i class='bi bi-exclamation-circle'></i> Sin Evidencias</small>";
                      }
                    }


                    if (($isGerente || $_SESSION['Departamento'] == 'Logistica') && $fila['Estado'] == 'Facturación') {
                      /// Para recibir de estado Facturación a Logistica:
                      //1.- Verificar que se encuentre registrado una factura 
                      /// Consultar la tabla entregas donde Id_Salida = $fila['Id'] y el campo Archivo o N/A o 0 no sea nulo
                      $query_factura = "SELECT * FROM entregas WHERE Id_Salida = {$fila['Id']} AND Archivo != '0'";
                      $result_factura = mysqli_query($conn, $query_factura);
                      $factura_registrada = mysqli_num_rows($result_factura) > 0;
                      if ($factura_registrada) {
                        echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Logistica' class='btn btn-warning'>
                      <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Logistica)
                    </a>";
                      } else {

                        echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Logistica' class='btn btn-warning disabled'>
                      <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Falta Factura)
                    </a>";
                      }
                    }

                    if (($isGerente || $_SESSION['Departamento'] == 'Logistica') && $fila['Estado'] == 'Logistica') {
                      /// Botón para abrir modal de la Pre-guia 
                      /// Mandar $fila['Id'] y $fila['Nombre_Cliente']
                      echo "<button class='btn btn-info me-2' 
                            data-bs-toggle='modal' 
                            data-bs-target='#preGuiaModal'
                            data-pedido-id='{$fila['Id']}'
                            data-cliente-nombre='{$fila['Nombre_Cliente']}'>
                          <i class='bi bi-truck me-1'></i> Pre-Guía
                        </button>";
                    }

                    if (($isGerente || $_SESSION['Departamento'] == 'Logistica') && $fila['Estado'] == 'A Ruta') {
                      echo "<button class='btn btn-danger me-2' data-bs-toggle='modal' 
                            data-bs-target='#RutaModal'
                            data-pedido-id='{$fila['Id']}'
                            data-cliente-nombre='{$fila['Nombre_Cliente']}'>
                          <i class='bi bi-arrow-clockwise'></i></i> Reasignación
                        </button>";
                    }
                    ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- ModalRuta -->
        <div class="modal fade" id="RutaModal" tabindex="-1" aria-labelledby="RutaModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="Back/reasignar_chofer.php">
              <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                  <h5 class="modal-title" id="RutaModalLabel">Reasignar</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                  <input type="hidden" name="Id_Salida" id="modalSalidaId">
                  <div class="row">
                    <!-- DEBUG: mostrar ID recibido -->
                    <div class="col-md-6">
                      <label for="mostrarId" class="form-label">ID de la salida:</label>
                      <input type="text" class="form-control" id="mostrarId" disabled>
                    </div>

                    <div class="col-md-6">
                      <label for="tipoFlete" class="form-label">Tipo de Flete</label>
                      <div class="form-floating mb-3">
                        <!-- Opciones del Selector: A Domicilio, Ocurre -->
                        <select class="form-select" name="Tipo_Flete" id="Tipo_Flete" required>
                          <option value=""> Selecciona una opción </option>
                          <option value="A Domicilio">A Domicilio</option>
                          <option value="Ocurre">Ocurre</option>
                          <option value="Ruta">Ruta</option>
                        </select>
                        <label for="Tipo_Flete">Tipo de Flete</label>
                      </div>
                    </div>

                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label for="choferActual" class="form-label">Chofer actual:</label>
                      <input type="text" class="form-control" id="choferActual" disabled>
                    </div>

                    <div class="col-md-6">
                      <label for="nuevoChofer" class="form-label">Nuevo chofer:</label>
                      <select class="form-select" name="nuevo_chofer" id="nuevoChofer" required>
                        <option value="">Selecciona un chofer</option>
                      </select>
                    </div>
                  </div>

                  <br>

                  <!-- Agrupar Paquetería y Método de Pago -->
                  <div id="opcionesEnvio">
                    <div class="row">
                      <div class="col-md-6">
                        <label for="NuevaPaqueteria" class="form-label">Paqueteria:</label>
                        <div class="form-floating">
                          <select class="form-control" name="Paqueteria" id="Paqueteria">
                            <option value="">Selecciona una Paqueteria</option>
                            <?php
                            $query_paqueteria = mysqli_query($conn, "SELECT * FROM paqueteria ORDER BY nombre");
                            while ($rw = mysqli_fetch_array($query_paqueteria)) {
                              echo "<option value='{$rw['nombre']}'>{$rw['nombre']}</option>";
                            }
                            ?>
                            <option value="Otro">Otro</option>
                          </select>
                          <label for="Paqueteria">Paqueteria</label>
                          <br>
                          <!-- Input oculto -->
                          <div class="form-floating mb-3" id="otroPaqueteriaDiv" style="display: none;">
                            <input type="text" class="form-control" id="otroPaqueteria" name="otroPaqueteria"
                              placeholder="Ingrese otra paquetería">
                            <label for="otroPaqueteria">Especificar otra paquetería</label>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <label for="Metodo" class="form-label">Método de Pago</label>
                        <div class="form-floating mb-3">
                          <select class="form-select" name="Metodo_Pago" id="Metodo_Pago" required>
                            <option value="">Selecciona una opción</option>
                            <option value="Cob. Reg.">Cob. Reg.</option>
                            <option value="Credito">Credito</option>
                            <option value="Pagado">Pagado</option>
                            <option value="X Cobrar">X Cobrar</option>
                          </select>
                          <label for="Metodo_Pago">Metodo De Pago:</label>
                        </div>
                      </div>
                    </div>
                  </div>


                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">Guardar</button>
                </div>
              </div>
            </form>
          </div>
        </div>


        <!-- Pagination Controls -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center flex-wrap">

            <!-- Previous -->
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= max(1, $current_page - 1) ?>" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>

            <?php
            // Mostrar siempre la primera página
            if ($current_page > 3) {
              echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }

            // Páginas antes y después del actual
            for ($page = max(1, $current_page - 2); $page <= min($total_pages, $current_page + 2); $page++) {
              $active = $page == $current_page ? 'active' : '';
              echo "<li class='page-item $active'><a class='page-link' href='?page=$page'>$page</a></li>";
            }

            // Mostrar última página
            if ($current_page < $total_pages - 2) {
              echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
              echo "<li class='page-item'><a class='page-link' href='?page=$total_pages'>$total_pages</a></li>";
            }
            ?>

            <!-- Next -->
            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) ?>" aria-label="Siguiente">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>

          </ul>
        </nav>


      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="preGuiaModal" tabindex="-1" aria-labelledby="preGuiaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="preGuiaModalLabel">Pre-Guía de Envío</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="Back/Preguia/process_pre_guia.php" method="POST" id="form_logistica">
            <div class="row">
              <div class="col-md-6">
                <label for="Id" class="form-label">Numero Id:</label>
                <input type="number" class="form-control" id="modalPedidoId" name="pedido_id">
              </div>
              <div class="col-md-6">
                <label for="clienteNombre" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="clienteNombre" name="clienteNombre" readonly disabled="false">
                <input type="hidden" id="clienteNombreHidden" name="clienteNombre">
              </div>
              <div id="camposExtraCliente" style="display: none;" class="mt-3">
                <div class="row">
                  <div class="col-md-6">
                    <label for="inputCalle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="inputCalle" name="Calle">
                  </div>
                  <div class="col-md-6">
                    <label for="inputColonia" class="form-label">Colonia</label>
                    <input type="text" class="form-control" id="inputColonia" name="Colonia">
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-4">
                    <label for="inputCiudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="inputCiudad" name="Ciudad">
                  </div>
                  <div class="col-md-4">
                    <label for="inputEstado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="inputEstado" name="Estado">
                  </div>
                  <div class="col-md-4">
                    <label for="inputCP" class="form-label">CP</label>
                    <input type="text" class="form-control" id="inputCP" name="CP">
                  </div>
                </div>
              </div>

            </div>
            <br>
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
            <div id="extraFields"></div>
            <button type="submit" class="btn btn-primary">Guardar Pre-Guía</button>

          </form>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal para agregar nueva etiqueta de salida -->
  <div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title " id="agregarModalLabel">Agregar Nueva Etiqueta de Salida</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <!-- Formulario de agregar -->
          <form action="Back/Etiquetas/addEtiqueta.php" method="POST" id="form_agregar">
            <div class="row">
              <!-- No. Folio -->
              <div class="col-md-12">
                <div class="form-floating mb-3">
                  <?php
                  // Obtener el último Id de la tabla salida_refactor
                  $query = "SELECT MAX(Id) AS Id FROM salidas";
                  $result = mysqli_query($conn, $query);
                  $row = mysqli_fetch_array($result);
                  $id = $row['Id'] + 1;
                  ?>
                  <input type="text" class="form-control" id="numero_salida" name="numero_salida"
                    value="<?php echo $id; ?>" readonly>
                  <label for="numero_salida">No. Folio</label>
                </div>
              </div>

              <!-- Primer Cliente -->
              <!-- Input con Autocompletado -->
              <div class="col-md-12 position-relative dropdown">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control dropdown-toggle" id="cliente_nombre" placeholder="Escribe el nombre del cliente..." autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false">
                  <input type="hidden" id="id_cliente" name="id_cliente">
                  <label for="cliente_nombre">Digite un caracter para buscar un cliente</label>
                  <div id="listaClientes" class="dropdown-menu w-100"></div>
                </div>
              </div>

              <!-- Campos para nuevo cliente (inicialmente ocultos) -->
              <div id="contenedor_nuevos" style="display:none;">
                <div class="col-md-12">
                  <label for="nuevo_nombre">Nombre del Cliente</label>
                  <input type="text" class="form-control" id="nuevo_nombre" name="nuevo_nombre">
                </div>
                <div class="col-md-12">
                  <label for="nuevo_clave">Clave SAP</label>
                  <input type="text" class="form-control" id="nuevo_clave" name="nuevo_clave">
                </div>
                <div class="col-md-12">
                  <label for="nuevo_rfc">RFC</label>
                  <input type="text" class="form-control" id="nuevo_rfc" name="nuevo_rfc">
                </div>
              </div>

              <!-- Header de la Sección de Partidas -->
              <div class="modal-header">
                <h5 class="modal-title" id="agregarModalLabel">Agregar Nueva Partida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <hr>
              <div class="row">
                <!-- Orden De Venta -->
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="orden_venta" name="orden_venta" required>
                    <label for="orden_venta">Orden de Venta</label>
                  </div>
                </div>

                <!-- Folio de Entrega -->
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="folio_entrega" name="folio_entrega"
                      required>
                    <label for="folio_entrega">Folio de Entrega</label>
                  </div>
                </div>
              </div>

              <!-- Partidas -->
              <div class="row">
                <div class="col-md-6">
                  <label for="partida" class="form-label">Partida</label>
                  <div class="d-flex align-items-center">
                    <input type="number" class="form-control me-2" id="partida1" name="partida1" min="1"
                      max="99" step="1" style="width: 80px;" value="1" readonly>
                    <span>-</span>
                    <input type="number" class="form-control ms-2" id="partida2" name="partida2" min="1"
                      max="99" step="1" style="width: 80px;" required>
                  </div>
                </div>

                <!-- Prioridad -->
                <div class="col-md-6">
                  <label for="Comentarios" class="form-label">Prioridad:</label>
                  <div class="form mb-3">
                    <select class="form-select" name="prioridad" id="prioridad" required>
                      <option value="Nada">Sin Prioridad</option>
                      <option value="Urgente">Urgente</option>
                    </select>
                  </div>
                </div>

              </div>
              <div class="row">
                <div class="col-md-12">
                  <label for="Comentarios" class="form-label">Comentarios:</label>
                  <input type="text" class="form-control" name="Comentarios" id="Comentarios">
                </div>
              </div>
            </div>
            <br>

            <!-- Footer del Modal -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <br><br><br>
  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Si usas Bootstrap 5, agrega el theme opcional -->
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script para hidden las opciones cuando solo es a ruta -->
  <script>
    $(document).ready(function() {
      $('#buscar_cliente').select2({
        theme: 'bootstrap4', // o 'bootstrap-5' si usas ese theme
        width: '100%',
        placeholder: 'Todos',
        allowClear: true // muestra una “x” para limpiar
      });
    });


    document.addEventListener('DOMContentLoaded', function() {
      const tipoFlete = document.getElementById('Tipo_Flete');
      const opcionesEnvio = document.getElementById('opcionesEnvio');

      tipoFlete.addEventListener('change', function() {
        const metodoPago = document.getElementById('Metodo_Pago');
        const paqueteria = document.getElementById('Paqueteria');

        if (this.value === 'Ruta') {
          opcionesEnvio.style.display = 'none';

          // Quitar required
          metodoPago.required = false;
          paqueteria.required = false;

          // Asignar valores default opcionalmente
          metodoPago.value = '';
          paqueteria.value = '';
        } else {
          opcionesEnvio.style.display = 'block';

          // Reestablecer required
          metodoPago.required = true;
          paqueteria.required = true;
        }
      });

    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var rutaModal = document.getElementById('RutaModal');

      rutaModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var salidaId = button.getAttribute('data-pedido-id');

        document.getElementById('modalSalidaId').value = salidaId;
        document.getElementById('mostrarId').value = salidaId;

        // Limpiar antes de cargar
        document.getElementById('choferActual').value = '';
        const selectChoferes = document.getElementById('nuevoChofer');
        selectChoferes.innerHTML = '<option value="">Cargando...</option>';

        // Fetch al backend
        fetch('Back/obtener_choferes.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'Id_Salida=' + encodeURIComponent(salidaId)
          })
          .then(res => res.json())
          .then(data => {
            console.log("Respuesta del servidor:", data);

            // Mostrar chofer actual
            document.getElementById('choferActual').value = data.chofer_actual;

            // Llenar select con choferes
            selectChoferes.innerHTML = '<option value="">Selecciona un chofer</option>';
            data.choferes.forEach(chofer => {
              const option = document.createElement('option');
              option.value = chofer.Id;
              option.textContent = chofer.Nombre;
              selectChoferes.appendChild(option);
            });
          })
          .catch(err => {
            console.error("Error al obtener los choferes:", err);
          });
      });
    });

    /// Animación de salida para el mensaje:
    window.addEventListener('DOMContentLoaded', () => {
      const alerta = document.getElementById("alertaBanner");
      if (alerta) {
        setTimeout(() => {
          alerta.style.opacity = '0';
          setTimeout(() => {
            alerta.remove();
          }, 500);
        }, 4000); // Desaparece después de 4 segundos
      }
    });


    // Filtros para la tabla:
    $(document).ready(function() {
      function buscarSalidas() {
        let numero_salida = $("#buscar_salida").val();
        let cliente = $("#buscar_cliente").val();
        let orden_venta = $("#buscar_orden").val();
        let estado = $("#buscar_estado").val();
        let Id_Entrega = $("#buscar_entrega").val();
        let Id_Factura = $("#buscar_factura").val();
        //console.log("Cliente seleccionad", cliente);
        //console.log("Entrega seleccionada", Id_Entrega);
        console.log("Salida seleccionada", numero_salida);

        $.ajax({
          url: "Back/buscar_salidas.php",
          type: "POST",
          data: {
            numero_salida: numero_salida,
            cliente: cliente,
            orden_venta: orden_venta,
            id_entrega: Id_Entrega,
            id_factura: Id_Factura,
            estado: estado
          },
          dataType: "json",

          success: function(response) {

            let tbody = $("#tabla_resultados");
            tbody.empty(); // Clear table before inserting new data

            if (response.length > 0) {
              response.forEach(function(item) {
                console.log(item);
                // Determine button HTML based on conditions (similar to your PHP logic)
                let buttonsHtml = `
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href='Front/detalles.php?id=${item.Id}' class='btn btn-primary btn-sm'>
                        <i class="bi bi-file-earmark-medical"></i> <span class="d-none d-sm-inline">Detalles</span>
                    </a>`;

                // Entrega button
                if (item.Estado == 'Entrega') {
                  if (currentUserDept == 'Empaque' || currentUserId == 34 || currentUserId == 1) {
                    buttonsHtml += `
                        <a href='Back/changeState.php?id=${item.Id}&estado=Empaque' class='btn btn-warning btn-sm'>
                            <i class='bi bi-box-seam me-sm-2'></i> 
                            <span class='d-none d-sm-inline'>Recibir Entrega (<strong>Empaque</strong>)</span>
                        </a>`;
                  }
                }

                // Empaque button logic - js
                if (item.Estado == 'Empaque') {
                  if (currentUserDept == 'Facturación' || currentUserId == 34 || currentUserId == 1) {
                    if (item.Imagenes_Registradas > 0) {
                      buttonsHtml += `
        <a href='Back/changeState.php?id=${item.Id}&estado=Facturación' class='btn btn-warning'>
          <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Facturación)
        </a>`;
                    } else {
                      buttonsHtml += `<small class='text-danger d-block mt-2'>
        <i class='bi bi-camera-off'></i> Sin Evidencias
      </small>`;
                    }
                  }
                }


                // Pasar a Logistica cuando tiene archivo asignado 
                if (item.Estado === 'Facturación' &&
                  (currentUserDept === 'Logistica' || currentUserId === 34) &&
                  parseInt(item.Factura_Registrada) > 0) {

                  buttonsHtml += `
                    <a href='Back/changeState.php?id=${item.Id}&estado=Logistica' class='btn btn-warning'>
                      <i class='bi bi-truck'></i> Recibir Entrega (Logística)
                    </a>`;
                }

                // Pre-Guía button
                if (item.Estado === 'Logistica' &&
                  (currentUserDept === 'Logistica' || currentUserId === 34)) {
                  buttonsHtml += `
                      <button class='btn btn-info me-2' 
                              data-bs-toggle='modal' 
                              data-bs-target='#preGuiaModal'
                              data-pedido-id='${item.Id}'
                              data-cliente-nombre='${item.Nombre_Cliente}'>
                          <i class='bi bi-truck me-1'></i> Pre-Guía
                      </button>`;
                }

                // Import IsGerente list, from PHp flow
                const isGerente = <?= json_encode($isGerente); ?>;

                if (isGerente) {
                  buttonsHtml += `
                <button type="button"
                  class="btn btn-danger btn-sm btn-eliminar-folio"
                  data-id="${item.Id}"
                  data-cliente="${item.Nombre_Cliente}">
                  <i class="bi bi-trash3"></i> Eliminar Folio
                </button>`;
                }

                if (item.Estado === 'A Ruta' &&
                  (currentUserDept === 'Logistica' || currentUserId === 34)) {
                  buttonsHtml += `
    <button class='btn btn-danger me-2'
            data-bs-toggle='modal' 
            data-bs-target='#RutaModal'
            data-pedido-id='${item.Id}'
            data-cliente-nombre='${item.Nombre_Cliente}'>
        <i class='bi bi-arrow-clockwise'></i> Reasignación
    </button>
  `;
                }
                // Close buttons div
                buttonsHtml += `</div>`;

                // Add row to table
                tbody.append(`
                <tr class="${item.Urgencia == 'Urgente' ? 'table-danger' : 'table-success'}">
                
                    <td>
                        <a href="Back/Etiquetas/changeUrgencia.php?id=${item.Id}&urgencia=${item.Urgencia == 'Urgente' ? 'Nada' : 'Urgente'}" 
                          class="btn ${item.Urgencia == 'Urgente' ? 'btn-danger' : 'btn-outline-primary'}">
                          ${item.Urgencia == 'Si' ? 'Quitar Urgencia' : 'Marcar Urgencia'}

                          Aqui
                        </a>
                    </td>
                    <td>${item.Id}</td>
                    <td>${item.Nombre_Cliente}</td>
                    <td>${item.Id_Orden_Venta ? item.Id_Orden_Venta : 'N/A'}</td>
                    <td>${item.Id_Entrega}</td>
                    <td>${item.Estado}</td>
                    <td>${item.Sucursal}</td>
                    <td>${buttonsHtml}</td>
                </tr>
            `);
              });
            } else {
              tbody.append('<tr><td colspan="6" class="text-center text-danger py-3">No se encontraron resultados</td></tr>');
            }
          }
        });
      }

      $("#buscar_btn").on("click", function() {
        buscarSalidas();
      });

      $("#buscar_salida").on("keyup", function(e) {
        if (e.key === "Enter") {
          console.log("Enter key pressed");
          buscarSalidas();
        }
      });

      // Ejecutar búsqueda con el botón de lupa
      $("#btn_buscar_salida").on("click", function() {
        buscarSalidas();
      });

      // Para que se actualice al escribir en los inputs sin dar clic en el botón
      $("#buscar_factura, #buscar_entrega, #buscar_cliente, #buscar_orden, #buscar_estado").on("keyup change", function() {
        buscarSalidas();
      });
    });

    $(document).on("click", ".detalles-btn", function(event) {
      event.preventDefault(); // Detiene cualquier otra acción que esté interfiriendo
      let url = $(this).attr("href");
      window.location.assign(url); // Redirige inmediatamente
    });



    /// Codigo para extender el tiempo de la sesion activa :
    setInterval(function() {
      fetch('extender_sesion.php'); // Llama al script cada 5 minutos
    }, 900000); // 300,000 ms = 5 minutos


    document.addEventListener('DOMContentLoaded', function() {
      var preGuiaModal = document.getElementById('preGuiaModal');
      preGuiaModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var pedidoId = button.getAttribute('data-pedido-id');
        var clienteNombre = button.getAttribute('data-cliente-nombre');

        document.getElementById('modalPedidoId').value = pedidoId;
        document.getElementById('clienteNombre').value = clienteNombre;
        document.getElementById('clienteNombreHidden').value = clienteNombre;
      });
    });

    // ----------------------------------------------- Funciones para el modal de Preguia -----------------------------------------------
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById("Tipo_Doc").addEventListener("change", function() {
        let tipo = this.value;
        console.log(tipo);
        const extraFields = document.getElementById("extraFields");

        // Limpiar contenido previo
        extraFields.innerHTML = "";

        let commonFields = `
        <div class="row">
            <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-control" name="Paqueteria" id="Paqueteria">
                                    <option value="">Selecciona una Paqueteria</option>
                                    <?php
                                    $query_paqueteria = mysqli_query($conn, "SELECT * FROM paqueteria ORDER BY nombre");
                                    while ($rw = mysqli_fetch_array($query_paqueteria)) {
                                      echo "<option value='{$rw['nombre']}'>{$rw['nombre']}</option>";
                                    }
                                    ?>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="Paqueteria">Paqueteria</label>
                                <br>

                                <!-- Input oculto -->
                                <div class="form-floating mb-3" id="otroPaqueteriaDiv" style="display: none;">
                                    <input type="text" class="form-control" id="otroPaqueteria" name="otroPaqueteria"
                                        placeholder="Ingrese otra paquetería">
                                    <label for="otroPaqueteria">Especificar otra paquetería</label>
                                </div>

                            </div>
                        </div>

            <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class='form-control' name='Chofer_Asignado' id='Chofer_Asignado' required>
                                    <option value="">Selecciona un chofer</option>
                                    

                                    <option value="Manuel Lopez Romero">Manuel Lopez Romero</option>
                                    <option value="Jose de Jesus Torres Aguilar">Jose de Jesus Torres Aguilar</option>
                                    <option value="Brandon Alexis Hernandez Robles">Brandon Alexis Hernandez Robles</option>
                                    <option value="Daniel Soto Mayor">Daniel Soto Mayor</option>
                                    <option value="Jonathan Islas Hernandez">Jonathan Islas Hernandez</option>
                                    <option value="Rene Canche Couoh">Rene Canche Couoh</option>
                                    <option value="Leonardo Daniel Urzua Pulido">Leonardo Daniel Urzua Pulido</option>
                                    <option value="">---------------------------</option>
                                    <option value="Cliente Pasa">Cliente Pasa</option>
                                    <option value="Entregado por Vendedor">Entregado por Vendedor</option>
                                    <option value="Proveedor Recolecta">Proveedor Recolecta</option>
                                </select>
                                </select>
                                <label for="Chofer">Chofer:</label>
                            </div>
                        </div>
        </div>
        <div class="row">
                <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <!-- Opciones del Selector: A Domicilio, Ocurre -->
                                <select class="form-select" name="Tipo_Flete" id="Tipo_Flete" required>
                                    <option value=""> Selecciona una opción </option>
                                    <option value="A Domicilio">A Domicilio</option>
                                    <option value="Ocurre">Ocurre</option>
                                </select>
                                <label for="Tipo_Flete">Tipo de Flete</label>
                            </div>
                        </div>
            <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <!-- Opciones del Selector: Cob. Reg., Credito, Pagado, X Cobrar -->
                                <select class="form-select" name="Metodo_Pago" id="Metodo_Pago" required>
                                    <option value="">Selecciona una opción</option>
                                    <option value="Cob. Reg.">Cob. Reg.</option>
                                    <option value="Credito">Credito</option>
                                    <option value="Pagado">Pagado</option>
                                    <option value="X Cobrar">X Cobrar</option>
                                </select>
                                <label for="Metodo_Pago">Metodo De Pago:</label>
                            </div>
                        </div>

        </div>
    `;

        let clienteIntermedioField = `
        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <select class='form-control' name='cliente_intermedio' id='cliente_intermedio'>
                                    <option value="">Selecciona un Cliente</option>
                                    <?php
                                    $query_cliente = mysqli_query($conn, "select * from clientes order by nombre");
                                    while ($rw = mysqli_fetch_array($query_cliente)) {
                                    ?>
                                        <option value="<?php echo $rw['Nombre']; ?>">
                                            <?php echo $rw['Nombre'] . "(" . $rw['Clave_Sap'] . ")"; ?>
                                        </option>
                                        <?php
                                      }
                                        ?>
                                </select>
                                <label for="cliente_intermedio">Cliente Intermedio:</label>
                            </div>
                        </div>

    `;

        let choferField = `
        <div class="col-md-12">
                            <div class="form-floating mb-3">
                                <select class='form-control' name='Chofer_Asignado' id='Chofer_Asignado' required>
                                    <option value="">Selecciona un chofer</option>
                                    <option value="Manuel Lopez Romero">Manuel Lopez Romero</option>
                                    <option value="Jose de Jesus Torres Aguilar">Jose de Jesus Torres Aguilar</option>
                                    <option value="Brandon Alexis Hernandez Robles">Brandon Alexis Hernandez Robles</option>
                                    <option value="Daniel Soto Mayor">Daniel Soto Mayor</option>
                                    <option value="Jonathan Islas Hernandez">Jonathan Islas Hernandez</option>
                                    <option value="Rene Canche Couoh">Rene Canche Couoh</option>
                                    <option value="Leonardo Daniel Urzua Pulido">Leonardo Daniel Urzua Pulido</option>
                                    <option value="">---------------------------</option>
                                    <option value="Cliente Pasa">Cliente Pasa</option>
                                    <option value="Entregado por Vendedor">Entregado por Vendedor</option>
                                    <option value="Proveedor Recolecta">Proveedor Recolecta</option>
                                </select>
                                <label for="Chofer">Chofer:</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                        <label for="FechaEntregado">Fecha Entregado:</label>
                        <input type="date" class="form-control" name="fecha_entregado" id='fecha_entregado' required>
                        </div>
                        <br>
    `;

        // Agregar los campos según la selección
        if (tipo == "Directo" || tipo == "Reembarque") {
          extraFields.innerHTML += commonFields;
        } else if (tipo === "Reembarque") {
          extraFields.innerHTML += `<div class="row">${clienteIntermedioField}</div>`;
        } else if (tipo === "Ruta") {
          extraFields.innerHTML += choferField;
        }

        if (!extraFields) {
          console.error("Element with ID 'extraFields' not found!");
          return;
        }
      });
    });

    // Re
    document.addEventListener('change', function(event) {
      if (event.target.id === 'Paqueteria') {
        const otroDiv = document.getElementById('otroPaqueteriaDiv');
        otroDiv.style.display = (event.target.value === 'Otro') ? 'block' : 'none';
      }
    });

    // JavaScript para sincronizar los selects 
    document.getElementById('id_cliente').addEventListener('change', function() {
      let selectedValue = this.value;
      document.getElementById('Cliente2').value = selectedValue; // Sincroniza el select
      document.getElementById('Cliente2_hidden').value = selectedValue; // Envía el valor al backend
    });

    // Agregar Dinamismo al formulario del registro de salidas en el campo de cliente
    document.addEventListener("DOMContentLoaded", function() {
      const inputCliente = document.getElementById("cliente_nombre");
      const idCliente = document.getElementById("id_cliente");
      const listaClientes = document.getElementById("listaClientes");

      const nombreNuevo = document.getElementById("nuevo_nombre");
      const claveNuevo = document.getElementById("nuevo_clave");
      const rfcNuevo = document.getElementById("nuevo_rfc");
      const contenedorNuevos = document.getElementById("contenedor_nuevos");

      inputCliente.addEventListener("input", function() {
        const query = this.value;
        if (query.length === 0) {
          listaClientes.classList.remove("show");
          return;
        }

        fetch("Back/Clientes/buscar_clientes.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "query=" + encodeURIComponent(query)
          })
          .then(response => response.json())
          .then(data => {
            console.log("Respuesta del servidor:", data); // <-- DEBUG
            listaClientes.innerHTML = "";

            if (data.length > 0) {
              data.forEach(cliente => {
                const item = document.createElement("button");
                item.type = "button";
                item.className = "dropdown-item";
                item.textContent = cliente.nombre;
                item.dataset.id = cliente.id;
                listaClientes.appendChild(item);
              });
            } else {
              const item = document.createElement("button");
              item.type = "button";
              item.className = "dropdown-item text-success";
              item.textContent = "➕ Agregar nuevo cliente";
              item.dataset.id = "nuevo"; // Para que dispare el bloque correspondiente
              listaClientes.appendChild(item);
            }

            listaClientes.classList.add("show");
          });
      });

      listaClientes.addEventListener("click", function(e) {
        if (e.target.classList.contains("dropdown-item")) {
          const id = e.target.dataset.id;
          const nombre = e.target.textContent;

          inputCliente.value = nombre;
          idCliente.value = id;
          listaClientes.classList.remove("show");

          if (id === "nuevo") {
            contenedorNuevos.style.display = "block";
          } else {
            contenedorNuevos.style.display = "none";
            nombreNuevo.value = "";
            claveNuevo.value = "";
            rfcNuevo.value = "";
          }
        }
      });

      // Seleccionar un cliente de la lista
      $(document).on("click", ".dropdown-item", function() {
        $("#cliente_nombre").val($(this).text());
        $("#id_cliente").val($(this).data("id")); // Guarda el ID real
        $("#listaClientes").hide();
      });

      // Ocultar lista si se hace clic fuera
      $(document).click(function(e) {
        if (!$(e.target).closest("#cliente_nombre, #listaClientes").length) {
          $("#listaClientes").hide();
        }
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var preGuiaModal = document.getElementById('preGuiaModal');

      preGuiaModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var pedidoId = button.getAttribute('data-pedido-id');
        var clienteNombre = button.getAttribute('data-cliente-nombre');

        document.getElementById('modalPedidoId').value = pedidoId;
        document.getElementById('clienteNombre').value = clienteNombre;
        document.getElementById('clienteNombreHidden').value = clienteNombre;

        // Consulta al backend para saber si tiene los datos completos
        fetch('Back/Clientes/get_info_cliente.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'nombre=' + encodeURIComponent(clienteNombre)
          })
          .then(res => res.json())
          .then(data => {
            if (!data || Object.values(data).some(val => val === null || val === '')) {
              // Mostrar campos para completar info
              document.getElementById('camposExtraCliente').style.display = 'block';
            } else {
              document.getElementById('camposExtraCliente').style.display = 'none';
            }

            // Si quieres precargar lo que sí tenga:
            document.getElementById('inputCalle').value = data.Calle ?? '';
            document.getElementById('inputColonia').value = data.Colonia ?? '';
            document.getElementById('inputCiudad').value = data.Ciudad ?? '';
            document.getElementById('inputEstado').value = data.Estado ?? '';
            document.getElementById('inputCP').value = data.CP ?? '';
          });
      });
    });
  </script>

  <script>
    /// Revisar si el folio de entrega ya existe:
    document.addEventListener('DOMContentLoaded', function() {
      const inputFolio = document.getElementById('folio_entrega');
      const btnGuardar = document.getElementById('btnGuardar');
      const mensajeFolio = document.createElement('small');

      mensajeFolio.classList.add('text-danger', 'mt-1');
      inputFolio.parentNode.appendChild(mensajeFolio);

      inputFolio.addEventListener('input', function() {
        const folio = this.value.trim();

        if (folio.length > 0) {
          fetch('Back/Entregas/validar_entregas.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'folio_entrega=' + encodeURIComponent(folio)
            })
            .then(res => res.json())
            .then(data => {
              if (data.existe) {
                mensajeFolio.textContent = `⚠️ El folio ya se encuentra registrado en la salida ${data.id_salida}`;
                btnGuardar.disabled = true;
              } else {
                mensajeFolio.textContent = '';
                btnGuardar.disabled = false;
              }
            })
            .catch(err => {
              console.error('Error al validar el folio:', err);
              mensajeFolio.textContent = '';
              btnGuardar.disabled = false;
            });
        } else {
          mensajeFolio.textContent = '';
          btnGuardar.disabled = false;
        }
      });
    });
  </script>

  <!-- Eliminar Folio de salida -->
  <script>
    $(document).on('click', '.btn-eliminar-folio', function() {
      const id = $(this).data('id');
      const cliente = $(this).data('cliente');

      if (!id) {
        console.error('No se ha encontrado Id');
        return;
      }

      Swal.fire({
        title: 'Estas Seguro?',
        html: `Estas apunto de eliminar el folio para <strong>${cliente}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, Eliminar!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Eliminando...',
            didOpen: () => Swal.showLoading()
          });

          $.ajax({
            url: 'Back/Etiquetas/eliminar_folio.php',
            type: 'POST',
            dataType: 'json', // ✅ Parse JSON automatically
            data: {
              id: id
            },
            success: function(json) {
              console.log('Parsed JSON from server:', json);
              if (json.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Eliminado!',
                  text: json.message || 'Se ha eliminado el folio.',
                  timer: 2000,
                  showConfirmButton: false
                });

                // Optional: Remove the row or reload
                $('#row-' + id).fadeOut(300, function() {
                  $(this).remove();
                });
                location.reload();
              } else {
                Swal.fire('Error', json.message || 'Algo Salio mal.', 'error');
              }
            },
            error: function(xhr, status, error) {
              Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
              console.error('AJAX error:', status, error);
              console.error('Response text:', xhr.responseText);
            }
          });



        }
      });
    });
  </script>

</body>

</html>