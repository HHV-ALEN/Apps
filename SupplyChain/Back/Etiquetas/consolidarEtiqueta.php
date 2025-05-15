<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
print_r($_SESSION);
echo "<hr>";

$Id_Salida_Base = $_GET['id_salida'];
$Nombre = $_SESSION['Name'];
$fecha = date("Y-m-d H:i:s");
$Salida = $_POST['Salida_Destino'];
$Destino = $_POST['Destino'];

echo "<br> Información del formulario ";
echo "<br><strong>Id_Salida_Base</strong> " . $Id_Salida_Base;
echo "<br><strong>Nombre</strong> " . $Nombre;
echo "<br><strong>Fecha</strong> " . $fecha;
echo "<br><strong>Salida</strong> " . $Salida;
echo "<br><strong>Destino</strong> " . $Destino;

echo "<br>--------------------------------------------------------------- <br>";


$sql = "SELECT * FROM entregas WHERE Id_Salida = $Salida";
$query = mysqli_query($conn, $sql);
// Podrian ser multiples folios
while ($row = mysqli_fetch_array($query)) {
    $Id_Entrega = $row['Id_Entrega'];
    $Id_Cliente_Entrega = $row['Id_Cliente'];
    $Cliente_Nombre_Entrega = $row['Cliente_Nombre'];

    $Sql_Consolidado = "INSERT INTO consolidados (Id_Base, Id_salida_consolidada,  Destino, Id_Cliente, Nombre_Cliente, Id_Entrega, Estado, Fecha) 
    VALUES ('$Id_Salida_Base', '$Salida', '$Destino', '$Id_Cliente_Entrega', '$Cliente_Nombre_Entrega', '$Id_Entrega', 'Pendiente', '$fecha')";

    $query_Consolidado = mysqli_query($conn, $Sql_Consolidado);
    if ($query_Consolidado) {
        echo "<br><strong> Registro en tabla consolidados para el Id_Entrega</strong> " . $Id_Entrega . " <strong>Exitoso</strong>";
        // Registrar proceso de Consolidación dentro de la tabla de Bitacora
        $Id_Salida_Base = mysqli_real_escape_string($conn, $Id_Salida_Base);
        $Salida = mysqli_real_escape_string($conn, $Salida);
        $Nombre = mysqli_real_escape_string($conn, $Nombre);

        // Now build the query with proper concatenation
        $sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) 
        VALUES ('$Id_Salida_Base', 'Consolidación de la Salida $Salida', '$fecha', '$Nombre')";
        $query_Bitacora = mysqli_query($conn, $sql_Bitacora);
        if ($query_Bitacora) {
            echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Exitoso</strong>";
        } else {
            echo "<br><strong> Registro en tabla Bitacora para el Id_Salida</strong> " . $Id_Salida_Base . " <strong>Fallido</strong>";
        }
    } else {
        echo "<br><strong> Registro en tabla consolidados para el Id_Entrega</strong> " . $Id_Entrega . " <strong>Fallido</strong>";
    }
    echo "<br>--------------------------------------------------------------- <br>";
}

header("Location: ../../Front/detalles.php?id=" . $Id_Salida_Base);
