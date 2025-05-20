<?php 
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
//print_r($_POST);
$Fecha_Hoy = date("Y-m-d H:i:s");
$Responsable = $_SESSION['Name'];
$Id_Salida_Relacion = $_GET['Id_Salida_Relacion'] ?? '';
$Id_Salida = $_GET['Id_Salida'] ?? '';
$Parametro = $_GET['Parametro'] ?? '';

echo "<strong>Id Salida: </strong>" . $Id_Salida;
echo "<br> <strong>Id Salida Relacion: </strong>" . $Id_Salida_Relacion;
echo "<br> <strong>Parametro: </strong>" . $Parametro;

if($Parametro == 'Base'){
    $sql = "UPDATE entregas SET Id_Factura = 0, Archivo = '0' WHERE Id_Salida = '$Id_Salida' AND Id_Factura = '1'";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
        // Registrar en la bitacora
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la remisión', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
            // Regresar al Listado 
            header("Location: ../../Front/detalles.php?id=" . $Id_Salida);
        } else {
            echo "<br>No se pudo registrar en la tabla de bitácora";
        }
    } else {
        echo "<br>No se pudieron limpiar los campos Id_Factura y Archivo de la tabla entregas";
    }

} elseif ($Parametro == 'Fusion'){ 
    echo "<br> Fusion";
    $Query_Remision_Update = "UPDATE etiquetas_fusionadas SET Id_Factura = 0, Archivo= '0' WHERE Id_Relacion_Salida = '$Id_Salida_Relacion'";
    $Query_Remision = mysqli_query($conn, $Query_Remision_Update);
    if ($Query_Remision) {
        echo "<br>Se actualizó correctamente la tabla entrega_factura_refactor";
        // Registrar en la tabla de bitácora la adición de una factura
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se eliminó la remisión de la etiqueta fusionada: $Id_Salida_Relacion', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
             header("Location: ../../Front/detalles.php?id=" . $Id_Salida);
        } else {
            echo "<br>No se registró correctamente en la tabla de bitácora";
        }
    } else {
        echo "<br>No se actualizó correctamente la tabla entrega_factura_refactor";
    }
}elseif ($Parametro == 'Consolidado'){
    echo "<br> Consolidado: $Id_Salida_Relacion";
    $sql = "UPDATE consolidados SET Id_Factura = 0, Archivo = '0' WHERE Id = '$Id_Salida_Relacion' AND Id_Factura = '1'";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
        // Registrar en la bitacora
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la remisión de la salida consolidada: $Id_Salida_Relacion', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";
            // Regresar al Listado 
        } else {
            echo "<br>No se pudo registrar en la tabla de bitácora";
        }
    } else {
        echo "<br>No se pudieron limpiar los campos Id_Factura y Archivo de la tabla entregas";
    }
    
}

header("Location: ../../Front/detalles.php?id=" . $Id_Salida);

?>