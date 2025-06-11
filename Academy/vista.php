<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();
date_default_timezone_set('America/Mexico_City');
$id_curso = (int)$_GET['id_curso'];
$capitulo = (int)$_GET['capitulo'];
$Nombre = $_SESSION['Name'];
$Fecha_hoy = date("Y-m-d H:i:s");
//echo "<br> Id Curso: " . $id_curso;
//echo "<br> Capitulo: " . $capitulo;

// Verificar si el capítulo actual está completo
$stmt = $conn->prepare("SELECT Completado FROM academy_progreso WHERE Usuario = ? AND Curso = ? AND Capitulo = ?");
$stmt->bind_param("sii", $Nombre, $id_curso, $capitulo);
$stmt->execute();
$result = $stmt->get_result();
$isCompleted = false;

if ($row = $result->fetch_assoc()) {
    $isCompleted = $row['Completado'] == 1;
}

$stmt->close();

// Si ya está completo, buscar el siguiente capítulo
if ($isCompleted) {
    // Buscar el capítulo siguiente
    $stmt_next = $conn->prepare("SELECT Id FROM academy_capitulos WHERE Id_Curso = ? AND Id > ? ORDER BY Id ASC LIMIT 1");
    $stmt_next->bind_param("ii", $id_curso, $capitulo);
    $stmt_next->execute();
    $result_next = $stmt_next->get_result();
    if ($row_next = $result_next->fetch_assoc()) {
        $next_capitulo = $row_next['Id'];
        // Redirigir al siguiente capítulo
        header("Location: vista.php?id_curso=$id_curso&capitulo=$next_capitulo");
        exit;
    }
    // Si no hay siguiente capítulo, se puede mostrar mensaje o terminar curso
    echo "¡Felicidades! Has completado el curso.";
    header("Location: Final.php?id_curso=$id_curso");
    exit;
}

// Si no está completo, mostrar el capítulo actual
$query_capitulo = "SELECT * FROM academy_capitulos WHERE Id_Curso = $id_curso AND Id = $capitulo";
$result_capitulo = $conn->query($query_capitulo);
$capitulo_info = $result_capitulo ? $result_capitulo->fetch_assoc() : null;

if ($capitulo_info) {
    $videoURL = $capitulo_info['Fuente'];

    // Extraer ID del video de YouTube
    parse_str(parse_url($videoURL, PHP_URL_QUERY), $urlParams);
    $videoId = $urlParams['v'] ?? '';

    // Aquí haces el embed del video con el ID
} else {
    echo "Capítulo no encontrado.";
}

/// Verificar si el Capitulo actual ya cuenta con un registro en academy_progreso
/// Si no lo tiene, crear uno con la información del capitulo actual
/// Si lo tiene, ps no hay pedo

