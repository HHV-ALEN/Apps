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


$Nombre = $_POST['nombre'];
$clave_sap = $_POST['Clave_Sap'];
$RFC = $_POST['rfc'];
$Calle = $_POST['calle'];
$Colonia = $_POST['Colonia'];
$Ciudad = $_POST['ciudad'];
$Estado = $_POST['estado'];
$Codigo_Postal = $_POST['codigo_postal'];
$Telefono = $_POST['telefono'];
echo "<br><strong>Información del formulario:</strong>";
echo "<br><strong>Nombre:</strong> " . htmlspecialchars($Nombre);
echo "<br><strong>Clave SAP:</strong> " . htmlspecialchars($clave_sap);
echo "<br><strong>RFC:</strong> " . htmlspecialchars($RFC);
echo "<br><strong>Calle:</strong> " . htmlspecialchars($Calle);
echo "<br><strong>Colonia:</strong> " . htmlspecialchars($Colonia);
echo "<br><strong>Ciudad:</strong> " . htmlspecialchars($Ciudad);
echo "<br><strong>Estado:</strong> " . htmlspecialchars($Estado);
echo "<br><strong>Código Postal:</strong> " . htmlspecialchars($Codigo_Postal);
echo "<br><strong>Teléfono:</strong> " . htmlspecialchars($Telefono);

// Insertar datos en la tabla clientes:
/// Atributos de la tabla: Nombre, RFC, Clave_Sap, Calle, Colonia, Ciudad, Estado, Cp, Telefono
$sql = "INSERT INTO clientes (Nombre, RFC, Clave_Sap, Calle, Colonia, Ciudad, Estado, Cp, Telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $Nombre, $RFC, $clave_sap, $Calle, $Colonia, $Ciudad, $Estado, $Codigo_Postal, $Telefono);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    $_SESSION['alerta_estado'] = "Cliente registrado correctamente.";
    $_SESSION['alerta_tipo'] = "success"; // Tipo de alerta (puede ser success, error, etc.)
} else {
    $_SESSION['alerta_estado'] = "Error al registrar el cliente: " . $stmt->error;
    $_SESSION['alerta_tipo'] = "error"; // Tipo de alerta (puede ser success, error, etc.)
}


$stmt->close();
header ("Location: ../../Proveedor.php"); // Redirigir a la página de proveedores
?>