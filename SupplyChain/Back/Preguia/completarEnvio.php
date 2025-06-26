<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$Responsable = $_SESSION['Name'];

$id_salida = $_GET["id_salida"];
$Tipo_Doc = $_POST["Tipo_Doc"];
$folio_guia = $_POST["folio_guia"] ?? "N/A";
$Costo = $_POST["Costo"] ?? 0;
$Fecha = date("Y-m-d H:i:s");
$Fecha_Guia = $_POST["Fecha_Guia"];
echo "<br> ID Salida: " . $id_salida;
echo "<br> Tipo Documento: " . $Tipo_Doc;
echo "<br> Folio Guía: " . $folio_guia;
echo "<br> Costo: " . $Costo;
echo "<br> Fecha: " . $Fecha_Guia;
//Si es "Directo" UPDATE A la tabla doc_preguia "Costo_Directo" "Guia_Directo"

if ($Tipo_Doc == "Directo") {
    $sql_update_preguia = "UPDATE doc_preguia SET Guia_Directo = '$folio_guia', Costo_Directo = '$Costo', Fecha_Final = '$Fecha_Guia' WHERE Id_Salida = $id_salida AND  Tipo_Doc = 'Directo'";

    $result_update_preguia = mysqli_query($conn, $sql_update_preguia);

    if ($result_update_preguia) {
        echo "<br>Actualización de doc_preguia exitosa";
        // Actualizar a Completado
        $sql_update_salida = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27', Urgencia = 'Nada' WHERE Id = $id_salida";
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
                    echo "<br>Error: " . $sql_insert_envios . "<br>" . mysqli_error($conn);
                }
            } else {
                echo "<br>Error: " . $sql_insert_bitacora . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "<br>Error: " . $sql_update_salida . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "<br>Error: " . $sql_update_preguia . "<br>" . mysqli_error($conn);
    }
} elseif ($Tipo_Doc == "Reembarque"){
    // Insertar en doc_preguia
    $sql_insert_docpreguia = "INSERT INTO doc_preguia (Id_Salida, Tipo_Doc, Folio_Doc, Id_Preguia, Responsable, Fecha, Costo_Directo)
    VALUES ('$id_salida', 'Reembarque', '$folio_guia', '$id_salida', '$Responsable', '$Fecha_Guia', '$Costo')";
    $result_insert_docpreguia = mysqli_query($conn, $sql_insert_docpreguia);
    if ($result_insert_docpreguia) {
        echo "<br>Registro en doc_preguia exitoso";
    } else {
        echo "<br>Error: " . $sql_insert_docpreguia . "<br>" . mysqli_error($conn);
    }
    // Actualizar bitacora
    $sql_insert_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES 
    ('$id_salida', 'Se agregaron los Costos del envio [1]', '$Fecha', '$Responsable')";
    $result_insert_bitacora = mysqli_query($conn, $sql_insert_bitacora);
    if ($result_insert_bitacora) {
        echo "<br>Bitacora registrada correctamente";
        header("Location: ../../index.php");
    } else {
        echo "<br>Error: " . $sql_insert_bitacora . "<br>" . mysqli_error($conn);
    }


}


elseif ($Tipo_Doc == "Ruta"){
    // Actualiza preguia
    // Inserta en Envios

    $Envios_sql = "INSERT INTO envios (Id_Salida, Costo, Folio_Guia, Tipo, Fecha, Responsable) VALUES 
    ('$id_salida', 0, 'N/A', 'Ruta', '$Fecha_Guia', '$Responsable')";
    $Envios_result = mysqli_query($conn, $Envios_sql);
    if ($Envios_result) {
        echo "<br>Envios registrados correctamente";
    } else {
        echo "<br>Error: " . $Envios_sql . "<br>" . mysqli_error($conn);
    }
    // Actualiza la Salida

    $sql_update_salida = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27', Urgencia = 'Nada' WHERE Id = $id_salida";
    $result_update_salida = mysqli_query($conn, $sql_update_salida);

    if ($result_update_salida) {
        echo "<br>Actualización de salida exitosa";
        /// Registrar en bitacora
        $sql_insert_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES 
        ('$id_salida', 'Se agregaron los Costos del envio', '$Fecha', '$Responsable')";
        $result_insert_bitacora = mysqli_query($conn, $sql_insert_bitacora);
        if ($result_insert_bitacora) {
            echo "<br>Bitacora registrada correctamente";
            // Redireccionar al dashboard
            header("Location: ../../index.php");
        } else {
            echo "<br>Error: " . $sql_insert_bitacora . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "<br>Error: " . $sql_update_salida . "<br>" . mysqli_error($conn);
    }
    // Actualiza La bitacora
}

// Si es "Reembarque" UPDATE A la tabla doc_preguia  "Guia_Reembarque "Costo_Reembarque"
