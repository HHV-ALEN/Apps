<?php 
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
date_default_timezone_set('America/Mexico_City');
$id_curso = $_GET['id_curso'];
$usuario = $_SESSION['Name'];
$fecha   = date('Y-m-d H:i:s');

/* ▸ Título del curso (opcional: cámbialo por tu consulta a DB) */
$stmt = $conn->prepare("SELECT Titulo FROM academy_cursos WHERE Id_Curso = ?");
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$cursoRow = $stmt->get_result()->fetch_assoc();
$tituloCurso = $cursoRow ? $cursoRow['Titulo'] : "Curso $id_curso";

/* -------- Generar imagen -------- */
$basePath   =  'certificado.png';          // imagen base
$fontPath   =  'fonts/Ashley.ttf';    // tu fuente
$downloadAs = "Certificado_$usuario.png";

if (!file_exists($basePath) || !file_exists($fontPath)) {
    exit('Base o fuente no encontrada');
}

$img  = imagecreatefrompng($basePath);
$negro = imagecolorallocate($img, 30, 30, 30);

/* ▸ Escribir nombre (centrado) */
$fontSizeNombre = 64;
$bbox  = imagettfbbox($fontSizeNombre, 0, $fontPath, $usuario);
$txtW  = $bbox[2] - $bbox[0];
$imgW  = imagesx($img);
$x     = ($imgW - $txtW) / 2;
$y     = 730;                     // píxel vertical (ajústalo)
imagettftext($img, $fontSizeNombre, 0, $x, $y, $negro, $fontPath, $usuario);

/* ▸ Escribir título del curso */
$fontSizeCurso = 24;
$bbox2 = imagettfbbox($fontSizeCurso, 0, $fontPath, $tituloCurso);
$txtW2 = $bbox2[2] - $bbox2[0];
$x2    = ($imgW - $txtW2) / 2;
$y2    = 600;
imagettftext($img, $fontSizeCurso, 0, $x2, $y2, $negro, $fontPath, $tituloCurso);

/* ▸ Stream de descarga */
ob_end_clean();
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $downloadAs . '"');

imagepng($img, __DIR__ . '/prueba.png');   // guarda a disco

imagepng($img);
imagedestroy($img);
exit;


?>