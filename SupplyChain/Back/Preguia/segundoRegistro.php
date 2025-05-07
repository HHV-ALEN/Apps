<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$firstname = $_SESSION['Name'];
$id_salida = $_GET['id_salida'];
$Tipo_doc = $_POST['Tipo_Doc'];
$folio_doc = $_POST['folio_doc'];
$costo = $_POST['costo'];
$Fecha_Actual = date("Y-m-d H:i:s");
$FechaFinal = $_POST['FechaFinal'];
$GuiaReembarque = $_POST['GuiaReembarque'] ?? "No Asignado";


echo "<br><strong>Información del form: </strong>";
echo "<br> Id Salida: " . $id_salida;
echo "<br> Tipo Doc: " . $Tipo_doc;
echo "<br> Folio Doc: " . $folio_doc;
echo "<br> Costo: " . $costo;
echo "<br> Fecha Final: " . $FechaFinal;
echo "<br> Guia Reembarque: " . $GuiaReembarque;
echo "<br> Fecha Actual: " . $Fecha_Actual;

echo "<br><strong>Información de la preguía:</strong>";

/* Actualizar campos en la tabla doc_preguia
*Directo:
    - Folio_Doc
    - Costo_Directo
    - Guia_Directo
    - Fecha_Final
*Reembarque:
    - Folio_Doc
    - Costo_Reembarque
    - Guia_Reembarque
    - Fecha_Final
*/

if ($Tipo_doc == "Directo") {
    $updateDocPreGuia = "UPDATE doc_preguia SET Folio_Doc='$folio_doc', Costo_Directo='$costo', Guia_Directo='$folio_doc', Fecha_Final='$FechaFinal' WHERE Id_Salida='$id_salida'";
} elseif ($Tipo_doc == "Reembarque") {
    $updateDocPreGuia = "UPDATE doc_preguia SET Folio_Doc='$folio_doc', Costo_Reembarque='$costo', Guia_Reembarque='$GuiaReembarque', Fecha_Final='$FechaFinal' WHERE Id_Salida='$id_salida'";
} elseif($Tipo_doc == "Ruta"){
    $updateDocPreGuia = "UPDATE doc_preguia SET Fecha='$Fecha_Actual' WHERE Id_Salida='$id_salida'";
}

else {
    echo "<br><strong>Error: Tipo de documento no válido</strong>";
    exit;
}

if ($conn->query($updateDocPreGuia) === TRUE) {
    echo "<br><strong>Registro de preguía exitoso</strong>";
} else {
    echo "<br><strong>Error al registrar la preguía: </strong>" . $conn->error;
}

/// Actualizar Salida
$updateSalida = "UPDATE salidas SET Estado ='Completado', Id_Status = 27 WHERE Id ='$id_salida'";
if ($conn->query($updateSalida) === TRUE) {
    echo "<br><strong>Registro de salida exitoso</strong>";
} else {
    echo "<br><strong>Error al registrar la salida: </strong>" . $conn->error;
}

// Registrar en bitacora
$insertBitacora = "INSERT INTO bitacora
 (Id_Salida, Responsable, Fecha, Accion) VALUES 
 ('$id_salida', '$firstname', '$Fecha_Actual', 'Registro de complemento de preguía')";
if ($conn->query($insertBitacora) === TRUE) {
    echo "<br><strong>Registro de bitacora exitoso</strong>";
} else {
    echo "<br><strong>Error al registrar la bitacora: </strong>" . $conn->error;
}


header("Location: ../../Front/detalles.php?id=$id_salida");




?>