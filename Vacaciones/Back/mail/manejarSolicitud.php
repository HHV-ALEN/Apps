<?php 
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$Id = $_GET['Id'];
$Accion = $_GET['Accion'];

echo "<br> Id: " . $Id;


echo "<br><strong>Acción Elegida: </strong>" . $Accion;

if($Accion == "Aprobada"){
    //Enviar al Correo de Aprobacion
    header("location: AprobarSolicitud.php?Id='$Id' ");
    
} elseif($Accion == "Rechazada"){

    header("location: rechazarSolicitud.php?Id='$Id' ");
}


?>