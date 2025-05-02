<?php 
include "../../../Back/config/config.php";
session_start();

if (isset($_SESSION['alerta_estado'])) {
    echo "<div id='alertaBanner' class='alerta fade-in'>
            {$_SESSION['alerta_estado']}
          </div>";
    unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

//print_r($_SESSION);
$conn = connectMySQLi();


$id_cliente = $_POST['id_cliente'];
$nombre = $_POST['nombre'];
$clave_sap = $_POST['clave_sap'];
$rfc = $_POST['rfc'];
$calle = $_POST['calle'];
$colonia = $_POST['colonia'];
$ciudad = $_POST['ciudad'];
$estado = $_POST['estado'];
$codigo_postal = $_POST['codigo_postal'];
$telefono = $_POST['telefono'];
echo "<br><strong>Datos recibidos:</strong><br>";
echo "<br><strong>- Id Cliente:</strong> $id_cliente";
echo "<br><strong>- Nombre:</strong> $nombre";
echo "<br><strong>- Clave SAP:</strong> $clave_sap";
echo "<br><strong>- RFC:</strong> $rfc";
echo "<br><strong>- Calle:</strong> $calle";
echo "<br><strong>- Colonia:</strong> $colonia";
echo "<br><strong>- Ciudad:</strong> $ciudad";
echo "<br><strong>- Estado:</strong> $estado";
echo "<br><strong>- Código Postal:</strong> $codigo_postal";
echo "<br><strong>- Teléfono:</strong> $telefono";

/// Actualizar cliente en la base de datos
$query_update  = "UPDATE clientes SET Nombre = '$nombre', RFC = '$rfc', Clave_Sap = '$clave_sap', 
Calle = '$calle', Colonia = '$colonia', Ciudad = '$ciudad', Estado = '$estado', 
Cp = '$codigo_postal', Telefono = '$telefono' WHERE Id_Original = $id_cliente";

$resultado = $conn->query($query_update);

if ($resultado) {
    $_SESSION['alerta_estado'] = "Cliente actualizado correctamente.";
    $_SESSION['alerta_tipo'] = "success"; // Tipo de alerta (success, error, etc.)
} else {
    $_SESSION['alerta_estado'] = "Error al actualizar el cliente: " . $conn->error;
    $_SESSION['alerta_tipo'] = "error"; // Tipo de alerta (success, error, etc.)
}

header ("Location: ../../Proveedor.php"); // Redirigir a la página de proveedores



?>