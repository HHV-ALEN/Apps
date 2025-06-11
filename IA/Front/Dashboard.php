<?php
include "../../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];

if (isset($_SESSION['analisis_resultado'])) {
    echo "<div class='alert alert-info'>";
    echo nl2br(htmlspecialchars($_SESSION['analisis_resultado']));
    echo "</div>";
    unset($_SESSION['analisis_resultado']);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - IA</title>
  <link rel="icon" type="image/png" href="Img/Icono-A.png" />

</head>

<body>
  <?php include "../../Front/navbar.php"; ?>

  <div class="container mt-4">
        <!-- Bóton para ir a la página de resultados (resultados.php) -->
     <button class="btn btn-primary mb-3" onclick="window.location.href='resultados.php'">Ver Resultados</button>

  <div class="row row-cols-1 row-cols-md-2 g-4">


    <div class="col">
      <div class="card">
        <img src="../Back/resultados_ia/top_incidentes.png" class="card-img-top" alt="Top incidentes">
        <div class="card-body">
          <h5 class="card-title">Top incidentes</h5>
        </div>
      </div>
    </div>

    <div class="col">
      <div class="card">
        <img src="../Back/resultados_ia/prioridades.png" class="card-img-top" alt="Prioridades">
        <div class="card-body">
          <h5 class="card-title">Prioridades</h5>
        </div>
      </div>
    </div>

    <div class="col">
      <div class="card">
        <img src="../Back/resultados_ia/estatus_pie.png" class="card-img-top" alt="Estatus">
        <div class="card-body">
          <h5 class="card-title">Estatus</h5>
        </div>
      </div>
    </div>

  </div>
</div>



</body>
</html>