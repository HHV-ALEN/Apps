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
/// Arreglos para cotejar el Id de la sucursal y estado

//Array ( [numero_salida] => 9 [id_cliente] => nuevo [nuevo_nombre] => nOMBRE cLIENTE [nuevo_clave] => SApCode [nuevo_rfc] => RFCCCCC [orden_venta] => 23 [folio_entrega] => 34 [partida1] => 1 [partida2] => 3 [prioridad] => nada [Comentarios] => )

$Estados = array(
  1 => "Entrega",
  2 => "Sin stock",
  3 => "Traspaso",
  4 => "Rechazado",
  5 => "Inactivo",
  6 => "Final de día/Entrega",
  9 => "Empaque",
  10 => "Final de día/Empaque",
  13 => "Facturación",
  14 => "No a pagado",
  15 => "Aplicacion de la factura",
  16 => "Credito excedido",
  17 => "No se puede facturar en parcialidades",
  18 => "Final de día/Facturacion",
  20 => "Cerrado",
  21 => "Entrega y Surtido",
  22 => "Empaque",
  23 => "Facturacion",
  24 => "Logistica",
  25 => "Ruta",
  26 => "Envios",
  30 => "Terminado",
  31 => "Primario Activo Comentario",
  33 => "Terminado Comentario",
  34 => "Urgente"
);

$Sucursales = array(
  1 => "Guadalajara",
  2 => "Veracruz",
  3 => "Tijuana",
  4 => "Texas",
  5 => "Miami",
  6 => "CDMX"
);

$Sucursal = $_SESSION['Sucursal'];
// Buscar el ID correspondiente en el arreglo
$Id_Sucursal = array_search($Sucursal, $Sucursales);

// Resultado:
echo "<br>Id_Sucursales:" . $Id_Sucursal; // Si $Sucursal es "Guadalajara", imprime: 1

$FechaHoy = date('Y-m-d H:i:s');
echo "<br> <strong>Fecha: </strong>" . $FechaHoy;
/// Información Recibida del formulario de nueva etiqueta
$Numero_Etiqueta = $_POST['numero_salida'];
$id_cliente = $_POST['id_cliente'];
$orden_venta = $_POST['orden_venta'];
$folio_entrega = $_POST['folio_entrega'];
$cliente = $_POST['id_cliente'];
$partida1 = $_POST['partida1'];
$partida2 = $_POST['partida2'];
$prioridad = $_POST['prioridad'];
$Comentario = $_POST['Comentarios'] ?? "";
$Cliente2 = $_POST['Cliente2'] ?? "";

$NuevoNombre = $_POST['nuevo_nombre'];
$NuevaClave = $_POST['nuevo_clave'];
$NuevaRFC = $_POST['nuevo_rfc'];

// Verificar cual fue el Id mas alto del atributo Id_Salida de la tabla entregas
$sql = "SELECT MAX(Id_Salida) AS max_id FROM entregas";
$query = mysqli_query($conn, $sql);
$result = mysqli_fetch_array($query);
$max_id = $result['max_id'];
$max_id += 1;

// Verificar cual fue el Id mas alto del Atributo Id_salida_consolidada
$sql = "SELECT MAX(Id) AS max_id_consolidado FROM consolidados";
$query = mysqli_query($conn, $sql);
$result = mysqli_fetch_array($query);
$max_id_consolidado = $result['max_id_consolidado'];
$max_id_consolidado += 1;

// Verificar cual fue el Id mas alto del Atributo Id de la tabla salidas
$sql = "SELECT MAX(Id) AS max_id_salida FROM salidas";
$query = mysqli_query($conn, $sql);
$result = mysqli_fetch_array($query);
$max_id_salida = $result['max_id_salida'];
$max_id_salida += 1;

$max_final = max(
  intval($max_id),
  intval($max_id_consolidado),
  intval($max_id_salida)
);

echo "<br><strong>Nuevo ID sugerido:</strong> " . $max_final;




// Verificar si el núm. de Etiqueta ya existe
$sql = "SELECT * FROM salidas WHERE Id = '$max_final'";
$query = mysqli_query($conn, $sql);
if (mysqli_num_rows($query) > 0) {
  echo "<h1>El número de etiqueta ya existe</h1>";
  $max_final += 1;
} else {
  echo "<h1>El número: $max_id de etiqueta no existe</h1>";
}

$Numero_Etiqueta = $max_final;
echo "<br>------------------------------- <strong>Información del Formulario</strong>";
echo "<br> ----> Numero de etiqueta: $max_final";
echo "<br> ----> Orden de venta: $orden_venta";
echo "<br> ----> Folio de entrega: $folio_entrega";
echo "<br> ----> Id_Cliente: $id_cliente";
echo "<br> ----> Partida: $partida1 - $partida2";
echo "<br> ----> Prioridad: $prioridad";
echo "<br> ----> Comentarios: $Comentario";
echo "<br> ----> Cliente 2: $Cliente2";
echo "<br>";


