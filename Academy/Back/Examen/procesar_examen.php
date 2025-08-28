<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
$Nombre = $_SESSION['Name'];
date_default_timezone_set('America/Mexico_City');
$respuestas = $_POST['respuestas'];       // [ pregunta_id => respuesta_id ]
$id_curso   = $_POST['id_curso'];

$usuario = $_SESSION['Name'];
$fecha   = date('Y-m-d H:i:s');

$correctas   = 0;
$total_pregs = count($respuestas);

$sql = "
  SELECT COALESCE(MAX(Intento), 0) + 1 AS siguiente_intento
  FROM   academy_test_responses
  WHERE  Nombre   = ?
    AND  Curso = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $Nombre, $id_curso); // ajusta $capitulo si lo necesitas
$stmt->execute();
$stmt->bind_result($siguienteIntento);
$stmt->fetch();
$stmt->close();

/* 2) $siguienteIntento ya contiene:
      ▸ 1  si nunca ha respondido
      ▸ n+1 si n era el último intento */
echo "Próximo intento: " . $siguienteIntento;


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
       (Intento, Nombre, Pregunta, Respuesta, Estado, Fecha, Curso, Capitulo, Responsable)
       VALUES (?,?,?,?,?,?,?,?,?)"
    );
    $ins->bind_param(
      "isisssiis",
      $siguienteIntento,
      $usuario,
      $pregunta_id,
      $texto_respuesta,
      $estado,
      $fecha,
      $id_curso,
      $capitulo, 
      $Nombre
    );
    $ins->execute();

    if ($es_correcta) $correctas++;
}

// ▸ Calcular porcentaje
$porcentaje = ($correctas / $total_pregs) * 100;

/* ---------- LOGICA DE APROBACIÓN ---------- */
if ($porcentaje >= 80) {
$completado = 1;
  // Registrar la completación del examen - correctamente
  $sql_completado = "INSERT INTO academy_completado (Usuario, Curso, Completado, Fecha, Calificacion)
                    VALUES (?, ?, ?, ?, ?)";
  $stmt_completado = $conn->prepare($sql_completado);
  $stmt_completado->bind_param('siisi', $usuario, $id_curso, $completado, $fecha, $porcentaje);
  $stmt_completado->execute();

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
