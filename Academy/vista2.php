<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$idCurso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : 0;
$capitulo = isset($_GET['capitulo']) ? intval($_GET['capitulo']) : 1;

// Info del curso
$queryCurso = "SELECT * FROM academy_cursos WHERE Id_Curso = $idCurso";
$resultCurso = mysqli_query($conn, $queryCurso);
$curso = mysqli_fetch_assoc($resultCurso);

// Info del capítulo actual
$queryCapitulo = "SELECT * FROM academy_capitulos WHERE Id_Curso = $idCurso AND Orden = $capitulo";
$resultCap = mysqli_query($conn, $queryCapitulo);
$cap = mysqli_fetch_assoc($resultCap);

// Extraer ID del video
$link = $cap['Fuente'] ?? '';
$videoId = '';

if (strpos($link, 'embed') !== false) {
    // https://www.youtube.com/embed/VIDEOID
    $parts = explode('/', $link);
    $videoId = end($parts);
} elseif (strpos($link, 'watch?v=') !== false) {
    // https://www.youtube.com/watch?v=VIDEOID
    parse_str(parse_url($link, PHP_URL_QUERY), $params);
    $videoId = $params['v'] ?? '';
} elseif (strpos($link, 'youtu.be') !== false) {
    // https://youtu.be/VIDEOID
    $parts = explode('/', parse_url($link, PHP_URL_PATH));
    $videoId = end($parts);
}

// Capítulo anterior
$queryAnterior = "SELECT Orden FROM academy_capitulos WHERE Id_Curso = $idCurso AND Orden < $capitulo ORDER BY Orden DESC LIMIT 1";
$resAnterior = mysqli_query($conn, $queryAnterior);
$anterior = mysqli_fetch_assoc($resAnterior);

// Capítulo siguiente
$querySiguiente = "SELECT Orden FROM academy_capitulos WHERE Id_Curso = $idCurso AND Orden > $capitulo ORDER BY Orden ASC LIMIT 1";
$resSiguiente = mysqli_query($conn, $querySiguiente);
$siguiente = mysqli_fetch_assoc($resSiguiente);

echo "<script>console.log('ID del video: " . $videoId . "');</script>";

?>
<script>
    const videoId = "<?php echo $videoId ?: 'VIDEO_DEFAULT'; ?>";
    console.log("VideoID:", videoId);
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($curso['Titulo']); ?></title>
    <style>
        .video-responsive {
            position: relative;
            padding-bottom: 56.25%;
            padding-top: 30px;
            height: 0;  
        }

        .video-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .info-box {
            background-color: #f8f9fa;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <?php include "../Front/navbar.php"; ?>

    <div class="container mt-5">


        <div class="info-box mb-4">
            <h4 class="text-success">Capítulo <?php echo $capitulo; ?>: <?php echo htmlspecialchars($cap['Titulo']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($cap['Descripcion'])); ?></p>

            <!-- Contenedor del reproductor -->
            <div class="video-responsive rounded shadow">
                <div id="playerContainer">
                    <div id="player"></div>
                </div>
            </div>
        </div>

        <div class="info-box mb-4">
            <h2 class="text-primary"><?php echo htmlspecialchars($curso['Titulo']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($curso['Descripcion'])); ?></p>
        </div>

        <?php if ($anterior): ?>
            <button onclick="window.location.href='?id_curso=<?php echo $idCurso; ?>&capitulo=<?php echo $anterior['Orden']; ?>'" class="btn btn-secondary">Anterior</button>
        <?php endif; ?>

        <?php if ($siguiente): ?>
            <button onclick="window.location.href='?id_curso=<?php echo $idCurso; ?>&capitulo=<?php echo $siguiente['Orden']; ?>'" class="btn btn-primary">Siguiente</button>
        <?php endif; ?>


        <?php
        // Obtener la pregunta del capítulo
        $queryPregunta = "SELECT * FROM academy_preguntas WHERE Curso = $idCurso AND Capitulo = $capitulo";
        $resultPregunta = mysqli_query($conn, $queryPregunta);
        while ($pregunta = mysqli_fetch_array($resultPregunta)) {
            $Id_pregunta = $pregunta['Id'];
            $Pregunta = $pregunta['Pregunta'];
            $Id_Curso = $pregunta['Curso'];
            $Capitulo = $pregunta['Capitulo'];
        }
        ?>

    </div>
    <!-- jQuery (requerido por Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Cargar API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <script>
  let player;
  let currentVideoId = videoId; // del echo PHP anterior

  // 1. Cuando la API está lista
  function onYouTubeIframeAPIReady() {
    loadPlayer(currentVideoId);
  }

  // 2. Cargar o recargar el reproductor
  function loadPlayer(id) {
    if (player) {
      player.destroy(); // destruir instancia vieja si existe
    }

    player = new YT.Player('player', {
      videoId: id,
      width: '100%',
      height: '360',
      playerVars: {
        autoplay: 1,
        controls: 0,
        disablekb: 1,
        rel: 0,
        modestbranding: 1,
      },
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });
  }

  // 3. Cuando el reproductor está listo
  function onPlayerReady(event) {
    event.target.playVideo();
  }

  // 4. Detectar fin del video
  function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.ENDED) {
      $('#modalPregunta').modal('show');
    }
  }

  // 5. Función para cambiar de capítulo (si navegas sin recargar página)
  function cambiarVideo(nuevoId) {
    currentVideoId = nuevoId;
    loadPlayer(currentVideoId);
  }

  // Extra: prevenir errores si se recarga o navega
  window.onunload = () => {
    if (player) player.destroy();
  };
</script>


</body>

</html>