if ($id_cliente === 'nuevo') {
  // Registrar cliente y obtener el nuevo nombre y el Id
  echo "<br> <strong>Nuevo Nombre: </strong>" . $NuevoNombre;
  echo "<br> <strong>Nueva Clave: </strong>" . $NuevaClave;
  echo "<br> <strong>Nueva RFC: </strong>" . $NuevaRFC;

  // 1. Obtener el último Id existente
  $result_last_id = mysqli_query($conn, "SELECT MAX(Id) AS ultimo_id FROM clientes");
  $row_last_id = mysqli_fetch_assoc($result_last_id);
  $siguiente_id = $row_last_id['ultimo_id'] + 1;

  // 2. Registrar la base del nuevo cliente con el Id calculado
  $insert_cliente = "INSERT INTO clientes (Id, Nombre, RFC, Clave_Sap) 
                   VALUES ('$siguiente_id', '$NuevoNombre', '$NuevaRFC', '$NuevaClave')";
  $query_cliente = mysqli_query($conn, $insert_cliente);

  // 3. Verificar si se insertó correctamente
  if ($query_cliente) {
    echo "<br>✅ Nuevo cliente registrado con Id: $siguiente_id";
  } else {
    echo "<br>❌ Error en el registro del cliente: " . mysqli_error($conn);
  }
  // Obtener el último ID insertado
  $id_cliente = mysqli_insert_id($conn);
  $nombre_cliente = $NuevoNombre;
} else {

  /// Obtener Información del Cliente
  $sql_cliente = "SELECT * FROM clientes WHERE Id = $id_cliente";
  $query_cliente = mysqli_query($conn, $sql_cliente);
  $info_cliente = mysqli_fetch_array($query_cliente);
  $nombre_cliente = $info_cliente['Nombre'];
  $clave_sap = $info_cliente['Clave_Sap'];
  $RFC = $info_cliente['RFC'];

  echo "<br>-------------------------->  <strong>Información del cliente</strong>";
  echo "<p>Nombre del cliente: $nombre_cliente</p>";
  echo "<p>Clave SAP: $clave_sap</p>";
}


echo "<br>**************************************";
echo "<h1>Información de la sucursal</h1>";
echo "<p>Nombre de la sucursal: $Sucursal</p>";
echo "**************************************";

echo "<h1>Registros</h1>";


// Registrar Salida
$sql_salida = "INSERT INTO salidas (Id, Id_Cliente, Nombre_Cliente, Id_Status, Estado, Id_Sucursal, Sucursal, Urgencia)
VALUES ('$max_final', '$id_cliente','$nombre_cliente','21','Entrega','$Id_Sucursal','$Sucursal', '$prioridad');";
$query_salida = mysqli_query($conn, $sql_salida);
if ($query_salida) {
  echo "<br>-> Salida Registrada Correctamente<br>";

  $query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
  ('$Numero_Etiqueta', 'Registro de Etiqueta', '$FechaHoy', '" . $_SESSION['Name'] . "')";
  $result = mysqli_query($conn, $query);
  if ($result) {
    echo "-> Bitacora Registrada Correctamente<br>";
  } else {
    echo "<h1>Error al registrar la bitacora</h1>";
  }
} else {
  echo "<h1>Error al registrar la salida</h1>";
  echo "**************************************";
}

// Registrar Partida
//entrega_factura -> entrega_factura_regactor
$partidas = $partida1 . " - " . $partida2;
$sql_entrega_factura = "INSERT INTO entregas (Id_Salida, Id_Orden_Venta, Id_Entrega, Partida, Id_Factura, Archivo, 
Id_Cliente, Cliente_Nombre) 
VALUES ('$Numero_Etiqueta','$orden_venta','$folio_entrega','$partidas','0','0','$id_cliente','$nombre_cliente');";
$query_entrega_factura = mysqli_query($conn, $sql_entrega_factura);
if ($query_entrega_factura) {
  echo "-> Partida Registrada Correctamente<br>";
} else {
  echo "<h1>Error al registrar la partida</h1>";
}


// Registrar Contenido:
// Si comentario es vacio, no se registra
if ($Comentario != "") {
  $sql_contenido = "INSERT INTO comentarios (Id_Salida, Comentario, Fecha, Responsable)
  VALUES ('$Numero_Etiqueta','$Comentario','$FechaHoy','" . $_SESSION['Name'] . "');";
  $query_contenido = mysqli_query($conn, $sql_contenido);
  if ($query_contenido) {
    echo "-> Comentario Registrado Correctamente<br>";
  } else {
    echo "<h1>Error al registrar el comentario</h1>";
  }
}


// Una vez registrados todos los datos, se redirige a la página de los detalles de la salida registrada
/// http://www.alenturno.com/Vinculacion/detalles.php?id_salida=33887
//echo "<br> Id a enviar: " . $Numero_Etiqueta ;
header("Location: ../../Front/detalles.php?id=" . $Numero_Etiqueta);
