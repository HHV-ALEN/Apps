<?php
include "../Back/config/config.php";
session_start();



error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();

if (isset($_SESSION['alerta_estado'])) {
  echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
  unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proveedor</title>
  <style>
    .alerta {
      position: fixed;
      top: 50px;
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

    .fade-in {
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .fade-out {
      animation: fadeOut 0.5s ease-in-out forwards;
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
      }

      to {
        opacity: 0;
      }
    }
  </style>
</head>

<body>
  <?php include "../Front/navbar.php"; ?>
  <!-- Toast Container -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastAlerta" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div id="toastMensaje" class="toast-body">
          <!-- Aquí va el mensaje -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>



  <div class="container mt-4">

    <!-- Botón para abrir el modal -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Clientes Registrados</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente">
        + Agregar Cliente
      </button>
    </div>

    <!-- Tabla responsive -->
    <div class="mb-3">
      <input type="text" class="form-control" id="busquedaCliente" placeholder="Buscar por nombre...">
    </div>

    <div class="container">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>RFC</th>
              <th>Calle</th>
              <th>Ciudad</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="resultadoClientes">
            <!-- Se llena por AJAX -->
          </tbody>
        </table>
      </div>

      <!-- Contenedor para los botones de paginación -->
      <nav>
        <ul class="pagination justify-content-center" id="paginacionClientes">
          <!-- Se llena por AJAX -->
        </ul>
      </nav>
    </div>

    <!-- Modal único para editar cliente -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="formEditarCliente" method="POST" action="Back/Clientes/editarCliente.php">
            <div class="modal-header">
              <h5 class="modal-title" id="modalEditarLabel">Editar Cliente</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <!-- Campos del formulario -->
              <input type="hidden" name="id_cliente" id="editar_id_cliente">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editar_nombre" class="form-label">Nombre</label>
                  <input type="text" class="form-control" id="editar_nombre" name="nombre">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_clave_sap" class="form-label">Clave SAP</label>
                  <input type="text" class="form-control" id="editar_clave_sap" name="clave_sap">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_rfc" class="form-label">RFC</label>
                  <input type="text" class="form-control" id="editar_rfc" name="rfc">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_calle" class="form-label">Calle</label>
                  <input type="text" class="form-control" id="editar_calle" name="calle">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_colonia" class="form-label">Colonia</label>
                  <input type="text" class="form-control" id="editar_colonia" name="colonia">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_ciudad" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" id="editar_ciudad" name="ciudad">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_estado" class="form-label">Estado</label>
                  <input type="text" class="form-control" id="editar_estado" name="estado">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_codigo_postal" class="form-label">Código Postal</label>
                  <input type="text" class="form-control" id="editar_codigo_postal" name="codigo_postal">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editar_telefono" class="form-label">Teléfono</label>
                  <input type="tel" class="form-control" id="editar_telefono" name="telefono">
                </div>


              </div>
              <!-- Agrega más campos según necesites -->
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>



    <!-- Modal para registrar cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg"> <!-- modal-sm, modal-lg, modal-xl -->
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalClienteLabel">Registrar Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formCliente" method="POST" action="Back/Clientes/RegistroCliente.php">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nombre</label>
                  <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Clave SAP</label>
                  <input type="text" name="Clave_Sap" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">RFC</label>
                  <input type="text" name="rfc" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Calle</label>
                  <input type="text" name="calle" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Colonia</label>
                  <input type="text" name="Colonia" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Ciudad</label>
                  <input type="text" name="ciudad" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Estado</label>
                  <input type="text" name="estado" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Código Postal</label>
                  <input type="text" name="codigo_postal" class="form-control" required>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Teléfono</label>
                  <input type="tel" name="telefono" class="form-control">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Guardar</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCvGUGwHQhj4ooPP4Oz1hzO8dS_aNgtrZs&libraries=places"></script>
    <script src="https://unpkg.com/@googlemaps/places@1.0.0/dist/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

function eliminarCliente(id) {
    Swal.fire({
      title: '¿Estás seguro?',
      text: "Se eliminara al cliente",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('Back/Clientes/eraseCliente.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire(
              '¡Eliminado!',
              'El cliente fue marcado como inactivo.',
              'success'
            ).then(() => {
              location.reload(); // recarga la tabla o actualiza con AJAX si prefieres
            });
          } else {
            Swal.fire('Error', 'Hubo un problema al eliminar.', 'error');
          }
        });
      }
    });
  }

document.addEventListener("DOMContentLoaded", function() {
        // Escucha clicks en botones con la clase btn-abrir-modal-editar
        document.body.addEventListener('click', function(e) {
          if (e.target.classList.contains('btn-abrir-modal-editar')) {
            let id = e.target.getAttribute('data-id');

            // Llama al backend para obtener datos del cliente por ID
            fetch('Back/Clientes/obtenerCliente.php?id=' + id)
              .then(res => res.json())
              .then(data => {
                console.log(data); // Para depuración
                // Llena los campos del modal
                document.getElementById('editar_id_cliente').value = data.Id_Original;
                document.getElementById('editar_nombre').value = data.Nombre;
                document.getElementById('editar_rfc').value = data.RFC;
                document.getElementById('editar_calle').value = data.Calle;
                document.getElementById('editar_colonia').value = data.Colonia;
                document.getElementById('editar_ciudad').value = data.Ciudad;
                document.getElementById('editar_estado').value = data.Estado;
                document.getElementById('editar_codigo_postal').value = data.Cp;
                document.getElementById('editar_telefono').value = data.Telefono;
                document.getElementById('editar_clave_sap').value = data.Clave_Sap;

                // Puedes llenar más campos si quieres...
              })
              .catch(err => console.error("Error cargando cliente: ", err));
          }
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

      let paginaActual = 1;

      function cargarClientes(pagina = 1) {
        const busqueda = document.getElementById('busquedaCliente').value;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'Back/Clientes/buscarCliente.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
          if (this.status === 200) {
            const respuesta = JSON.parse(this.responseText);
            document.getElementById('resultadoClientes').innerHTML = respuesta.html;
            document.getElementById('paginacionClientes').innerHTML = respuesta.paginacion;
          }
        };
        xhr.send(`busqueda=${encodeURIComponent(busqueda)}&pagina=${pagina}`);
      }

      // Eventos
      document.getElementById('busquedaCliente').addEventListener('input', () => {
        paginaActual = 1;
        cargarClientes(paginaActual);
      });

      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagina-link')) {
          e.preventDefault();
          paginaActual = parseInt(e.target.dataset.pagina);
          cargarClientes(paginaActual);
        }
      });

      // Cargar al iniciar
      window.onload = () => cargarClientes();
    </script>

</body>

</html>