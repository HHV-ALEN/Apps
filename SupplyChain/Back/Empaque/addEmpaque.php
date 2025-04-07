<?php
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();
print_r($_POST);

$firstname = $_SESSION['Name'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los arrays de los inputs
    $contenedores = $_POST['id_contenedor'];  // Array con los tipos de contenedor
    $cantidades = $_POST['Cantidad_contenedores'];  // Array con las cantidades
    $otro_contenedor = $_POST['otro_contenedor'] ?? '';
    $Id_Salida = $_POST['Id_Salida'];
    $Cliente = $_POST['Cliente'];
    $Comentario = $_POST['Comentario'] ?? '';
    $Fecha_Hoy = date("Y-m-d H:i:s");

    echo "<h1>Información del Formulario</h1>";
    echo "ID Salida: " . $Id_Salida . "<br>";
    echo "Cliente: " . $Cliente . "<br>";
    echo "Comentario: " . $Comentario . "<br>";
    print_r($otro_contenedor);
    echo "<hr>";
    // si en
    // Recorrer los arrays y registrar cada contenedor
    for ($i = 0; $i < count($contenedores); $i++) {
        $contenedor = $contenedores[$i];
        $cantidad = $cantidades[$i];
        if ($contenedor == 'Otro') {
            $contenedor = $otro_contenedor[$i];
        }

        //echo "<br>" . $cantidad . " " . $contenedor . "<br>";
        // Insertar en la base de datos
        $Insert_Contenedor = "INSERT INTO contenido 
            (Id_Salida, Contenedor, Cantidad) VALUES ('$Id_Salida', '$contenedor', '$cantidad')";
        $Query_Contenedor = mysqli_query($conn, $Insert_Contenedor);
        if ($Query_Contenedor) {
            echo "<br>Se Insertaron " . $cantidad . " De " . $contenedor . "";
        } else {
            echo "Error al registrar el contenedor";
        }
    }

    header("Location: ../../Front/detalles.php?id=".$Id_Salida );
}
?>