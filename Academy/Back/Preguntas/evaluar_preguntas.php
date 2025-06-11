<?php 
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
date_default_timezone_set('America/Mexico_City');

$respuesta_usuario = $_POST['respuesta'];
$pregunta_id = $_POST['pregunta_id'];
$capitulo_id = $_POST['capitulo_id'];
$curso_id = $_POST['curso_id'];
$Nombre = $_SESSION['Name'];

$query = "SELECT * FROM academy_respuestas WHERE Pregunta = ? AND Capitulo = ? AND Curso = ? AND Es_Correcta = 1 LIMIT 1";
$stmt = $conn->prepare($query); // 4 - 4 - 2
$stmt->bind_param("iii", $pregunta_id, $capitulo_id, $curso_id);
$stmt->execute();
$result = $stmt->get_result();

$respuesta_correcta = null;
if ($row = $result->fetch_assoc()) {
    $respuesta_correcta = trim($row['Respuesta']);
}

// Comparar la respuesta del usuario
if ($respuesta_correcta !== null && trim($respuesta_usuario) === $respuesta_correcta) {
    $response['correcta'] = true;
    // Redirige para guardar progreso y registrar respuesta correcta
    $response['url_siguiente'] = "Back/Preguntas/procesar_respuesta.php?pregunta_id=$pregunta_id&respuesta=$respuesta_usuario&curso_id=$curso_id&capitulo_id=$capitulo_id&correcta=1";
} else {

    // 👇 INSERTA la respuesta incorrecta directamente
    $sql = "INSERT INTO academy_responses (Nombre, Curso, Capitulo, Pregunta, Respuesta, Estado, Fecha)
            VALUES (?, ?, ?, ?, ?, 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiis", $Nombre, $curso_id, $capitulo_id, $pregunta_id, $respuesta_usuario);
    $stmt->execute();
    // El JSON devolverá false, y frontend mostrará el feedback

    // También puedes redirigir aunque haya sido incorrecta
    $response['url_siguiente'] = "Back/Preguntas/procesar_respuesta.php?pregunta_id=$pregunta_id&respuesta=$respuesta_usuario&curso_id=$curso_id&capitulo_id=$capitulo_id&correcta=0";
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($response);

?>