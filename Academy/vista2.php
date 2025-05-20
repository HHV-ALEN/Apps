<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$idCurso = isset($_GET['id']) ? intval($_GET['id']) : 0;
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

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($curso['Titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .video-responsive {
            position: relative;
            padding-bottom: 56.25%;
            padding-top: 30px;
            height: 0;
            overflow: hidden;
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
            <h2 class="text-primary"><?php echo htmlspecialchars($curso['Titulo']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($curso['Descripcion'])); ?></p>
        </div>

        <div class="info-box mb-4">
            <h4 class="text-success">Capítulo <?php echo $capitulo; ?>: <?php echo htmlspecialchars($cap['Titulo']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($cap['Descripcion'])); ?></p>

            <div class="video-responsive rounded shadow">
                <div id="player"></div>
            </div>
        </div>
        <div class="info-box mb-4 d-flex justify-content-between">
            <!-- Botón anterior -->
            <div>
                <?php if ($anterior): ?>
                    <a href="?id=<?php echo $idCurso; ?>&capitulo=<?php echo $anterior['Orden']; ?>" class="btn btn-outline-primary">
                        ← Capítulo <?php echo $anterior['Orden']; ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">No hay capítulo anterior</span>
                <?php endif; ?>
            </div>

            <!-- Botón siguiente -->
            <div>
                <?php if ($siguiente): ?>
                    <a href="?id=<?php echo $idCurso; ?>&capitulo=<?php echo $siguiente['Orden']; ?>" class="btn btn-outline-primary">
                        Capítulo <?php echo $siguiente['Orden']; ?> →
                    </a>
                <?php else: ?>
                    <span class="text-muted">No hay más capítulos</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalPregunta" tabindex="-1" aria-labelledby="modalPreguntaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="evaluar_pregunta.php">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalPreguntaLabel">Pregunta del capítulo</h5>
                        </div>
                        <div class="modal-body">
                            <p>¿Qué te ha parecido el capítulo?</p>
                            <div class="form-group">
                                <label for="respuesta">Tu respuesta:</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="3" required></textarea>
                            </div>
                            <p>¿Cual es el Modelo de Ilumador Necesario??</p>
                            <div class="form-group">
                                <label for="respuesta">Tu respuesta:</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="3" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Responder</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>


    </div>
    <!-- jQuery (requerido por Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- YouTube Iframe API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        let player;

        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                videoId: '<?php echo $videoId; ?>',
                events: {
                    'onStateChange': onPlayerStateChange
                }
            });
        }

        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                // Mostrar el modal con la pregunta
                $('#modalPregunta').modal('show');
            }
        }
    </script>
</body>

</html>