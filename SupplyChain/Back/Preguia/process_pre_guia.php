<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

//print_r($_POST);

$firstname = $_SESSION['Name'];
$id_salida = $_POST['pedido_id'];
echo "<h1>Información general: </h1>";
echo "<br><strong> Nombre:</strong> " . $firstname;
echo "<br><strong>Información del Envio: </strong>";
print_r($_POST);
echo "<br><strong> ID Salida: </strong> " . $id_salida;
echo "<hr>";

$Tipo_Doc = $_POST['Tipo_Doc'];
$clienteNombre = $_POST['clienteNombre'];
$Paqueteria = $_POST['Paqueteria'] ?? "No Asignado";
$otroPaqueteria = $_POST['otroPaqueteria'] ?? "No Asignado";
$Chofer_Asignado = $_POST['Chofer_Asignado'];
$Tipo_Flete = $_POST['Tipo_Flete'] ?? "No Asignado";
$Metodo_Pago = $_POST['Metodo_Pago'] ?? "No Asignado";
$cliente_intermedio = $_POST['Cliente_Intermedio'] ?? "No Asignado";
$Fecha_Entregado = $_POST['fecha_entregado'] ?? "N/A";
$Fecha_Actual = date("Y-m-d H:i:s");

echo "<br><strong>Información de la preguía:</strong>";
echo "<br><strong> Id Seleccionado:</strong> " . $id_salida;
echo "<br><strong> Cliente:</strong> " . $clienteNombre;
echo "<br><strong> Tipo de Documento:</strong> " . $Tipo_Doc;
echo "<br><strong> Paqueteria: </strong>" . $Paqueteria;
echo "<br><strong> Chofer Asignado:</strong> " . $Chofer_Asignado;
echo "<br><strong> Tipo de Flete: </strong>" . $Tipo_Flete;
echo "<br><strong> Metodo de Pago: </strong>" . $Metodo_Pago;
echo "<br><strong> Cliente Intermedio: </strong>" . $cliente_intermedio;
echo "<br><strong> Fecha Actual: </strong>" . $Fecha_Actual;
echo "<br><strong> Fecha de Entregado: </strong>" . $Fecha_Entregado;

/// -> Cliente Pasa, Entregado por Vendedor, Proveedor Recolecta
$OtrasOpciones = [
    "Cliente Pasa",
    "Entregado por Vendedor",
    "Proveedor Recolecta"
];


// Si se envió información de dirección, actualizamos el cliente
if (!empty($_POST['Calle'])) {
    $nombre = $_POST['clienteNombre'];
    $calle = $_POST['Calle'];
    $colonia = $_POST['Colonia'];
    $ciudad = $_POST['Ciudad'];
    $estado = $_POST['Estado'];
    $cp = $_POST['CP'];

    echo "<hr><br><strong>Información de la dirección:</strong>";
    echo "<br><strong> Calle:</strong> " . $calle;
    echo "<br><strong> Colonia:</strong> " . $colonia;
    echo "<br><strong> Ciudad:</strong> " . $ciudad;
    echo "<br><strong> Estado:</strong> " . $estado;
    echo "<br><strong> CP:</strong> " . $cp;


    $query_update_cliente = "UPDATE clientes SET Calle = '$calle', Colonia = '$colonia', Ciudad = '$ciudad', Estado = '$estado', CP = '$cp' WHERE Nombre = '$nombre'";
    if ($conn->query($query_update_cliente) === TRUE) {
        echo "<br><strong>Actualización de cliente exitosa</strong><br>";
    } else {
        echo "<br><strong>Error al actualizar el cliente: </strong>" . $conn->error;
    }
}


