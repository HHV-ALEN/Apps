<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
date_default_timezone_set('America/Mexico_City');
$respuestas = $_POST['respuestas'];       // [ pregunta_id => respuesta_id ]
$id_curso   = $_POST['id_curso'];

$usuario = $_SESSION['Name'];
$fecha   = date('Y-m-d H:i:s');

$correctas   = 0;
$total_pregs = count($respuestas);

// ▸ GUARDA cada respuesta y cuenta aciertos
foreach ($respuestas as $pregunta_id => $respuesta_id) {

    $stmt = $conn->prepare(
       "SELECT Es_Correcta, Capitulo, Respuesta
        FROM academy_respuestas
        WHERE Id = ? AND Curso = ?"
    );
    $stmt->bind_param("ii", $respuesta_id, $id_curso);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) continue;  // seguridad

    $es_correcta     = (int)$row['Es_Correcta'];
    $capitulo        = (int)$row['Capitulo'];
    $texto_respuesta = $row['Respuesta'];
    $estado          = $es_correcta ? 'Correcto' : 'Incorrecto';

    // Insertar en tabla de respuestas
    $ins = $conn->prepare(
      "INSERT INTO academy_test_responses
       (Nombre, Pregunta, Respuesta, Estado, Fecha, Curso, Capitulo)
       VALUES (?,?,?,?,?,?,?)"
    );
    $ins->bind_param(
      "sisssii",
      $usuario,
      $pregunta_id,
      $texto_respuesta,
      $estado,
      $fecha,
      $id_curso,
      $capitulo
    );
    $ins->execute();

    if ($es_correcta) $correctas++;
}

// ▸ Calcular porcentaje
$porcentaje = ($correctas / $total_pregs) * 100;

// ▸ Guardar resultado global si quieres
$saveRes = $conn->prepare(
  "INSERT INTO academy_test_responses
   (Nombre, Curso, Estado, Fecha)
   VALUES (?,?,?,?)"
);
$saveRes->bind_param(
  "siii",
  $usuario, $id_curso, $correctas, $fecha
);
$saveRes->execute();

/* ---------- LOGICA DE APROBACIÓN ---------- */
if ($porcentaje >= 80) {
    // Éxito: redirige a página de certificado
    $_SESSION['examen_msg'] = "🎉 ¡Felicidades! Aprobaste con $porcentaje % de aciertos.";
    header("Location: ../../certificado.php?id_curso=$id_curso");
    exit;
} else {
    // Reprobado: volver a intentar
    $_SESSION['examen_msg'] = "⚠️ Obtuviste $porcentaje % (mínimo 80 %). Intenta de nuevo.";
    header("Location: ../../FinalRetry.php?id_curso=$id_curso&retry=1");
    exit;
}
