<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
$Fecha_Hoy = date("Y-m-d H:i:s");

$Responsable = $_SESSION['Name'];
$Id_Salida = $_GET['Id_Salida'];
$Parametro = $_GET['Parametro'] ?? '';
$Id_Salida_Relacion = $_GET['Id_Salida_Relacion'] ?? '';



echo "<strong>- Id Salida (BASE): </strong>" . $Id_Salida;
echo "<br><strong>- Id Salida (FUSION): </strong>" . $Id_Salida_Relacion;
echo "<br><strong>- Parametro </strong>: " . $Parametro;

if ($Parametro == 'Base') {
    $Query_Remision_Update = "UPDATE entregas SET Id_Factura = '1', Archivo = 'REMISION' WHERE Id_Salida = '$Id_Salida'";
    $Query_Remision = mysqli_query($conn, $Query_Remision_Update);
    if ($Query_Remision) {
        echo "<br>Se actualizó correctamente la tabla entrega_factura_refactor";
        // Registrar en la tabla de bitácora la adición de una factura

        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se agregó la remisión', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
        } else {
            echo "<br>No se registró correctamente en la tabla de bitácora";
        }
    } else {
        echo "<br>No se actualizó correctamente la tabla entrega_factura_refactor";
    }
} elseif ($Parametro == 'Fusion'){
    $Query_Remision_Update = "UPDATE etiquetas_fusionadas SET Id_Factura = '1', Archivo = 'REMISION' WHERE Id_Relacion_Salida = '$Id_Salida_Relacion'";
    $Query_Remision = mysqli_query($conn, $Query_Remision_Update);
    if ($Query_Remision) {
        echo "<br>Se actualizó correctamente la tabla entrega_factura_refactor";
        // Registrar en la tabla de bitácora la adición de una factura

        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se agregó la remisión A la etiqueta fusionada: $Id_Salida_Relacion', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
        } else {
            echo "<br>No se registró correctamente en la tabla de bitácora";
        }
    } else {
        echo "<br>No se actualizó correctamente la tabla entrega_factura_refactor";
    }

} elseif ($Parametro == 'Consolidado'){
    echo "<br> Consolidado: $Id_Salida_Relacion";
    $Query_Remision_Update = "UPDATE consolidados SET Id_Factura = 1, Archivo = 'REMISION' WHERE Id = '$Id_Salida_Relacion'";
    $Query_Remision = mysqli_query($conn, $Query_Remision_Update);
    if ($Query_Remision) {
        echo "<br>Se actualizó correctamente la tabla entrega_factura_refactor";
        // Registrar en la tabla de bitácora la adición de una factura

        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se agregó la remisión A la etiqueta fusionada: $Id_Salida_Relacion', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
        } else {
            echo "<br>No se registró correctamente en la tabla de bitácora";
        }
    } else {
        echo "<br>No se actualizó correctamente la tabla entrega_factura_refactor";
    }
}

    /// Redireccionar a la pagina 
header("Location: ../../Front/detalles.php?id=$Id_Salida");
