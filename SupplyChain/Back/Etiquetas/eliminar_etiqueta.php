<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
// Mostrar todos los errores:

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conectar a la base de datos
$conn = connectMySQLi();
session_start();
print_r($_POST);
$id_folioBase = $_GET['id'];
$id_Salida = $_GET['id_salida'];
$Tipo = $_GET['Tipo'];
$id_fusionada = $_GET['idfusionada'];

echo "<br> Id Folio Base: " . $id_Salida;
echo "<br> <strong>Tipo de Etiqueta: </strong>" . $Tipo;
echo "<br> Id_ Fusionada: " . $id_fusionada;

if ($Tipo == 'Base') {
    /// Eliminar Registro en la tabla entregas 
    $Delete_Entrega = "DELETE FROM entregas WHERE Id = $id_folioBase";
    $query_eliminar = mysqli_query($conn, $Delete_Entrega);
    if ($query_eliminar) {
        echo "<br> Registro Base Eliminado";
        header("location: ../../Front/detalles.php?id=$id_Salida");
    } else {
        echo "<br> Registro Base NOOOOO Eliminado";
    }
} elseif ($Tipo == 'Fusionada'){

    $Delete = "DELETE FROM etiquetas_fusionadas 
               WHERE Id_Relacion_Salida = $id_fusionada 
                 AND Salida_Base        = $id_Salida";

    if (mysqli_query($conn, $Delete)) {

        // 2️⃣  Guardar texto para el mensaje
        $_SESSION['alerta_estado'] = "✅ Etiqueta fusionada eliminada correctamente.";

    } else {
        $_SESSION['alerta_estado'] = "❌ No se pudo eliminar la etiqueta fusionada.";
    }

    // 3️⃣  Redirige y detén el script
    header("Location: ../../Front/detalles.php?id=$id_Salida");
    exit;
} elseif($Tipo == 'Consolidada'){
    echo "<br> Etiqueta Fusionada";
    echo "<br> Id Salida Consolidada: " . $id_fusionada;
    echo "<br> Id Base: " . $id_Salida;

    $Delete_Consolidado = "DELETE FROM consolidados
    WHERE Id = $id_fusionada 
    AND Id_Base = $id_Salida";

    if (mysqli_query($conn, $Delete_Consolidado)) {
        echo "<br> eLIMINADO cORRECTAMENTE";
        // 2️⃣  Guardar texto para el mensaje
        $_SESSION['alerta_estado'] = "✅ Etiqueta fusionada eliminada correctamente.";

    } else {
        $_SESSION['alerta_estado'] = "❌ No se pudo eliminar la etiqueta fusionada.";
        echo "<br> No se ha eliminado";
    }

    // 3️⃣  Redirige y detén el script
    header("Location: ../../Front/detalles.php?id=$id_Salida");
    exit;
}
