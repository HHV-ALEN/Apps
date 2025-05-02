<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($conn, $_POST['query']);

    $query = "SELECT Id, Nombre, Clave_Sap FROM clientes 
              WHERE Nombre LIKE '%$search%' OR Clave_Sap LIKE '%$search%' 
              ORDER BY Nombre LIMIT 10";
    
    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['Id'],
            'nombre' => "{$row['Nombre']} ({$row['Clave_Sap']})"
        ];
    }
    echo json_encode($data);
}
?>
