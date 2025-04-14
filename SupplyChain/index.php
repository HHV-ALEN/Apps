<?php
include "../Back/config/config.php";
session_start();

if (isset($_SESSION['alerta_estado'])) {
  echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
  unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();

// Pagination logic
$records_per_page = 7;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get total records count
$total_records_query = "SELECT COUNT(*) as total FROM salidas";
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = mysqli_fetch_assoc($total_records_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch records for current page
$query_salida = "SELECT salidas.*, entregas.Id_Orden_Venta 
                 FROM salidas
                 LEFT JOIN entregas ON salidas.Id = entregas.Id_Salida
                 ORDER BY salidas.Id DESC 
                 LIMIT $offset, $records_per_page";

$result_salida = mysqli_query($conn, $query_salida);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
  <style>
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
  <?php
  if ($_SESSION['Puesto'] == 'Chofer') {
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

            $query_salida = "SELECT p.Id, p.Id_Salida, p.Cliente, p.Tipo_Doc, p.Paqueteria, s.Estado 
                         FROM preguia p
                         INNER JOIN salidas s ON p.Id_Salida = s.Id
                         WHERE s.Estado = 'A Ruta' AND p.Chofer = '{$_SESSION['Name']}'
                         ORDER BY p.Id DESC";

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
          <form action="Back/Entregas/entregaChofer.php" method="POST" id="form_agregar_empaque">
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
              </div>
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
  ?>

  <div class="container mt-5 text-center">
    <div class="card shadow-lg border-0">
      <div class="card-header">
        <div class="row text-center">
          <div class="col-md-12">
            <h2>Listado General</h2>
            <?php
            if ($_SESSION['Puesto'] == 'Entrega') {
            ?>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarModal">
                <i class="bi bi-plus-lg"></i> Agregar Nueva Etiqueta de Salida
              </button>

            <?php

            }

            ?>
          </div>

          <div class="col-md-12">
            <div class="row">
              <div class="col-md-6">
                <label for="buscar_salida" class="form-label">Número de Salida:</label>
                <input type="text" class="form-control" id="buscar_salida" placeholder="Buscar...">
              </div>
              <div class="col-md-6">
                <label for="buscar_cliente" class="form-label">Cliente:</label>
                <select class="form-select" id="buscar_cliente">
                  <option value="">Todos</option>
                  <?php
                  $query_cliente = mysqli_query($conn, "SELECT DISTINCT Nombre_Cliente FROM salidas ORDER BY Nombre_Cliente");
                  while ($row = mysqli_fetch_assoc($query_cliente)) {
                    echo "<option value='{$row['Nombre_Cliente']}'>{$row['Nombre_Cliente']}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <label for="buscar_orden" class="form-label">Orden de Venta:</label>
                <input type="text" class="form-control" id="buscar_orden" placeholder="Buscar...">
              </div>
              <div class="col-md-6">
                <label for="buscar_estado" class="form-label">Buscar por Estado:</label>
                <select class="form-select" id="buscar_estado">
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
                <th>ID</th>
                <th>Nombre Cliente</th>
                <th>Orden De Venta</th>
                <th>Estado</th>
                <th>Sucursal</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla_resultados">
              <?php while ($fila = mysqli_fetch_assoc($result_salida)):
                $Id_Orden_Venta = $fila['Id_Orden_Venta'] ?? 'N/A';
              ?>
                <tr class="<?= $fila['Urgencia'] == 'Si' ? 'table-danger' : 'table-success' ?>">
                  <td><?= htmlspecialchars($fila['Id']) ?></td>
                  <td><?= htmlspecialchars($fila['Nombre_Cliente']) ?></td>
                  <td><?php echo  $Id_Orden_Venta  ?></td> <!-- Manejo de valores nulos -->
                  <td><?= htmlspecialchars($fila['Estado']) ?></td>
                  <td><?= htmlspecialchars($fila['Sucursal']) ?></td>

                  <td>
                    <a class='btn btn-primary' href='Front/detalles.php?id=<?= $fila['Id'] ?>'>
                      <i class="bi bi-file-earmark-medical"></i> Detalles
                    </a>
                    <?php
                    /// Para Recbir la entrega con el puesto Empaque
                    if ($_SESSION['Puesto'] == 'Empaque' && $fila['Estado'] == 'Entrega') {
                      echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Empaque' class='btn btn-warning'>
                        <i class='bi bi-box-seam me-2'></i> Recibir Entrega (Empaque)
                      </a>";
                    }

                    /// Para recibir de estado Empaque a Facturación:
                    if ($_SESSION['Puesto'] == 'Facturación' && $fila['Estado'] == 'Empaque') {
                      echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Facturación' class='btn btn-warning'>
                      <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Facturación)
                    </a>";
                    }
                    /// Para recibir de estado Facturación a Logistica:
                    if ($_SESSION['Puesto'] == 'Logistica' && $fila['Estado'] == 'Facturación') {
                      echo "<a href='Back/changeState.php?id=" . $fila['Id'] . "&estado=Logistica' class='btn btn-warning'>
                      <i class='bi bi-file-earmark-fill'></i> Recibir Entrega (Logistica)
                    </a>";
                    }


                    if ($_SESSION['Puesto'] == 'Logistica' && $fila['Estado'] == 'Logistica') {
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
                    ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <!-- Previous Button -->
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link"
                href="?page=<?= $current_page - 1 ?>"
                aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>

            <!-- Page Numbers -->
            <?php for ($page = 1; $page <= $total_pages; $page++): ?>
              <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $page ?>">
                  <?= $page ?>
                </a>
              </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link"
                href="?page=<?= $current_page + 1 ?>"
                aria-label="Next">
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
              <div class="col-md-12 position-relative">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="cliente_nombre" placeholder="Escribe el nombre del cliente..." autocomplete="off">
                  <input type="hidden" id="id_cliente" name="id_cliente"> <!-- ID del cliente oculto -->
                  <label for="cliente_nombre">Selecciona un Cliente</label>
                  <div id="listaClientes" class="dropdown-menu w-100"></div> <!-- Opciones dinámicas -->
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
                      <option value="nada">Sin Prioridad</option>
                      <option value="Media">Urgente</option>
                      <option value="Alta">Urgente Cliente Pasa</option>
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
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <br>
    <br>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
          console.log("Cliente seleccionad", cliente);

          $.ajax({
            url: "Back/buscar_salidas.php",
            type: "POST",
            data: {
              numero_salida: numero_salida,
              cliente: cliente,
              orden_venta: orden_venta,
              estado: estado
            },
            dataType: "json",

            success: function(response) {
              let tbody = $("#tabla_resultados");
              tbody.empty(); // Limpiar tabla antes de insertar nuevos datos
              //console.log(response);
              if (response.length > 0) {

                response.forEach(function(item) {
                  console.log(item);
                  tbody.append(`
    <tr>
        <td>${item.Id}</td>
        <td>${item.Nombre_Cliente}</td>
        <td>${item.Id_Orden_Venta ? item.Id_Orden_Venta : 'N/A'}</td>
        <td>${item.Estado}</td>
        <td>${item.Sucursal}</td>
        <td>
            <a href='Front/detalles.php?id=${item.Id}' class='btn btn-primary detalles-btn'>
                <i class="bi bi-file-earmark-medical"></i> Detalles
            </a>
        </td>
    </tr>
`);


                });
              } else {
                tbody.append('<tr><td colspan="7" class="text-center text-danger">No se encontraron resultados</td></tr>');
              }
            }

          });
        }


        $("#buscar_btn").on("click", function() {
          buscarSalidas();
        });

        // Para que se actualice al escribir en los inputs sin dar clic en el botón
        $("#buscar_salida, #buscar_cliente, #buscar_orden, #buscar_estado").on("keyup change", function() {
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
                                    <option value="Brandon Alexis Hernandez Robles">Brandon Alexis Hernandez Robles
                                    </option>
                                    <option value="Daniel Soto Mayor">Daniel Soto Mayor</option>
                                    <option value="Jonathan Islas Hernandez">Jonathan Islas Hernandez</option>
                                    <option value="Rene Canche Couoh">Rene Canche Couoh</option>
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
                                    <option value="Brandon Alexis Hernandez Robles">Brandon Alexis Hernandez Robles
                                    </option>
                                    <option value="Daniel Soto Mayor">Daniel Soto Mayor</option>
                                    <option value="Jonathan Islas Hernandez">Jonathan Islas Hernandez</option>
                                    <option value="Rene Canche Couoh">Rene Canche Couoh</option>
                                    <option value="Rene Canche Couoh">Rene Canche Couoh</option>
                                    <option value="">---------------------------</option>
                                    <option value="Cliente Pasa">Cliente Pasa</option>
                                    <option value="Entregado por Vendedor">Entregado por Vendedor</option>
                                    <option value="Proveedor Recolecta">Proveedor Recolecta</option>
                                </select>
                                </select>
                                <label for="Chofer">Chofer:</label>
                            </div>
                        </div>

    `;

          // Agregar los campos según la selección
          if (tipo == "Directo" || tipo == "Reembarque") {
            extraFields.innerHTML += commonFields;
          }
          if (tipo === "Reembarque") {
            extraFields.innerHTML += `<div class="row">${clienteIntermedioField}</div>`;
          }
          if (tipo === "Ruta") {
            extraFields.innerHTML += choferField;
          }

          if (!extraFields) {
            console.error("Element with ID 'extraFields' not found!");
            return;
          }
        });
      });

      // Replace your existing event listener with this:
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
      $(document).ready(function() {
        $("#cliente_nombre").on("keyup", function() {
          let query = $(this).val();
          if (query.length > 1) { // Solo busca si hay más de 1 letra
            $.ajax({
              url: "Back/buscar_clientes.php",
              method: "POST",
              data: {
                query: query
              },
              dataType: "json",
              success: function(data) {
                let dropdown = $("#listaClientes");
                dropdown.empty().show();

                if (data.length > 0) {
                  data.forEach(cliente => {
                    dropdown.append(`<div class="dropdown-item text-truncate" data-id="${cliente.id}" title="${cliente.nombre}">${cliente.nombre}</div>`);
                  });
                } else {
                  dropdown.append(`<div class="dropdown-item text-muted">No hay coincidencias</div>`);
                }
              }
            });
          } else {
            $("#listaClientes").hide();
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

</body>

</html>