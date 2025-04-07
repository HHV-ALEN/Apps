<?php
include "../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
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
  <div class="container mt-5">
    <div class="card shadow-lg border-0">
      <div class="card-body">
        <h4 class="card-title">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['Username']); ?></strong></h4>
        <p class="text-muted">Fecha de Ingreso: <?php echo htmlspecialchars($_SESSION['Date']); ?></p>

        <div class="row">
          <!-- Left Column -->
          <div class="col-md-6">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['Name']); ?></li>
              <li class="list-group-item"><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['Mail']); ?></li>
              <li class="list-group-item"><strong>Área:</strong> <?php echo htmlspecialchars($_SESSION['Area']); ?></li>
            </ul>
          </div>

          <!-- Right Column -->
          <div class="col-md-6">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['Role']); ?></li>
              <li class="list-group-item"><strong>Sucursal:</strong> <?php echo htmlspecialchars($_SESSION['Sucursal']); ?></li>
              <li class="list-group-item"><strong>Puesto:</strong> <?php echo htmlspecialchars($_SESSION['Puesto']); ?></li>
            </ul>
          </div>
        </div>

        <hr>
      </div>
    </div>
  </div>

  <br>
  <div class="container mt-5">
    <div class="card shadow-lg border-0 p-4">
      <div class="card-body">
        <h4 class="mb-4">Funciones Disponibles</h4>
        <hr>
        <?php
        // Define the user's role and area
        $role = $_SESSION['Role'];
        $Area_Del_Personal = $_SESSION['Area'];

        // Determine the permisos based on the role
        if ($role == 'Gerente' || $role == 'Admin' || $role == 'Control') {
          // Gerente, Admin, and Control can see both "Basicos" and "Superiores"
          $permisos = ['Basicos', 'Superiores'];
        } else {
          // For other roles (e.g., Empleado), only show "Basicos"
          $permisos = ['Basicos'];
        }

        // Initialize the array to store available functions
        $Arreglo_De_Funciones_Disponibles = array();

        // Build the query based on the permisos
        if (is_array($permisos)) {
          // If $permisos is an array (for Gerente, Admin, Control), use IN clause
          $permisosList = "'" . implode("','", $permisos) . "'";
          $query = "SELECT * FROM funciones 
            WHERE (Area = '$Area_Del_Personal' AND Permisos IN ($permisosList)) 
            OR (Area = 'General' AND Permisos IN ($permisosList))";
        } else {
          // If $permisos is a string (for Empleado), use the original logic
          $query = "SELECT * FROM funciones 
            WHERE (Area = '$Area_Del_Personal' AND Permisos = '$permisos') 
            OR (Area = 'General' AND Permisos = '$permisos')";
        }

        // Execute the query
        $result = mysqli_query($conn, $query);

        // Fetch the results and store them in the array
        while ($row = mysqli_fetch_assoc($result)) {
          $Arreglo_De_Funciones_Disponibles[] = array(
            "Id" => $row['Id'],
            "Nombre" => $row['Nombre'],
            "Permisos" => $row['Permisos'],
            "Ruta" => $row['Ruta']
          );
        }

        // Debugging: Print the array (optional)
        //print_r($Arreglo_De_Funciones_Disponibles);
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
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>