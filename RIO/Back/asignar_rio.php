<?php
include "../../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];

$id_empleado = $_POST['id_empleado'];
$anio = $_POST['anio'];
$mes = $_POST['mes'];

print_r($_FILES['archivo']);
echo "<br>";
echo "<br> - Archivo [Name]: " . $_FILES['archivo']['name'];

echo "<h1><strong>Información del Formulario: </strong></h1>";
echo "<br><strong>-> Id Empleado: </strong>" . $id_empleado;
echo "<br><strong>-> Año Seleccionado: </strong>" . $anio;
echo "<br><strong>-> Mes Selección: </strong>" . $mes;
echo "<br><strong>-> Información del Archivo: </strong><br>";
print_r($_FILES['archivo']);

/// Consultar Información del Jefe

$Query_info_user = "SELECT * FROM usuarios WHERE Id = $id_empleado";
$resultado = mysqli_query($conn, $Query_info_user);

if (mysqli_num_rows($resultado) > 0) {
    $row = mysqli_fetch_assoc($resultado);  // Changed $result to $resultado
    $Nombre_Usuario = $row['Nombre'];
    $Nombre_Gerente = $row['Jerarquia'];
}

echo "<br> Nombre: " . $Nombre_Usuario;
echo "<br> Gerente: " . $Nombre_Gerente;

/// Query asignacion_rio 
//$asignacion_query = "INSERT INTO asignacion_rio (Id_Empleado, Empleado, Id_Jefe, Archivo, Año, Mes, Fecha_asignacion, Status)
//VALUES ('$id_empleado', '')"
