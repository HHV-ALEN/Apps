<?php
include "../Back/config/config.php";
session_start();
$idCurso = $_GET['id'];
$conn = connectMySQLi();


$query = "SELECT * FROM academy_cursos WHERE Id_Curso = '$idCurso'";
$resultado = mysqli_query($conn, $query);

if (mysqli_num_rows($resultado) > 0) {
    $row = mysqli_fetch_assoc($resultado);  // Changed $result to $resultado
    $TituloCurso = $row['Titulo'];
    $DescripcionCurso = $row['Descripcion'];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Capítulo - Alen Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .chapter-list li:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .video-responsive {
            position: relative;
            padding-bottom: 56.25%;
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
    </style>
</head>

<body>
    <?php include "../Front/navbar.php"; ?>

    <div class="container py-4">

        <!-- Título del Curso -->
        <div class="mb-4 text-center">
            <h2 class="fw-bold" id="nombreCurso"><?php echo $TituloCurso; ?></h2>
            <p class="text-muted"><?php echo $DescripcionCurso ?></p>
        </div>
        <hr>

        <?php
        /// Query para obtener la información del capitulo
        $cap_query = "SELECT * FROM academy_capitulos WHERE Id_Curso = $idCurso";
        $result = mysqli_query($conn, $cap_query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);  // Changed $result to $resultado
            $TituloCapitulo = $row['Titulo'];
        }

        $query = "SELECT * FROM academy_capitulos WHERE Id_Curso = '$idCurso'";
        $resultado = mysqli_query($conn, $query);
        if (mysqli_num_rows($resultado) > 0) {
            $row = mysqli_fetch_assoc(result: $resultado);
            $linkOriginal = $row['Fuente'];

            // Extraer el ID del video
            parse_str(parse_url($linkOriginal, PHP_URL_QUERY), $params);
            $videoId = $params['v'] ?? '';

            // Construir URL embebida
            $linkCapitulo = "https://www.youtube.com/embed/" . $videoId;
        }
        ?>

        <!-- Reproductor y detalles -->
        <div class="row">
            <!-- Reproductor de video -->
            <div class="col-lg-8 mb-4">
                <div class="video-responsive rounded shadow-sm">
                    <div id="player"></div>
                </div>

                <div class="mt-3">
                    <h4 id="tituloCapitulo"><?php echo $TituloCapitulo; ?></h4>
                    <p id="descripcionCapitulo">Este módulo aborda los conceptos básicos de la electrónica dentro de Alen.</p>
                </div>
            </div>
            <!-- Lista de capítulos -->
            <div class="col-lg-4">
                <h5 class="mb-3">📚 Capítulos del curso</h5>
                <ul class="list-group chapter-list">
                    <?php
                    $query = "SELECT * FROM academy_capitulos WHERE Id_Curso = '$idCurso'";
                    $resultado = mysqli_query($conn, $query);
                    // Suponiendo que estás usando el ID del curso recibido por GET o SESSION
                    while ($cap = mysqli_fetch_assoc($resultado)) {
                        echo '<li class="list-group-item">';
                        echo '<strong>' . htmlspecialchars($cap['Titulo']) . '</strong><br>';
                        echo '<small>' . htmlspecialchars($cap['Descripcion']) . '</small>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

        </div>

    </div>

    <!-- Modal del Reactivo -->
    <div class="modal fade" id="reactivoModal" tabindex="-1" aria-labelledby="reactivoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="reactivoModalLabel">📘 Pregunta del Capítulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>¿Qué aprendiste en este capítulo?</strong></p>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="respuesta" id="opcionA" value="A">
                        <label class="form-check-label" for="opcionA">A) Respuesta A</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="respuesta" id="opcionB" value="B">
                        <label class="form-check-label" for="opcionB">B) Respuesta B</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="respuesta" id="opcionC" value="C">
                        <label class="form-check-label" for="opcionC">C) Respuesta C</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="respuesta" id="opcionD" value="D">
                        <label class="form-check-label" for="opcionD">D) Respuesta D</label>
                    </div>
                    <button class="btn btn-primary w-100" onclick="validarRespuesta()">Responder</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cargar API de YouTube -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
    var player;

    // Esperar a que la API y la página estén listas
    window.onload = function () {
        // Esperar a que la API esté lista
        if (typeof YT === "undefined" || typeof YT.Player === "undefined") {
            setTimeout(initPlayer, 500);
        } else {
            initPlayer();
        }
    }

    function initPlayer() {
        player = new YT.Player('player', {
            height: '360',
            width: '100%',
            videoId: '<?php echo $videoId; ?>',
            events: {
                'onStateChange': onPlayerStateChange
            }
        });
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.ENDED) {
            const modal = new bootstrap.Modal(document.getElementById('reactivoModal'));
            modal.show();
        }
    }
</script>


</body>

</html>