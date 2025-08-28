<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
require  'config_mail.php';
$conn = connectMySQLi();
session_start();
$HumanR = ($_SESSION['User_Id'] == 26 || $_SESSION['User_Id'] == 27 );
  
$Nombre = $_SESSION['Name'];

// si son de rh, que la variable $Nombre = nombre_solicitante proveniente del front
if ($HumanR) {
  $Nombre = $_POST['nombre_solicitante'];
}

if($_POST['estadorh'] == ''){
  echo "Estado no seleccionado";
  $estado_rh = "Proceso";
} else {
  $estado_rh = $_POST['estadorh'];
}

$fecha_inicio = $_POST['fecha_inicio'];
$fecha_final = $_POST['fecha_final'];
$tipo_permiso = $_POST['tipo_permiso'];
$Fecha_Hoy = date("Y-m-d H:i:s");

// Obtener Gerente del usuario solicitante
$sql_gerente = "SELECT * FROM usuarios WHERE Nombre = '$Nombre'";
$result_gerente = $conn->query($sql_gerente);
if ($result_gerente->num_rows > 0) {
  $row_gerente = $result_gerente->fetch_assoc();
  $jerarquia = $row_gerente['Jerarquia'];
  $Correo_Solicitante = $row_gerente['Email'];
} else {
  echo "Error: No se encontró el gerente del usuario.";
  exit;
}
echo "<br>Correo Solicitante: " . $Correo_Solicitante;
echo "<br><strong>- Nombre</strong>: " . $Nombre;
echo "<br><strong>- Fecha Inicio</strong>: " . $fecha_inicio;
echo "<br><strong>- Fecha Final</strong>: " . $fecha_final;
echo "<br><strong>- Tipo de Permiso</strong>: " . $tipo_permiso;
echo "<br><strong>- Fecha de Solicitud</strong>: " . $Fecha_Hoy;
echo "<br><strong>- Gerente</strong>: " . $jerarquia;

// Obtener el Correo del Gerente
$sql_correo_gerente = "SELECT Email FROM usuarios WHERE Nombre = '$jerarquia'";
$result_correo_gerente = $conn->query($sql_correo_gerente);
if ($result_correo_gerente->num_rows > 0) {
  $row_correo_gerente = $result_correo_gerente->fetch_assoc();
  $correo_gerente = $row_correo_gerente['Email'];
} else {
  echo "Error: No se encontró el correo del gerente.";
  exit;
}
echo "<br><strong>- Correo Gerente</strong>: " . $correo_gerente;

// Usuario, Dias_Solicitados, Fecha_Inicio, Fecha_Fin, Fecha_Solicitud, Tipo_Permiso, Estado

$anio = date('Y');

function getMexicanHolidays($year)
{
  $holidays = [
    "$year-01-01", // Año Nuevo
    "$year-02-03", // Constitución (primer lunes feb)
    "$year-03-17", // Benito Juárez (tercer lunes marzo)
    "$year-04-17", // Jueves Santo
    "$year-04-18", // Viernes Santo
    "$year-05-01", // Día del Trabajo
    "$year-09-16", // Independencia
    "$year-11-17", // Revolución (tercer lunes nov)
    "$year-12-25", // Navidad
  ];

  return $holidays;
}

$Dias_Feriados = getMexicanHolidays($anio);

$fechas = array_merge(
  getMexicanHolidays(2024),
  getMexicanHolidays(2025)
);
function contarDiasHabiles($fechaInicio, $fechaFin, $feriados = [])
{
  $inicio = new DateTime($fechaInicio);
  $fin = new DateTime($fechaFin);
  $fin->modify('+1 day');
  $intervalo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
  $diasHabiles = 0;
  foreach ($intervalo as $fecha) {
    $diaSemana = $fecha->format('N');
    $fechaStr = $fecha->format('Y-m-d');
    if ($diaSemana < 6 && !in_array($fechaStr, $feriados)) {
      $diasHabiles++;
    }
  }
  return $diasHabiles;
}

$Dias_Habiles = contarDiasHabiles($fecha_inicio, $fecha_final, $fechas);

echo "<br><strong>- Dias Habiles</strong>: " . $Dias_Habiles;


/// Insertar En la base de datos
$sql_insert_vacaciones = "INSERT INTO vacaciones_solicitudes (Usuario, Dias_Solicitados, Fecha_Inicio, Fecha_Fin, Fecha_Solicitud, Tipo_Permiso, Estado) 
VALUES ('$Nombre', '$Dias_Habiles', '$fecha_inicio', '$fecha_final', '$Fecha_Hoy', '$tipo_permiso', '$estado_rh')";
$result_insert_vacaciones = $conn->query($sql_insert_vacaciones);
if ($result_insert_vacaciones) {
  echo "<br><strong>Solicitud de vacaciones enviada correctamente.</strong>";
} else {
  echo "<br><strong>Error al enviar la solicitud de vacaciones: " . $conn->error . "</strong>";
}

/// Enviar Correo de Notificación al Gerente
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../../vendor/phpmailer/phpmailer/src/Exception.php';

$mail = new PHPMailer(true);

