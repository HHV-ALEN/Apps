<?php
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

/// -> Cliente Pasa, Entregado por Vendedor, Proveedor Recolecta
$OtrasOpciones = [
    "Cliente Pasa",
    "Entregado por Vendedor",
    "Proveedor Recolecta"
];

// 1.- Directo --> Chofer
// Tabla -> Preguia: Id | Id_Salida | Cliente | Cliente_Intermedio | Paqueteria | Chofer | Tipo_Flete | Metodo_Pago | Tipo_Doc | Fecha 
if ($Tipo_Doc == 'Directo') {

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

    /// Si la variable de "Chofer_Asignado" es una de las opciones de $OtrasOpciones entonces pasara Directo a Envios
    if (in_array($Chofer_Asignado, $OtrasOpciones)) {
        echo "<br> una de las opciones: " . $Chofer_Asignado;

        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        /// Registro en la tabla : bitacora
        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia', '$Fecha_Actual', '$firstname');";
        $query_bitacora = mysqli_query($conn, $sql_bitacora);
        if ($query_bitacora) {
            echo "<br>Bitacora registrada correctamente";
        } else {
            echo "<br>Bitacora No registrada correctamente";
        }

        // Actualizar Estado de la slaida 
        $sql_A_RUTA = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27' WHERE Id = '$id_salida'";
        $result_A_RUTA = mysqli_query($conn, $sql_A_RUTA);
        if ($result_A_RUTA) {
            echo "<br>Actualizacion de estado de salida exitoso";
        } else {
            echo "<br>Error: " . $sql_A_RUTA . "<br>" . mysqli_error($con);
        }
    } /// Si no es una opción, y se selecciono un Chofer:
    else {
        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        /// Registro en la tabla : bitacora
        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia para el chofer $Chofer_Asignado', '$Fecha_Actual', '$firstname');";
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
    }
} elseif ($Tipo_Doc == 'Reembarque') {
    // Insertar en preguia
    $sql_preguia = "INSERT INTO preguia (Id_Salida, Cliente, Cliente_Intermedio, Paqueteria, Chofer, Tipo_Flete,
     Metodo_Pago, Tipo_Doc, Fecha) 
     VALUES ('$id_salida', '$clienteNombre', '$cliente_intermedio', '$Paqueteria', '$Chofer_Asignado', '$Tipo_Flete', '$Metodo_Pago', '$Tipo_Doc', NOW());";
    $query_preguia = mysqli_query($conn, $sql_preguia);
    if ($query_preguia) {
        echo "<br>Preguia registrada correctamente";
    } else {
        echo "<br>Error al registrar la preguía";
    }

    /// Si la variable de "Chofer_Asignado" es una de las opciones de $OtrasOpciones entonces pasara Directo a Envios

    if (in_array($Chofer_Asignado, $OtrasOpciones)) {
        echo "<br> una de las opciones: " . $Chofer_Asignado;

        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        /// Registro en la tabla : bitacora
        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia', '$Fecha_Actual', '$firstname');";
        $query_bitacora = mysqli_query($conn, $sql_bitacora);
        if ($query_bitacora) {
            echo "<br>Bitacora registrada correctamente";
        } else {
            echo "<br>Bitacora No registrada correctamente";
        }

        // Actualizar Estado de la slaida 
        $sql_A_RUTA = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27' WHERE Id = '$id_salida'";
        $result_A_RUTA = mysqli_query($conn, $sql_A_RUTA);
        if ($result_A_RUTA) {
            echo "<br>Actualizacion de estado de salida exitoso";
        } else {
            echo "<br>Error: " . $sql_A_RUTA . "<br>" . mysqli_error($con);
        }
    }else{
        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        /// Registro en la tabla : bitacora
        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia para el chofer $Chofer_Asignado', '$Fecha_Actual', '$firstname');";
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

        
    }
} elseif ($Tipo_Doc == 'Ruta') {

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

    if (in_array($Chofer_Asignado, $OtrasOpciones)) {
        echo "<br>Opciones extras: " . $Chofer_Asignado;
        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        //registro en la tabla : bitacora

        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia', '$Fecha_Actual', '$firstname');";
        $query_bitacora = mysqli_query($conn, $sql_bitacora);

        // Actualizar Estado de la slaida 
        $sql_A_RUTA = "UPDATE salidas SET Estado = 'Completado', Id_Status = '27' WHERE Id = '$id_salida'";
        $result_A_RUTA = mysqli_query($conn, $sql_A_RUTA);
        if ($result_A_RUTA) {
            echo "<br>Actualizacion de estado de salida exitoso";
        } else {
            echo "<br>Error: " . $sql_A_RUTA . "<br>" . mysqli_error($con);
        }
    } else {
        // Registro Normal
        // Obtener el Id creado de la tabla preguia_refactor
        $Id_Preguia = mysqli_insert_id($conn);
        echo "<br>Id de la tabla preguia_refactor: " . $Id_Preguia;

        //registro en la tabla : bitacora
        $sql_bitacora = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
         ('$id_salida', 'Registro de Preguia No. $Id_Preguia para el chofer $Chofer_Asignado', '$Fecha_Actual', '$firstname');";
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
    }
}

header("Location: ../../Front/detalles.php?id=" . $id_salida);

// -------------------------------------------

// 1.- DIRECTO
// 1.1.- Registro de Información del Formulario
// 1.2.- Asignación del Chofer
// 1.3.- Actualizacion de estado A envios
// | Continuación del Proceso -> Desde la vista de Detalles registrar el Costo, Folio y Feca


// 2.- Reembarque
// 2.1.- Registro de Información del Formulario
// 2.2.- Asignación del Chofer
// 2.3.- Actualizacion de estado A envios
// | Continuación del Proceso :
// | 1era Revisión: Guia de Envio, Costo, Fecha 
// | ----> 2da Revisión: Guia de Reembarque, Costo, Fecha  [ NO Se Completa el proceso hasta la segunda Revisión]

// 3.- Ruta
// 3.1.- Registro de Información del Formulario
// 3.2.- Asignación del Chofer
// 3.3.- Proceso del Chofer
// | Continuación del Proceso : Revisión para Aceptación 

/// Cuando en ves del Chofer Se asigna una opcion de las de $OtrasOpciones 
// El estado de la salida Cambia a Completado



/*

if (in_array($Chofer_Asignado, $OtrasOpciones)) {
    echo "<br> una de las opciones: " . $Chofer_Asignado;

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
    $sql_A_RUTA = "UPDATE salidas SET Estado = 'Envíos', Id_Status = '26' WHERE Id = '$id_salida'";
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
} else {
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
}

header("Location: ../../Front/detalles.php?id=".$id_salida);

/// ----------------------------------------- Registros en Base de datos --------------------------------

/* Registro en la tabla: Preguia
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

*/
