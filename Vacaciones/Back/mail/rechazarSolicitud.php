<?php 
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$Id = $_GET['Id'];

echo "<br> - Id: " . $Id;


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


$mail = new PHPMailer(true);

try {

    //Server settings
    $mail->SMTPDebug = 0; //Enable verbose debug output
    $mail->isSMTP(); //Send using SMTP
    $mail->Host = 'smtp.office365.com'; //Set the SMTP server to send through
    $mail->SMTPAuth = true; //Enable SMTP authentication  
    $mail->Username = 'alenapp@alenintelligent.com'; //SMTP username
    $mail->Password = 'A1enM4IL.'; //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged

    $mail->Port = 587; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    // Configurar el correo para el gerente
    $mail->setFrom('alenapp@alenintelligent.com', 'Solicitud de Vacaciones Aprobada');

    // Enviar correos en CC a los gerentes de las áreas
    $mail->addAddress($Correo_Solicitante, $Usuario);
    $mail->addAddress($Correo_Jerarquia, $Jerarquia_Solicitante);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->Subject = '✨ Solicitud de Vacaciones ';
    /* Añade las chicas de RH
        foreach ($correosRH as $correo) {
            $mail->addCC($correo);
        }*/

    $mail->Body  = '
<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; background-color: #ffffff; margin: auto; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px;">
            <h2 style="margin: 0;">🎉 Solicitud Rechazada</h2>
        </div>
        <div style="padding: 20px;">
            <p>Hola <strong>' . htmlspecialchars($Usuario) . '</strong>,</p>
            <p>Nos complace informarte que la solicitud de <strong>' . htmlspecialchars($Tipo_Permiso) . '</strong> ha sido <span style="color: red; font-weight: bold;">❌ Rechazada </span>.</p>

            <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <tr style="background-color: #f9f9f9;">
                    <td>📅 <strong>Fecha de Solicitud:</strong></td>
                    <td>' . htmlspecialchars($Fecha_Solicitud) . '</td>
                </tr>
                <tr>
                    <td>⏰ <strong>Fecha de Inicio:</strong></td>
                    <td>' . htmlspecialchars($Fecha_Inicio) . '</td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                    <td>⏳ <strong>Fecha Final:</strong></td>
                    <td>' . htmlspecialchars($Fecha_Fin) . '</td>
                </tr>
                <tr>
                    <td>📆 <strong>Días Solicitados:</strong></td>
                    <td>' . htmlspecialchars($Dias_Solicitados) . '</td>
                </tr>
            </table>

            <p>Saludos cordiales,</p>
            <p><strong>Equipo de Recursos Humanos 👩‍💼👨‍💼</strong></p>
        </div>
    </div>
</div>';

    $mail->AltBody = 'Tu solicitud de vacaciones ha sido Rechazada. Consulta detalles con RH.';


    $mail->send();
} catch (Exception $e) {
    echo "❌ Error al enviar el correo: {$mail->ErrorInfo}";
}

header("location: ../../Front/listado_revision.php");

?>