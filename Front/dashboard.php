<?php
include "../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="Img/Icono-A.png" />

</head>

<body>
  <?php include "navbar.php"; ?>
  <?php

  if (isset($_SESSION['mensaje'])) {
    echo "<div class='alert alert-{$_SESSION['tipo_mensaje']} alert-dismissible fade show' role='alert'>
          {$_SESSION['mensaje']}
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";

    // Elimina el mensaje para que no se muestre nuevamente al refrescar
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
  }

  ?>

  <div class="container py-4">
    <!-- User Profile Card -->
    <div class="card border-0 shadow-lg overflow-hidden">
      <!-- Card Header with Gradient Background -->
      <div class="card-header bg-primary bg-gradient text-white py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="h4 mb-0">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['Username']); ?></strong></h2>
            <small class="text-white-50">Miembro de Alen desde: <?php echo htmlspecialchars($_SESSION['Date']); ?></small>
            <!-- 
            <button><a href="../Academy/index.php">Index - Academy</a></button>
            <button><a href="../SupplyChain/Front/compras.php">Compras</a></button> -->

          </div>
          <div class="bg-white rounded-circle p-2 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#0d6efd" class="bi bi-person-circle" viewBox="0 0 16 16">
              <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
              <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Card Body -->
      <div class="card-body p-0">
        <div class="row g-0">
          <!-- Left Column -->
          <div class="col-md-6 p-4 border-end">
            <div class="mb-4">
              <h5 class="text-primary mb-3"><i class="bi bi-person-lines-fill me-2"></i>Información Personal</h5>
              <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                  <span class="fw-bold">Nombre:</span>
                  <span class="text-muted"><?php echo htmlspecialchars($_SESSION['Name']); ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                  <span class="fw-bold">Email:</span>
                  <span class="text-muted"><?php echo htmlspecialchars($_SESSION['Mail']); ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                  <span class="fw-bold">Area:</span>
                  <span class="text-muted"><?php echo htmlspecialchars($_SESSION['Area']); ?></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Enhanced User Info Section with Password Change Button -->
          <div class="col-md-6 p-4">
            <div class="mb-4">
              <h5 class="text-primary mb-3"><i class="bi bi-building me-2"></i>Información General</h5>
              <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                  <span class="fw-bold">Departamento:</span>
                  <span class="text-muted"><?php echo htmlspecialchars($_SESSION['Departamento']); ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                  <span class="fw-bold">Sucursal:</span>
                  <span class="text-muted"><?php echo htmlspecialchars($_SESSION['Sucursal']); ?></span>
                </div>
              </div>

              <!-- Password Change Button - Better Placement -->
              <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCambiarContrasena">
                  <i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
                </button>
              </div>
            </div>
          </div>

          <!-- Password Change Modal -->
          <div class="modal fade" id="modalCambiarContrasena" tabindex="-1" aria-labelledby="modalCambiarContrasenaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="modalCambiarContrasenaLabel">
                    <i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
                  </h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="passwordChangeForm" action="Users/Back/change_pass.php" method="POST">
                  <div class="modal-body">
                    <!-- Current Password -->
                    <div class="mb-3">
                      <label for="currentPassword" class="form-label">Contraseña Actual</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                      <label for="newPassword" class="form-label">Nueva Contraseña</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                      <div class="form-text">Tiene que ser de 5 a 20 Caracteres</div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-3">
                      <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="bi bi-eye"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Password Strength Meter -->
                    <div class="progress mb-3" style="height: 5px;">
                      <div id="passwordStrength" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="passwordHelp" class="text-muted"></small>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitPasswordChange">Actualizar Contraseña</button>
                  </div>
                </form>
              </div>
            </div>
          </div>



          <!-- JavaScript for Password Toggle and Validation -->
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              // Toggle password visibility
              document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                  const input = this.previousElementSibling;
                  const icon = this.querySelector('i');
                  if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                  } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                  }
                });
              });

              // Password strength checker
              const newPassword = document.getElementById('newPassword');
              const passwordStrength = document.getElementById('passwordStrength');
              const passwordHelp = document.getElementById('passwordHelp');

              newPassword.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                updateStrengthMeter(strength);
              });

              function checkPasswordStrength(password) {
                let strength = 0;

                // Length check
                if (password.length >= 4) strength++;
                if (password.length >= 12) strength++;

                // Character variety
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                return Math.min(strength, 5); // Max strength of 5
              }

              function updateStrengthMeter(strength) {
                const percentage = strength * 20;
                passwordStrength.style.width = percentage + '%';

                if (strength < 2) {
                  passwordStrength.className = 'progress-bar bg-danger';
                  passwordHelp.textContent = 'Weak password';
                } else if (strength < 4) {
                  passwordStrength.className = 'progress-bar bg-warning';
                  passwordHelp.textContent = 'Moderate password';
                } else {
                  passwordStrength.className = 'progress-bar bg-success';
                  passwordHelp.textContent = 'Strong password';
                }
              }

              // Form validation
              document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (newPassword !== confirmPassword) {
                  e.preventDefault();
                  alert('Contraseñas no coinciden!');
                }

                if (newPassword.length < 5) {
                  e.preventDefault();
                  alert('La contraseña tiene que tener por lo menos 5 Caracteres!');
                }
              });
            });
          </script>


        </div>
      </div>

      <!-- Quick Actions Section -->
      <div class="card-footer bg-light">
        <div class="text-center py-3">
          <h5 class="text-primary mb-3"><i class="bi bi-sun me-2"></i>Vacaciones</h5>
          <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
            <?php
            $fechaAntiguedad = new DateTime($_SESSION['Date']);
            $hoy = new DateTime();
            $intervalo = $fechaAntiguedad->diff($hoy);

            // Verifica si ha pasado 1 año o más
            $activo = ($intervalo->y >= 1);
            ?>
            <!-- Muestra la fecha para fines de debugg
            <p>Antigüedad desde: <?= htmlspecialchars($_SESSION['Date']) ?></p>-->

            <?php if ($activo): ?>
              <button class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#modalSolicitarVacaciones">
                <i class="bi bi-file-earmark-plus me-2"></i>Nueva Solicitud
              </button>
              <a href="../Vacaciones/Front/detalles.php?Nombre=<?= htmlspecialchars($_SESSION['Name']) ?>" class="btn btn-success flex-grow-1">
                <i class="bi bi-list-check me-2"></i>Mis Solicitudes
              </a>
            <?php else: ?>
              <button class="btn btn-secondary flex-grow-1" disabled>
                <i class="bi bi-lock me-2"></i>Aún no disponible
              </button>
            <?php endif; ?>
            <?php if ($_SESSION['Role'] != 'Empleado') { ?>
              <a href="../Vacaciones/Front/listado_revision.php" class="btn btn-warning flex-grow-1">
                <i class="bi bi-clock-history me-2"></i>Pendientes
              </a>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Para solicitar Vacaciones -->
  <div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1" aria-labelledby="modalSolicitarVacacionesLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formVacaciones" method="POST" action="../Vacaciones/Back/Solicitar_vacaciones.php">
          <div class="modal-header">
            <h5 class="modal-title" id="modalSolicitarVacacionesLabel">Formulario de Vacaciones</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="fechaInicio" class="form-label">Fecha de Inicio</label>
              <input type="date" class="form-control" id="fechaInicio" name="fecha_inicio" required>
            </div>
            <div class="mb-3">
              <label for="fechaFinal" class="form-label">Fecha Final</label>
              <input type="date" class="form-control" id="fechaFinal" name="fecha_final" required>
            </div>
            <div class="mb-3">
              <label for="tipoPermiso" class="form-label">Tipo de Permiso</label>
              <select class="form-select" id="tipoPermiso" name="tipo_permiso" required>
                <option value="">Seleccione una opción</option>
                <option value="Permiso Especial">Permiso Especial</option>
                <option value="Vacaciones">Vacaciones</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-aceptar" data-loading-text="Procesando...">Enviar Solicitud</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container mt-5">
    <div class="card shadow-lg border-0 p-4">
      <div class="card-body">
        <h4 class="mb-4">Funciones Disponibles</h4>
        <hr>
        <?php
        // Define the user's role and area
        $role = $_SESSION['Role'];
        $Area_Del_Personal = $_SESSION['Area'];
        $Departamento_Del_Personal = $_SESSION['Departamento'];
        // echo "Rol: " . $role;
        // echo "<br>";

        // Determine the permisos based on the role
        if ($role == 'Gerente' || $role == 'Admin' || $role == 'Coordinador' || $role == 'Control') {
          // Gerente, Admin, and Coordinador can see both "Basicos" and "Superiores"
          $permisos = ['Basicos', 'Superiores'];
        } elseif ($role == 'Empleado') {
          // For other roles (e.g., Empleado), only show "Basicos"
          $permisos = ['Basicos'];
        }

        // print_r($permisos);
        // Initialize the array to store available functions
        $Arreglo_De_Funciones_Disponibles = array();

        $DepartamentosAlmacen = ['Entrega', 'Empaque', 'Facturación', 'Logistica', 'Chofer'];

        $Arreglo_De_Funciones_Disponibles = [];

        if (in_array('Superiores', $permisos)) {
          // echo "<br> Entrando al query de nivel Superior";
          $query_superior = "SELECT * FROM funciones 
                     WHERE (Area = '$Area_Del_Personal' OR Area = 'General') 
                     AND Permisos = 'Superiores'";
          $result = mysqli_query($conn, $query_superior);
          while ($row = mysqli_fetch_assoc($result)) {
            $Arreglo_De_Funciones_Disponibles[] = $row;
          }
        }

        if (in_array('Basicos', $permisos)) {
          // echo "<br> Entrando al query de nivel Básico";
          $query_basico = "SELECT * FROM funciones 
                   WHERE (Area = '$Area_Del_Personal' OR Area = 'General') 
                   AND Permisos = 'Basicos'";
          $result = mysqli_query($conn, $query_basico);
          while ($row = mysqli_fetch_assoc($result)) {
            $Arreglo_De_Funciones_Disponibles[] = $row;
          }
        }
        ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php foreach ($Arreglo_De_Funciones_Disponibles as $funcion): ?>
            <div class="col">
              <div class="card h-100 border-0 shadow-sm p-3">
                <h5 class="card-title text-primary"><?php echo htmlspecialchars($funcion['Nombre']); ?></h5>
                <p class="text-muted">Permiso: <?php echo htmlspecialchars($funcion['Permisos']); ?></p>
                <a href="<?php echo htmlspecialchars($funcion['Ruta']); ?>" class="btn btn-primary btn-block">Ir a <?php echo htmlspecialchars($funcion['Nombre']); ?></a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <hr>
  <br>
  <?php include "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    /// Animación para evitar que se den muchos clicks en el submit:
    document.addEventListener("DOMContentLoaded", function() {
      const botonesAceptar = document.querySelectorAll(".btn-aceptar");

      botonesAceptar.forEach(boton => {
        boton.addEventListener("click", function(e) {
          if (boton.classList.contains("disabled")) {
            e.preventDefault(); // Evita doble click
            return;
          }y
          boton.classList.add("disabled");
          boton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...`;
        });
      });
    });
  </script>
</body>

</html>