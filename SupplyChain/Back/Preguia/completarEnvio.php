<?php

require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$Responsable = $_SESSION['Name'];

$id_salida = $_GET["id_salida"];
$Tipo_Doc = $_POST["Tipo_Doc"];
$folio_guia = $_POST["folio_guia"];
$Costo = $_POST["Costo"];
$Fecha = date("Y-m-d");
echo "<br> ID Salida: ". $id_salida;
echo "<br> Tipo Documento: ". $Tipo_Doc;
echo "<br> Folio Guía: ". $folio_guia;
echo "<br> Costo: ". $Costo;


//Si es "Directo" UPDATE A la tabla doc_preguia "Costo_Directo" "Guia_Directo"

$sql_update_preguia = "UPDATE doc_preguia SET Guia_Directo = '$folio_guia', Costo_Directo = '$Costo' WHERE Id_Salida = $id_salida AND  Tipo_Doc = 'Directo'";

$result_update_preguia = mysqli_query($conn, $sql_update_preguia);

if ($result_update_preguia) {
    echo "<br>Actualización de doc_preguia exitosa";
    // Actualizar a Completado
    $sql_update_salida = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27' WHERE Id = $id_salida";
    $result_update_salida = mysqli_query($conn, $sql_update_salida);
    if ($result_update_salida) {
        echo "<br>Actualización de salida exitosa";
        /// Registrar en bitacora
        $sql_insert_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES 
        ('$id_salida', 'Se agregaron los Costos del envio', '$Fecha', '$Responsable')";
        $result_insert_bitacora = mysqli_query($conn, $sql_insert_bitacora);
        if ($result_insert_bitacora) {
            echo "<br>Bitacora registrada correctamente";
            // Registrar en la tabla Envios 
            $sql_insert_envios = "INSERT INTO envios (Id_Salida, Costo, Folio_Guia, Tipo, Fecha, Responsable) VALUES 
            
            ('$id_salida', '$Costo', '$folio_guia', '$Tipo_Doc', '$Fecha', '$Responsable')";
            $result_insert_envios = mysqli_query($conn, $sql_insert_envios);
            if ($result_insert_envios) {
                echo "<br>Envios registrados correctamente";
                // Redireccionar al dashboard
            header("Location: ../../index.php");
            } else {
                echo "<br>Error: ". $sql_insert_envios. "<br>". mysqli_error($conn);
            } 
        } else {
            echo "<br>Error: ". $sql_insert_bitacora. "<br>". mysqli_error($conn);
        }

    } else {
        echo "<br>Error: ". $sql_update_salida. "<br>". mysqli_error($conn);
    }
} else {
    echo "<br>Error: ". $sql_update_preguia. "<br>". mysqli_error($conn);
}

// Si es "Reembarque" UPDATE A la tabla doc_preguia  "Guia_Reembarque "Costo_Reembarque"
?>