/// Si $Chofer_Asignado esta en las opciones de $OtrasOpciones:
if (in_array($Chofer_Asignado, $OtrasOpciones)) {
    echo "<strong><br>El chofer asignado no es una de las opciones de Otras Opciones</strong>";
    /// 4.- Actualizar el estatus de la salida
    $updateSalida = "UPDATE salidas SET Estado = 'Envios', Id_Status = 26, Urgencia = 'Nada'  WHERE Id = '$id_salida'";
    if ($conn->query($updateSalida) === TRUE) {
        echo "<br><strong>Actualización de salida exitosa</strong>";
    } else {
        echo "<br><strong>Error al actualizar la salida: </strong>" . $conn->error;
    }

    // Registro en Preguia
    $insertPreGuia = "INSERT INTO preguia
    (Id_Salida, Chofer, Fecha, Fecha_Entregado) VALUES 
    ('$id_salida', '$Chofer_Asignado', '$Fecha_Actual', '$Fecha_Entregado')";
    if ($conn->query($insertPreGuia) === TRUE) {
        echo "<br><strong>Registro de preguía exitoso</strong>";
    } else {
        echo "<br><strong>Error al registrar la preguía: </strong>" . $conn->error;
    }

    $insertBitacora = "INSERT INTO bitacora (Id_Salida, Responsable, Fecha, Accion)
    VALUES ('$id_salida', '$firstname', 'Registro de preguía ( $Chofer_Asignado )', '$Fecha_Actual')";
    if ($conn->query($insertBitacora) === TRUE) {
        echo "<br><strong>Registro de bitacora exitoso</strong>";
    } else {
        echo "<br><strong>Error al registrar la bitacora: </strong>" . $conn->error;
    }


} else {
    echo "<strong>El chofer asignado no es una de las opciones de Otras Opciones</strong>";
    /// 4.- Actualizar el estatus de la salida
    $updateSalida = "UPDATE salidas SET Estado = 'A Ruta', Id_Status = 25  WHERE Id = '$id_salida'";
    if ($conn->query($updateSalida) === TRUE) {
        echo "<br><strong>Actualización de salida exitosa</strong>";
    } else {
        echo "<br><strong>Error al actualizar la salida: </strong>" . $conn->error;
    }

    /// ---------------------------------------------------------------------------------------- 

    /// 1.- Registro de Preguia 
    $insertPreGuia = "INSERT INTO preguia 
    (Id_Salida, Cliente, Cliente_Intermedio, Paqueteria, Chofer, Tipo_Flete, Metodo_Pago, Tipo_Doc, Fecha, Fecha_Entregado)
    VALUES 
    ('$id_salida', '$clienteNombre', '$cliente_intermedio', '$Paqueteria', '$Chofer_Asignado', '$Tipo_Flete', '$Metodo_Pago', '$Tipo_Doc', '$Fecha_Actual', '$Fecha_Entregado')";

    if ($conn->query($insertPreGuia) === TRUE) {
        echo "<br><strong>Registro de preguía exitoso</strong>";
    } else {
        echo "<br><strong>Error al registrar la preguía: </strong>" . $conn->error;
    }
    /// 2.- rEGISTRO DE Doc_preguia
    $ultimo_Id_preguia = $conn->insert_id;
    $insertDocPreGuia = "INSERT INTO doc_preguia (Tipo_Doc, Id_Preguia, Id_Salida, Responsable, Fecha)
    VALUES ('$Tipo_Doc', '$ultimo_Id_preguia', '$id_salida', '$firstname', '$Fecha_Actual')";
    if ($conn->query($insertDocPreGuia) === TRUE) {
        echo "<br><strong>Registro de doc_preguia exitoso</strong>";
    } else {
        echo "<br><strong>Error al registrar la doc_preguia: </strong>" . $conn->error;
    }

    /// 3.- registro en bitacora
    $insertBitacora = "INSERT INTO bitacora (Id_Salida, Responsable, Fecha, Accion)
    VALUES ('$id_salida', '$firstname', '$Fecha_Actual', 'Registro de preguía')";
    if ($conn->query($insertBitacora) === TRUE) {
        echo "<br><strong>Registro de bitacora exitoso</strong>";
    } else {
        echo "<br><strong>Error al registrar la bitacora: </strong>" . $conn->error;
    }

    echo "<br> ------------------------------------------------------------ <br>";
}


echo "<br>Id Salida: " . $id_salida;

header('Location: ../../Front/detalles.php?id=' . $id_salida . '');