try {
  // Configuración del servidor
  $mail->SMTPDebug = 0; //Enable verbose debug output
  $mail->isSMTP();
  $mail->Host = 'smtp.office365.com'; //Set the SMTP server to send through
  $mail->SMTPAuth = true; //Enable SMTP authentication  
  $mail->Username = 'alenapp2@alenintelligent.com'; //SMTP username
  $encrypted_pass = 'aWFNL3l0ZEMyR0Jma3FYdFFCa3gyZ3FkbEpHcGF1RDFwalord1JmUFlaaz0==';
  $mail->Password = decryptPassword($encrypted_pass);
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
  $mail->Port = 587; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
  $mail->CharSet = 'UTF-8';
  // Destinatarios
  $mail->setFrom('alenapp2@alenintelligent.com', 'Solicitud de Vacaciones Aceptada');
  $mail->addAddress($correo_gerente, $jerarquia); /// Agregar Destinatario

  // Contenido
  $mail->isHTML(true);
  $mail->Subject = ' 📝 Nueva Solicitud de ' . $tipo_permiso . ' de ' . $Nombre;
  $mail->AltBody = 'Este es un mensaje automático. Por favor no responda a este correo.';
  $mail->Body = '
  <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background-color: #f9f9f9;">
    <h2 style="color: #2c3e50; text-align: center;">🤩 Solicitud de Vacaciones</h2>
    <p style="font-size: 16px; color: #333;">
      Estimado/a:,
    </p>
    <p style="font-size: 16px; color: #333;">
      El colaborador <strong style="color: #007bff;">' . htmlspecialchars($Nombre) . '</strong> ha registrado
       una <strong>solicitud de ' . $tipo_permiso . '</strong> en el sistema, con los siguientes detalles:
    </p>
    <ul style="font-size: 16px; color: #555; line-height: 1.6;">
      <li><strong>📅 Fecha de Inicio:</strong> ' . htmlspecialchars($fecha_inicio) . '</li>
      <li><strong>📅 Fecha de Fin:</strong> ' . htmlspecialchars($fecha_final) . '</li>
      <li><strong>⏳ Días Solicitados:</strong> ' . htmlspecialchars($Dias_Habiles) . '</li>
      <li><strong>📜 Tipo de Permiso:</strong> ' . htmlspecialchars($tipo_permiso) . '</li>
      <li><strong>📍  Fecha de Solicitud:</strong> ' . htmlspecialchars($Fecha_Hoy) . '</li>
    </ul>
    <p style="font-size: 16px; color: #333;">
      Le solicitamos ingresar al sistema para <strong>revisar y autorizar</strong> dicha solicitud.
    </p>
    <div style="text-align: center; margin-top: 20px;">
      <a href="https://alenapps.com/" 
         style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        👉 Revisar Solicitud
      </a>
    </div>
    <p style="margin-top: 30px; font-size: 14px; color: #888; text-align: center;">
      Este es un mensaje automático. Por favor no responda a este correo.
    </p>
  </div>
';

  $mail->send();
  echo '✅ Correo enviado al gerente.';


  // Limpiamos destinatarios anteriores
  $mail->clearAddresses();

  // Configuramos el correo pa' el usuario
  $mail->setFrom('alenapp2@alenintelligent.com', 'Confirmación de Solicitud');
  $mail->addAddress($Correo_Solicitante, $Nombre);
  $mail->Subject = ' 📢 Confirmación: Tu solicitud de ' . $tipo_permiso . ' ha sido recibida';

  $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background-color: #f9f9f9;">
        <h2 style="color: #2c3e50; text-align: center;">✅ Solicitud Registrada</h2>
        
        <p style="font-size: 16px; color: #333;">
            Hola <strong style="color: #007bff;">' . htmlspecialchars($Nombre) . '</strong>,
        </p>
        
        <p style="font-size: 16px; color: #333;">
            Tu solicitud de <strong>' . $tipo_permiso . '</strong> ha sido recibida correctamente y está siendo revisada.
        </p>
        
        <div style="background-color: #e8f4ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #0056b3; margin-top: 0;">📋 Resumen de tu solicitud:</h3>
            <ul style="font-size: 16px; color: #555; line-height: 1.6; padding-left: 20px;">
                <li><strong>🗓 Fechas:</strong> Del ' . htmlspecialchars($fecha_inicio) . ' al ' . htmlspecialchars($fecha_final) . '</li>
                <li><strong>🔢 Días solicitados:</strong> ' . htmlspecialchars($Dias_Habiles) . '</li>
                <li><strong>🕒 Fecha de registro:</strong> ' . htmlspecialchars($Fecha_Hoy) . '</li>
                <li><strong>👨‍💼 Enviada a:</strong> ' . htmlspecialchars($jerarquia) . '</li>
            </ul>
        </div>
        
        <p style="font-size: 16px; color: #333;">
            Recibirás una notificación cuando tu solicitud sea aprobada. 
            <span style="color: #28a745;">¡Gracias por usar nuestro sistema!</span>
        </p>
        
        <p style="margin-top: 30px; font-size: 14px; color: #888; text-align: center;">
            Este es un mensaje automático. No es necesario responder.
        </p>
    </div>
    ';

  $mail->send();
  echo '✅ Correo de confirmación enviado al solicitante.';
} catch (Exception $e) {
  echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
}

header("Location: /Vacaciones/Front/detalles.php?Nombre=" . $Nombre); // Redirigir a la página de detalles con el nombre del usuario
