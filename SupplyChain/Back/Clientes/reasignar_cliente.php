<?php

require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

$Id_Salida = $_POST['Id_Salida'];
$Nuevo_Responsable = $_POST['personal_admin'];
$NombreCliente = $_POST['NombreCliente'];

echo "<h1> Información del Formulario</h1>";
echo "<br><string>- Id Salida: </string>" . $Id_Salida;
echo "<br><string>- Nuevo Respondable: </string>" . $Nuevo_Responsable;
echo "<br><string>- Nombre del Cliente: </string>" . $NombreCliente;

/// Actualizar encargado:

$update_query = "UPDATE clientes_asignados SET encargado = '$Nuevo_Responsable' WHERE Nombre_Cliente LIKE '%$NombreCliente%';";

if ($conn->query($update_query) === TRUE) {
    echo "<br> <strong>Nombre del encargado actualizado correctamente en la tabla clientes_asignados</strong>";
    /// Insertar registro con la respuesta y detalles
} else {
    echo "<br> <strong>Error al actualizar el nombre del encargado en la tabla clientes_asignados: </strong>" . $conn->error;
}

header ("Location: ../../listadoAsignado.php"); // Redirigir a la página de proveedores

?>