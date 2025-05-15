<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
//print_r($_POST);

$id_salida = $_GET['id_salida'] ?? '';
$Folio_Orden = $_POST['Folio_Orden'];
$Folio_Entrega = $_POST['Folio_Entrega'];
$FolioFactura = $_POST['FolioFactura'];
$factura = $_FILES['archivo']['name'];

echo "<strong> Información del Post: </strong>";
echo "<br>- Folio_Orden: " . $Folio_Orden;
echo "<br>- Folio_Entrega: " . $Folio_Entrega;
echo "<br>- Folio_Factura: " . $FolioFactura;
echo "<br>- Factura: " . $factura;


// Subir archivo PDF a la carpeta Facturas
$target_dir = "../Files/Facturas/";


$target_file = $target_dir . basename($_FILES['archivo']['name']);

// Verificar si el archivo es un PDF (opcional)
$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
if ($file_type !== "pdf") {
    die("Solo se permiten archivos PDF.");
}

// Mover el archivo subido a la carpeta de destino
if (move_uploaded_file($_FILES['archivo']['tmp_name'], $target_file)) {
    echo "<br>El archivo " . htmlspecialchars(basename($_FILES['archivo']['name'])) . " se ha subido correctamente.";
} else {
    echo "<br>Hubo un error al subir el archivo.";
}

$Fecha_Hoy = date("Y-m-d H:i:s");
$Responsable = $_SESSION['Name'];

/// Actualizar registros de la tabla entrega_factura_refactor
$sql = "UPDATE entregas SET Id_Factura = '$FolioFactura', Archivo = '$factura' WHERE Id_Salida = '$id_salida'";
$query = mysqli_query($conn, $sql);
if ($query) {
    echo "<br>Se actualizó correctamente la tabla entrega_factura_refactor";
    // Registrar en la tabla de bitácora la adición de una factura
    $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$id_salida', 'Se agregó la factura $FolioFactura', '$Fecha_Hoy', '$Responsable')";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se registró correctamente en la tabla de bitácora";
    } else {
        echo "<br>No se registró correctamente en la tabla de bitácora";
    }
} else {
    echo "<br>No se actualizó correctamente la tabla entrega_factura_refactor";
}


header("Location: ../../Front/detalles.php?id=".$id_salida);



?>