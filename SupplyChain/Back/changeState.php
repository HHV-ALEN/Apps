<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$id_salida = $_GET['id'];
$Estado = $_GET['estado'];
echo "-Id Salida: " . $id_salida;
echo "-Estado: " . $Estado;
$Fecha = date("Y-m-d H:i:s");

// Cuando el estado esta en Entrega y Surtido -> Empaque
if ($Estado == "Empaque") {
    echo "<br>Entro a Empaque</br>";
    $Sql_Update_Salida = "UPDATE salidas SET Estado = 'Empaque', Id_Status = 22
     WHERE Id = $id_salida";
    $Query_Update_Salida = mysqli_query($conn, $Sql_Update_Salida);
    if ($Query_Update_Salida) {
        echo "Se actualizo el estado de la salida a Empaque";
        /// Actualización de la Bitacora (Tabla: actualizaciones_bitacora_nueva)
        $Sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
        VALUES ($id_salida, 'Recepción para Empaque', '$Fecha', '$_SESSION[Name]')";
        $Query_Bitacora = mysqli_query($conn, $Sql_Bitacora);

        if ($Query_Bitacora) {
            $_SESSION['alerta_estado'] = "✅ La salida No. $id_salida ha sido actualizada a <strong style='color:green;'>🟢 Empaque</strong>.<br>📦 Estado actualizado correctamente y registrado en la bitácora.";
        } else {
            $_SESSION['alerta_estado'] = "⚠️ La salida fue actualizada, pero hubo un error al guardar la bitácora.";
        }
    } else {
        $_SESSION['alerta_estado'] = "❌ Hubo un error al actualizar el estado de la salida.";
    }

} elseif ($Estado == "Facturación") {
    echo "<br>Se actualizo el estado de la salida a Facturacion";
    $Sql_Update_Salida = "UPDATE salidas SET Estado = 'Facturación', Id_Status = 23
     WHERE Id = $id_salida";
    $Query_Update_Salida = mysqli_query($conn, $Sql_Update_Salida);
    if ($Query_Update_Salida) {
        echo "Se actualizo el estado de la salida a Facturacion";
        /// Actualización de la Bitacora (Tabla: actualizaciones_bitacora_nueva)
        $Sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
        VALUES ($id_salida, 'Recepción de Mercancia para Facturación', '$Fecha', '$_SESSION[Name]')";
        $Query_Bitacora = mysqli_query($conn, $Sql_Bitacora);

        if ($Query_Bitacora) {
            $_SESSION['alerta_estado'] = "✅ La salida No. $id_salida ha sido actualizada a <strong style='color:green;'>🟢 Facturación</strong>.<br>📦 Estado actualizado correctamente y registrado en la bitácora.";
        } else {
            $_SESSION['alerta_estado'] = "⚠️ La salida fue actualizada, pero hubo un error al guardar la bitácora.";
        }
    } else {
        $_SESSION['alerta_estado'] = "❌ Hubo un error al actualizar el estado de la salida.";
    }
} elseif ($Estado == "Logistica") {
    $Sql_Update_Salida = "UPDATE salidas SET Estado = 'Logistica', Id_Status = 24 WHERE Id = $id_salida";
    $Query_Update_Salida = mysqli_query($conn, $Sql_Update_Salida);

    if ($Query_Update_Salida) {
        $Sql_Bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable)
                         VALUES ($id_salida, 'Recepción de Mercancia para Logística', '$Fecha', '$_SESSION[Name]')";
        $Query_Bitacora = mysqli_query($conn, $Sql_Bitacora);

        if ($Query_Bitacora) {
            $_SESSION['alerta_estado'] = "✅ La salida No. $id_salida ha sido actualizada a <strong style='color:green;'>🟢 Logística</strong>.<br>📦 Estado actualizado correctamente y registrado en la bitácora.";
        } else {
            $_SESSION['alerta_estado'] = "⚠️ La salida fue actualizada, pero hubo un error al guardar la bitácora.";
        }
    } else {
        $_SESSION['alerta_estado'] = "❌ Hubo un error al actualizar el estado de la salida.";
    }

    // Redirigir al listado
    header("Location: ../index.php");
    exit();
}



header("Location: ../Front/detalles.php?id=".$id_salida);
?>