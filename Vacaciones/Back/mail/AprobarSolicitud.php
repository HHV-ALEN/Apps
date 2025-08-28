<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
require '../../../vendor/autoload.php';
$Id = $_GET['Id'];
/// Consulta a vacaciones_solicitudes

$sql_solicitudes = "SELECT * FROM vacaciones_solicitudes WHERE Id = $Id";
$result_solicitudes = $conn->query($sql_solicitudes);

if ($result_solicitudes->num_rows > 0) {
    $row_solicitud = $result_solicitudes->fetch_assoc();

    $Usuario = $row_solicitud['Usuario'];
    $Dias_Solicitados = $row_solicitud['Dias_Solicitados'];
    $Fecha_Inicio = $row_solicitud['Fecha_Inicio'];
    $Fecha_Fin  = $row_solicitud['Fecha_Fin'];
    $Fecha_Solicitud = $row_solicitud['Fecha_Solicitud'];
    $Tipo_Permiso  = $row_solicitud['Tipo_Permiso'];
}

echo "<br> <strong>Usuario Solicitante: </strong>" . $Usuario;
echo "<br> <strong>Días Solicitados: </strong>" . $Dias_Solicitados;
echo "<br> <strong>Fecha De Inicio: </strong>" . $Fecha_Inicio;
echo "<br> <strong>Fecha Final: </strong>" . $Fecha_Fin;
echo "<br> <strong>Fecha de Solicitud </strong>" . $Fecha_Solicitud;
echo "<br> <strong>Tipo de Permiso: </strong>" . $Tipo_Permiso;
echo "<hr>";

/// Obtener Correo del Solicitante
$sql_Solicitante = "SELECT * FROM usuarios WHERE Nombre = '$Usuario' ";
$resultado_Solicitante = mysqli_query($conn, $sql_Solicitante);
if (mysqli_num_rows($resultado_Solicitante) > 0) {
    $row_solicitante = mysqli_fetch_assoc($resultado_Solicitante);
    $Correo_Solicitante = $row_solicitante['Email'];
    $Jerarquia_Solicitante = $row_solicitante['Jerarquia'];
}

$sql_Jerarquia = "SELECT * FROM usuarios WHERE Nombre = '$Jerarquia_Solicitante'";
$resultado_Jerarquia = mysqli_query($conn, $sql_Solicitante);
if (mysqli_num_rows($resultado_Jerarquia) > 0) {
    $row_Jerarquia = mysqli_fetch_assoc($resultado_Jerarquia);
    $Correo_Jerarquia = $row_Jerarquia['Email'];
}


echo "<br> - Correo Solicitante: " . $Correo_Solicitante;
echo "<br> - Correo Jerarquia: " . $Correo_Jerarquia;

// Importación de clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require '../../../vendor/phpmailer/phpmailer/src/Exception.php';

$correosRH = ['bibarra@alenintelligent.com', 'bsalazar@alenintelligent.com'];

$mail = new PHPMailer(true);
require  'config_mail.php';


try {

    //Server settings
    $mail->SMTPDebug = 0; //Enable verbose debug output
    $mail->isSMTP(); //Send using SMTP
    $mail->Host = 'smtp.office365.com'; //Set the SMTP server to send through
    $mail->SMTPAuth = true; //Enable SMTP authentication  
    $mail->Username = 'alenapp2@alenintelligent.com'; //SMTP username
    $encrypted_pass = 'aWFNL3l0ZEMyR0Jma3FYdFFCa3gyZ3FkbEpHcGF1RDFwalord1JmUFlaaz0==';
    $mail->Password = decryptPassword($encrypted_pass);

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged

    $mail->Port = 587; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    // Configurar el correo para el gerente
    $mail->setFrom('alenapp2@alenintelligent.com', 'Solicitud de Vacaciones Aprobada');

    // Enviar correos en CC a los gerentes de las áreas
    $mail->addAddress($Correo_Solicitante, $Usuario);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->Subject = '✨ Tu solicitud de vacaciones fue APROBADA';
    
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background-color: #f9f9f9;">
        <div style="background-color: #28a745; color: white; padding: 15px; border-radius: 8px 8px 0 0; text-align: center;">
            <h2 style="margin: 0;">🎉 ¡Solicitud Aprobada!</h2>
        </div>
        
        <div style="padding: 20px;">
            <p>Hola <strong style="color: #007bff;">'.htmlspecialchars($Usuario).'</strong>,</p>
            
            <div style="background-color: #e8f4ff; padding: 15px; border-radius: 8px; margin: 15px 0;">
                <h3 style="color: #0056b3; margin-top: 0;">📋 Detalles de tu aprobación:</h3>
                <ul style="font-size: 16px; color: #555; line-height: 1.6;">
                    <li><strong>🗓 Periodo:</strong> Del '.htmlspecialchars($Fecha_Inicio).' al '.htmlspecialchars($Fecha_Fin).'</li>
                    <li><strong>⏳ Días autorizados:</strong> '.htmlspecialchars($Dias_Solicitados).' días hábiles</li>
                    <li><strong>📌 Tipo:</strong> '.htmlspecialchars($Tipo_Permiso).'</li>
                    <li><strong>📅 Fecha de aprobación:</strong> '.date('d/m/Y').'</li>
                </ul>
            </div>
            
            <p style="font-size: 16px;">Recuerda coordinar con tu equipo antes de ausentarte.</p>
            
            <div style="text-align: center; margin: 25px 0;">
                <a href="https://alenapps.com/" style="padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    👉 Ver solicitud en el sistema
                </a>
            </div>
            
            <p style="font-size: 14px; color: #777; text-align: center;">
                Este es un mensaje automático. No es necesario responder.
            </p>
        </div>
    </div>';

    $mail->send();
    echo '✅ Correo de aprobación enviado al solicitante.<br>';
    $mail->clearAddresses();

    $mail->setFrom('alenapp2@alenintelligent.com', 'Notificación de Vacaciones');
    foreach ($correosRH as $correoRH) {
        $mail->addAddress($correoRH);
    }
    $mail->Subject = '📌 Solicitud aprobada: '.htmlspecialchars($Usuario);
    
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background-color: #f9f9f9;">
        <div style="background-color: #17a2b8; color: white; padding: 15px; border-radius: 8px 8px 0 0; text-align: center;">
            <h2 style="margin: 0;">📋 Nueva aprobación de vacaciones</h2>
        </div>
        
        <div style="padding: 20px;">
            <p>Se ha aprobado la solicitud de <strong style="color: #17a2b8;">'.htmlspecialchars($Usuario).'</strong>:</p>
            
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr style="background-color: #f1f1f1;">
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>Empleado</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">'.htmlspecialchars($Usuario).'</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>Tipo</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">'.htmlspecialchars($Tipo_Permiso).'</td>
                </tr>
                <tr style="background-color: #f1f1f1;">
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>Periodo</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">Del '.htmlspecialchars($Fecha_Inicio).' al '.htmlspecialchars($Fecha_Fin).'</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>Días</strong></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">'.htmlspecialchars($Dias_Solicitados).' días hábiles</td>
                </tr>
            </table>
            
            <p style="font-size: 14px; color: #777; text-align: center;">
                Este es un mensaje automático del sistema de vacaciones.
            </p>
        </div>
    </div>';

    $mail->send();
    echo '✅ Notificación enviada a Recursos Humanos.<br>';
    

} catch (Exception $e) {
    echo "❌ Error al enviar el correo: {$mail->ErrorInfo}";
}

header("location: ../../Front/listado_revision.php");

?>