<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();

$id_curso = $_GET['id_curso'];
$Nombre = $_SESSION['Name'];
// Obtener nombre del curso
$nombre_curso = "Desconocido";
$query_curso = $conn->prepare("SELECT Titulo FROM academy_cursos WHERE Id_Curso = ?");
$query_curso->bind_param("i", $id_curso);
$query_curso->execute();
$result_curso = $query_curso->get_result();

if ($row_curso = $result_curso->fetch_assoc()) {
    $nombre_curso = $row_curso['Titulo'];
}

// Obtener capítulos del curso
$capitulos = [];
$query_capitulos = $conn->prepare("SELECT Id, Titulo FROM academy_capitulos WHERE Id_Curso = ? ORDER BY Id ASC");
$query_capitulos->bind_param("i", $id_curso);
$query_capitulos->execute();
$result_capitulos = $query_capitulos->get_result();

while ($row_capitulo = $result_capitulos->fetch_assoc()) {
    $capitulos[] = [
        'id' => $row_capitulo['Id'],
        'titulo' => $row_capitulo['Titulo']
    ];
}

$estadisticas = [];

foreach ($capitulos as $capitulo) {
    $capitulo_id = $capitulo['id'];

    $query_resp = $conn->prepare("
        SELECT 
            SUM(CASE WHEN Estado = 'Correcto' THEN 1 ELSE 0 END) AS correctas,
            SUM(CASE WHEN Estado = '0' THEN 1 ELSE 0 END) AS incorrectas
        FROM academy_responses
        WHERE Nombre = ? AND Curso = ? AND Capitulo = ?
    ");
    $query_resp->bind_param("sii", $Nombre, $id_curso, $capitulo_id);
    $query_resp->execute();
    $result_resp = $query_resp->get_result();
    $row_resp = $result_resp->fetch_assoc();

    $estadisticas[$capitulo_id] = [
        'correctas' => $row_resp['correctas'] ?? 0,
        'incorrectas' => $row_resp['incorrectas'] ?? 0
    ];
}


$tiempos_capitulos = [];

$query_tiempo = $conn->prepare("SELECT Capitulo, Fecha_Inicio, Fecha_Completado FROM academy_progreso WHERE Usuario = ? AND Curso = ?");
$query_tiempo->bind_param("ii", $Nombre, $id_curso);
$query_tiempo->execute();
$result_tiempo = $query_tiempo->get_result();
while ($row = $result_tiempo->fetch_assoc()) {
    $inicio = new DateTime($row['Fecha_Inicio']);
    $fin = new DateTime($row['Fecha_Completado']);
    $intervalo = $inicio->diff($fin);
    $tiempos_capitulos[$row['Capitulo']] = $intervalo->format('%H:%I:%S');
}

$query_total = $conn->prepare("SELECT MIN(Fecha_Inicio) AS inicio, MAX(Fecha_Completado) AS fin FROM academy_progreso WHERE Usuario = ? AND Curso = ?");
$query_total->bind_param("ii", $Nombre, $id_curso);
$query_total->execute();
$result_total = $query_total->get_result();
$row_total = $result_total->fetch_assoc();

$tiempo_total = "N/A";
if ($row_total['inicio'] && $row_total['fin']) {
    $inicio = new DateTime($row_total['inicio']);
    $fin = new DateTime($row_total['fin']);
    $intervalo_total = $inicio->diff($fin);
    $tiempo_total = $intervalo_total->format('%H:%I:%S');
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>¡Curso Completado!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .final-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
            max-width: 600px;
        }

        .icon-success {
            font-size: 3rem;
            color: #198754;
        }
    </style>
</head>

<body>
    <?php include "../Front/navbar.php"; ?>
    <br>
    <div class="container  d-flex flex-column justify-content-center align-items-center">
        <div class="card final-card p-4 w-100">
            <div class="text-center">

                <div class="mb-2">
                    <img src="Front/img/Alenitos/AlienCorazon.png" alt="Alien celebración" style="height: 100px; max-width: 100%;" />
                </div>

                <h2 class="mt-3 mb-1">¡Enhorabuena!</h2>
                <p class="text-muted mb-0">Has completado la Evaluación del Curso</p>
                <h4 class="fw-bold text-dark mt-2"><?php echo htmlspecialchars($nombre_curso); ?></h4>
            </div>
            
            <hr>

            <div class="d-grid gap-2 mt-4">
                <a href="Back/Examen/descargarCertificado.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-dark">Descargar Certificado</a>
            </div>
        </div>
    </div>
    <br>
    <br>
</body>

</html>