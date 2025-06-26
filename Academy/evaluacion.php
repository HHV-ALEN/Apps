<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$usuario = $_SESSION['Name'];
$id_curso = $_GET['id_curso'];
$id_curso = (int) $_GET['id_curso'];
$conn = connectMySQLi();

// Obtener todas las preguntas del curso
$query_preguntas = $conn->prepare("SELECT * FROM academy_preguntas WHERE Curso = ? ORDER BY Id ASC");
$query_preguntas->bind_param("i", $id_curso);
$query_preguntas->execute();
$result_preguntas = $query_preguntas->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>¡Evaluación Final!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom, #eaf4ff, #ffffff);
            font-family: 'Segoe UI', sans-serif;
        }

        .final-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: auto;
        }

        .pregunta-card {
            border-radius: 0.8rem;
            padding: 20px;
            margin-bottom: 25px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .pregunta-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .pregunta-numero {
            font-size: 1.4rem;
            color: #0d6efd;
            font-weight: bold;
        }

        .form-check {
            margin-top: 10px;
            transition: background-color 0.2s ease;
        }

        .form-check:hover {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 4px 8px;
        }

        .btn-success {
            background-color: #198754;
            border: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-success:hover {
            background-color: #157347;
            transform: scale(1.02);
        }

        .examen-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 20px 0;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php include "../Front/navbar.php"; ?>

    <div class="container mt-5">
        <div class="final-card p-4">
            <div class="examen-header">
                <h2 class="mb-3">🧠 Evaluación Final del Curso</h2>
                <p class="text-muted">Responde las siguientes preguntas para finalizar tu aprendizaje</p>
            </div>

            <form action="Back/Examen/procesar_examen.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">

                <?php
                $num = 1;
                while ($pregunta = $result_preguntas->fetch_assoc()):
                    $pregunta_id = $pregunta['Id'];
                    $texto_pregunta = htmlspecialchars($pregunta['Pregunta']);

                    // Obtener respuestas para esta pregunta
                    $query_respuestas = $conn->prepare("SELECT * FROM academy_respuestas WHERE Pregunta = ? AND Curso = ?");
                    $query_respuestas->bind_param("ii", $pregunta_id, $id_curso);
                    $query_respuestas->execute();
                    $result_respuestas = $query_respuestas->get_result();
                ?>
                    <div class="pregunta-card">
                        <div class="pregunta-numero mb-2"><?php echo "$num."; ?></div>
                        <strong class="mb-3 d-block"><?php echo $texto_pregunta; ?></strong>

                        <?php while ($respuesta = $result_respuestas->fetch_assoc()):
                            $respuesta_id = $respuesta['Id'];
                            $texto_respuesta = htmlspecialchars($respuesta['Respuesta']);
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta_id; ?>]" value="<?php echo $respuesta_id; ?>" id="r<?php echo $respuesta_id; ?>" required>
                                <label class="form-check-label" for="r<?php echo $respuesta_id; ?>">
                                    <?php echo $texto_respuesta; ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php
                    $num++;
                endwhile;
                ?>

                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-lg px-4 mt-3">📨 Enviar evaluación</button>
                </div>
            </form>
        </div>
    </div>
    <br>
    <br>


    <!--  academy_responses:
    Id, Nombre, Pregunta, Respuesta, Estado, Fecha, Curso, Capitulo --->

    <!--  academy_Preguntas:
    Id, Capitulo, Curso, Pregunta --->


</body>

</html>