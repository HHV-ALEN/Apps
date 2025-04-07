<?php 
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

$firstname = $_SESSION['Name'];
echo "<br> Nombre: " . $firstname;
echo "<hr>";
echo "<strong>Información del Envio: </strong>";
print_r($_POST);
$id_salida = $_POST['pedido_id'];
echo "<br> ID Salida: " . $id_salida;

$Tipo_Doc = $_POST['Tipo_Doc'];
$clienteNombre = $_POST['clienteNombre'];
$Paqueteria = $_POST['Paqueteria'];
$otroPaqueteria = $_POST['otroPaqueteria'] ?? "No Asignado";
$Chofer_Asignado = $_POST['Chofer_Asignado'];
$Tipo_Flete = $_POST['Tipo_Flete'];
$Metodo_Pago = $_POST['Metodo_Pago'];
$cliente_intermedio = $_POST['Cliente_Intermedio'] ?? "No Asignado";
$Fecha_Actual = date("Y-m-d H:i:s");

echo "<br><strong>Información de la preguía:</strong>";
echo "<br> Id seleccionado: " . $id_salida;
echo "<br> Cliente: " . $clienteNombre;
echo "<br> Tipo de Documento: " . $Tipo_Doc;
echo "<br> Paqueteria: " . $Paqueteria;
echo "<br> Chofer Asignado: " . $Chofer_Asignado;
echo "<br> Tipo de Flete: " . $Tipo_Flete;
echo "<br> Metodo de Pago: " . $Metodo_Pago;
echo "<br> Cliente Intermedio: " . $cliente_intermedio;
echo "<br> Fecha Actual: " . $Fecha_Actual;


/// ----------------------------------------- Registros en Base de datos --------------------------------

// Registro en la tabla: Preguia
$sql_preguia = "INSERT INTO preguia (Id_Salida, Cliente, Cliente_Intermedio, Paqueteria, Chofer, Tipo_Flete,
 Metodo_Pago, Tipo_Doc, Fecha) 
 VALUES ('$id_salida', '$clienteNombre', '$cliente_intermedio', '$Paqueteria', '$Chofer_Asignado', '$Tipo_Flete', '$Metodo_Pago', '$Tipo_Doc', NOW());";
$query_preguia = mysqli_query($conn, $sql_preguia);
if ($query_preguia) {
    echo "<br>Preguia registrada correctamente";
} else {
    echo "<br>Error al registrar la preguía";
}

// Obtener el Id creado de la tabla preguia_refactor
$Id_Preguia = mysqli_insert_id($conn);
echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

/// Registro en la tabla : bitacora
$sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
 ('$id_salida', 'Registro de Preguia', '$Fecha_Actual', '$firstname');";
$query_bitacora = mysqli_query($conn, $sql_bitacora);
if ($query_bitacora) {
    echo "<br>Bitacora registrada correctamente";
} else {
    echo "<br>Bitacora No registrada correctamente";
}

// Actualizar Estado de la slaida 
$sql_A_RUTA = "UPDATE salidas SET Estado = 'A Ruta', Id_Status = '25' WHERE Id = '$id_salida'";
$result_A_RUTA = mysqli_query($conn, $sql_A_RUTA);
if ($result_A_RUTA) {
    echo "<br>Actualizacion de estado de salida exitoso";
} else {
    echo "<br>Error: " . $sql_A_RUTA . "<br>" . mysqli_error($con);
}


/// Registro en tabla: documentos_preguia
$sql_documentos_preguia = "INSERT INTO doc_preguia 
(Tipo_Doc, Id_Preguia, Id_Salida, Responsable, Fecha)
 VALUES ('$Tipo_Doc', '$Id_Preguia', '$id_salida', '$firstname', '$Fecha_Actual');";
$result_documentos_preguia = mysqli_query($conn, $sql_documentos_preguia);
if ($result_documentos_preguia) {
    echo "<br>Registro en tabla: documentos_preguia exitoso";
} else {
    echo "<br> Registro incompleto exit";
}

header("Location: ../../Front/detalles.php?id=".$id_salida);



?>