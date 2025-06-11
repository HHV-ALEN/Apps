<?php
include "../Back/config/config.php";
session_start();

$id_curso = $_GET['id_curso'];
$Nombre_Usuario = $_SESSION['Name'];
$conn = connectMySQLi();

$sql = "SELECT * FROM academy_capitulos WHERE id_curso = $id_curso ORDER BY orden ASC";
$result = $conn->query($sql);

$capitulosTotales = [];
while ($row = $result->fetch_assoc()) {
  $capitulosTotales[] = $row;
}

//echo "<br><h1>Capítulos del Curso (Totales)</h1>";
//print_r($capitulosTotales);

/// 1.- Obtener los capitulos del curso
/// 2.- Obtener los capitulos que el usuario ya ha completado

$sql_progreso = "SELECT * FROM academy_progreso WHERE Usuario = '$Nombre_Usuario' AND Curso = $id_curso AND Completado = 1";
$result_progreso = $conn->query($sql_progreso);

$capitulosCompletados = [];
while ($row = $result_progreso->fetch_assoc()) {
  $capitulosCompletados[] = $row['Capitulo'];
}
//echo "<br><h1>Capítulos del Curso (Completados)</h1>";
//print_r($capitulosCompletados);


// Obtener los IDs de todos los capítulos del curso
$idsCapitulos = array_column($capitulosTotales, 'Id');

// Comprobar si todos los capítulos están en el arreglo de completados
$completadosTodos = !array_diff($idsCapitulos, $capitulosCompletados);

if ($completadosTodos) {
  // Redirigir a la página final del curso
  header("Location: Final.php?id_curso=$id_curso");
  exit;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapa del Curso</title>
  <!-- Css Import -->
  <link rel="stylesheet" href="Front/css/dashboard.css">
  <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
  <?php include "../Front/navbar.php"; ?>
  <div class="mapa-container">
    <img src="Front/img/campus2.png" alt="Mapa Campus" class="mapa-fondo">
    <?php foreach ($capitulosTotales as $capitulo):
      $id = $capitulo['Id'];
      $orden = $capitulo['Orden'];
      $titulo = $capitulo['Titulo'];
      $descripcion = $capitulo['Descripcion'];
      $top = $capitulo['Coordenada_Top'] ? $capitulo['Coordenada_Top'] : '0px';
      $left = $capitulo['Coordenada_Left'] ? $capitulo['Coordenada_Left'] : '0px';

      $completado = in_array($id, $capitulosCompletados);
      $clase = $completado ? '' : 'incompleto';
    ?>
      <div
        class="icono-capitulo <?= $clase ?>"
        style="top: <?= $top ?>; left: <?= $left ?>;"
        data-id="<?= $id ?>"
        data-orden="<?= $orden ?>"
        data-titulo="<?= htmlspecialchars($titulo) ?>"
        data-descripcion="<?= htmlspecialchars($descripcion) ?>"
        data-curso="<?= $id_curso ?>"
        data-completado="<?= $completado ? '1' : '0' ?>"
        onclick="mostrarModal(this)">
        <img src="Front/img/cubo.png" width="100%" height="100%">
      </div>
    <?php endforeach; ?>

  </div>
  <br>
  <!-- Modal para los detalles del capitulo -->
  <div class="modal fade" id="modalCapitulo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModal">Título del Capítulo</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body d-flex align-items-center">
          <!-- Columna Izquierda (Mascota) -->
          <div class="col-4 text-center">
            <img src="Front/img/Alenitos/AlenitoCompu.png" alt="Alenito" class="img-fluid mascota-modal">
          </div>

          <!-- Columna Derecha (Texto) -->
          <div class="col-8">
            <h5 id="tituloModal">Título del Capítulo</h5>
            <p id="descripcionModal">Descripción...</p>
          </div>
        </div>

        <div class="modal-footer">
          <a id="btnVerCapitulo" class="btn btn-dark">Ver capítulo</a>
          <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS + Popper + jQuery -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function mostrarModal(el) {
      const titulo = el.dataset.titulo;
      const descripcion = el.dataset.descripcion;
      const capitulo = el.dataset.id;
      const curso = el.dataset.curso;
      const completado = el.dataset.completado;

      if (completado === "1") {
        alert("✅ Este capítulo ya fue completado.");
        return; // Evita que se abra el modal
      }

      console.log(titulo);

      document.getElementById('tituloModal').textContent = titulo;
      document.getElementById('descripcionModal').textContent = descripcion;
      document.getElementById('btnVerCapitulo').href = `Back/conteo.php?id_curso=${curso}&capitulo=${capitulo}&razon=Inicio`;

      $('#modalCapitulo').modal('show');
    }
  </script>

</body>

</html>