<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
//print_r($_POST);
$Fecha_Hoy = date("Y-m-d H:i:s");
$Responsable = $_SESSION['Name'];
$Id_Salida = $_GET['Id_Salida'];
$Id_Factura = $_GET['Id_Factura'] ?? '';
$Archivo = $_GET['Archivo'] ?? '';
$Parametro = $_GET['Parametro'] ?? '';
$Id_Salida_consolidado = $_GET['Id_Salida_consolidado'] ?? '';
$Id_Salida_Relacion = $_GET['Id_Salida_Relacion'] ?? '';

$dir = '../Files/Facturas/';

echo "<strong>Id Salida: </strong>" . $Id_Salida;
echo "<br> <strong>Id Factura: </strong>" . $Id_Factura;
echo "<br> <strong>Archivo: </strong>" . $Archivo;
echo "<br> <strong>Parametro: </strong>" . $Parametro;
/// Limpiar los Campos "Id_Factura" y "Archivo" de la tabla entregas

if ($Parametro == 'Base') {
    $sql = "UPDATE entregas SET Id_Factura = 0, Archivo = '0' WHERE Id_Salida = '$Id_Salida' AND Id_Factura = '$Id_Factura'";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
        // Registrar en la bitacora
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la factura $Id_Factura', '$Fecha_Hoy', '$Responsable')";
        $query = mysqli_query($conn, $sql);
        if ($query) {
            echo "<br>Se registró correctamente en la tabla de bitácora";


            if (unlink($dir . $Archivo)) {
                echo "<br>El archivo se eliminó correctamente";
            } else {
                echo "<br>No se pudo eliminar el archivo";
            }
            // Regresar al Listado 
            header("Location: ../../Front/detalles.php?id=" . $Id_Salida);
        } else {
            echo "<br>No se pudo registrar en la tabla de bitácora";
        }
    } else {
        echo "<br>No se pudieron limpiar los campos Id_Factura y Archivo de la tabla entregas";
    }
}elseif ($Parametro == 'Consolidado'){
    echo "<br> Consolidado: $Id_Salida_consolidado";
    $sql = "UPDATE consolidados SET Id_Factura = 0, Archivo = '0' WHERE Id = '$Id_Salida_consolidado' AND Id_Factura = '$Id_Factura'";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
        // Registrar en la bitacora
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la factura $Id_Factura de la salida consolidada: $Id_Salida_consolidado', '$Fecha_Hoy', '$Responsable')";
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
    echo "<br> Fusion: $Id_Salida_Relacion";
    $sql = "UPDATE etiquetas_fusionadas SET Id_Factura = 0, Archivo = '0' WHERE Id_Relacion_Salida = '$Id_Salida_Relacion' AND Id_Factura = '$Id_Factura'";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        echo "<br>Se limpiaron correctamente los campos Id_Factura y Archivo de la tabla entregas";
        // Registrar en la bitacora
        $sql = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
    VALUES ('$Id_Salida', 'Se Elimino la factura $Id_Factura de la salida fusionada: $Id_Salida_consolidado', '$Fecha_Hoy', '$Responsable')";
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
}
else {
    echo "<br> No se pudo limpiar los campos Id_Factura y Archivo de la tabla entregas";
}