// usuario - curso - capitulo
/// Buscar si tiene ya un registro
    $query = "SELECT * FROM academy_progreso WHERE Usuario = '$Nombre' AND Curso = $id_curso AND Capitulo = $capitulo";
    $result = $conn->query($query);
    if ($result && $result->num_rows == 0) {
        // Si no existe, insertar nuevo progreso
        $sql = "INSERT INTO academy_progreso (Usuario, Curso, Capitulo, Fecha_Inicio) 
            VALUES ('$Nombre', $id_curso, $capitulo, '$Fecha_hoy')";
        if($conn->query($sql)){
            //echo "<br> Inserción de nuevo progreso";
        }else{
            //echo "<br> No se Inserto Debido a que ya existe";
        }

    } 

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Capítulo - Alen Academy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

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

    <!-- Contenedor del reproductor -->
    <div class="container mt-5">
        <div class="ratio ratio-16x9">
            <div id="player"></div>
        </div>
    </div>

    <!-- Modal de Pregunta -->
    <div class="modal fade" id="modalPregunta" tabindex="-1">
        <div class="modal-dialog">
            <form id="formPregunta">
                <div id="feedback" class="mt-2"></div>
                <div class="modal-content p-3">
                    <h5 class="modal-title">Pregunta del capítulo</h5>
                    <div class="modal-body">
                        <div class="row align-items-center">
                            <!-- Columna de Pregunta y Respuestas -->
                            <div class="col-md-8">
                                <?php
                                $query_pregunta = "SELECT * FROM academy_preguntas WHERE Capitulo = $capitulo AND Curso = $id_curso LIMIT 1";
                                $result_pregunta = $conn->query($query_pregunta);

                                if ($result_pregunta && $pregunta = $result_pregunta->fetch_assoc()) {
                                    $pregunta_id = $pregunta['Id'];
                                    echo "<p><strong>" . htmlspecialchars($pregunta['Pregunta']) . "</strong></p>";

                                    // Consultar respuestas
                                    $query_respuestas = "SELECT * FROM academy_respuestas WHERE Pregunta = $capitulo AND Curso = $id_curso";
                                    $result_respuestas = $conn->query($query_respuestas);

                                    if ($result_respuestas && $result_respuestas->num_rows > 0) {
                                        echo "<form method='post' action='procesar_respuesta.php'>";
                                        while ($respuesta = $result_respuestas->fetch_assoc()) {
                                            $respuesta_id = $respuesta['Id'];
                                            $respuesta_Nombre = $respuesta['Respuesta'];

                                            echo "<div class='form-check'>";
                                            echo "<input class='form-check-input' type='radio' name='respuesta' id='respuesta_$respuesta_id' value='$respuesta_Nombre' required>";
                                            echo "<label class='form-check-label' for='respuesta_$respuesta_id'>" . htmlspecialchars($respuesta['Respuesta']) . "</label>";
                                            echo "</div>";
                                        }

                                        // Campos ocultos y botón
                                        echo "<input type='hidden' name='pregunta_id' value='$pregunta_id'>";
                                        echo "<input type='hidden' name='capitulo_id' value='$capitulo'>";
                                        echo "<input type='hidden' name='curso_id' value='$id_curso'>";
                                        echo "<button type='submit' class='btn btn-dark mt-3'>Enviar respuesta</button>";
                                        echo "</form>";
                                    } else {
                                        echo "<p>No hay respuestas disponibles para esta pregunta.</p>";
                                    }
                                } else {
                                    echo "<p>No hay pregunta disponible para este capítulo.</p>";
                                }
                                ?>
                            </div>

                            <!-- Columna con la imagen del monito -->
                            <div class="col-md-4 text-center d-none d-md-block">
                                <img src="Front/img/Alenitos/Astral.png" alt="Alien animado" class="img-fluid animate__animated animate__bounce animate__infinite" style="max-height: 180px;" />
                                <p class="mt-2 fw-bold">¡Vamos, tú puedes!</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <br>
    <hr>
    <br>

    <!-- YouTube API -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formPregunta');

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Evita el envío tradicional

                const formData = new FormData(form);

                fetch('Back/Preguntas/evaluar_preguntas.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text()) // 👈 leer como texto en vez de .json()
                    .then(text => {
                        console.log("Raw response:", text); // 🔍 Ver qué responde el PHP
                        try {
                            const data = JSON.parse(text); // Intentar parsear manualmente
                            console.log(data);
                            if (data.correcta) {
                                window.location.href = data.url_siguiente;

                            } else {
                                document.getElementById('feedback').innerHTML = `
                                <style>
                                @keyframes vibrar {
                                        0% { transform: translateX(0); }
                                        25% { transform: translateX(-3px); }
                                        50% { transform: translateX(3px); }
                                        75% { transform: translateX(-3px); }
                                        100% { transform: translateX(0); }
                                        }

                                        .alert-danger img {
                                        animation: vibrar 0.3s ease-in-out 2;
                                        }
                                
                                </style>
                                    <div class="alert alert-danger d-flex align-items-center" style="gap: 15px;">
                                        <img src="Front/img/Alenitos/enojado.png" alt="Personaje enojado" style="height: 120px;" />
                                        <div>
                                        <strong>Noo chula, esa no es la respuesta correcta</strong><br>
                                        Inténtalo de nuevo.
                                        </div>
                                    </div>
                                    `;
                            }
                        } catch (e) {
                            console.error("JSON parse error:", e);
                        }
                    })
                    .catch(err => {
                        console.error('Error al procesar la respuesta:', err);
                    });
            });
        });
    </script>


    <script>
        let player;
        const videoId = '<?php echo $videoId; ?>';
        console.log("Video Id:" + videoId);

        // Cargar la API de YouTube
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        // Crear el reproductor
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: '390',
                width: '640',
                videoId: videoId,
                playerVars: {
                    'playsinline': 1,
                    'controls': 0,
                    'disablekb': 1
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }

        // Iniciar reproducción cuando esté listo
        function onPlayerReady(event) {
            event.target.playVideo();
        }

        // Detectar fin del video
        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                console.log("Video terminado.");
                const modal = new bootstrap.Modal(document.getElementById('modalPregunta'));
                modal.show();
            }
        }
    </script>


</body>

</html>