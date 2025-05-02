<?php
include "../../../Back/config/config.php";
$conn = connectMySQLi();
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
ob_start();


if (isset($_POST['data'])) {
    foreach ($_POST['data'] as $fila) {


        // Skip si alguna fila esta vacia o si detecta que es la fila de cabeceras
        if (empty($fila['A']) || empty($fila['B']) || empty($fila['C']) || empty($fila['J'])) {
            continue; // Skip this row
        } elseif ($fila['A'] == 'O.C' || $fila['B'] == 'Nombre de cliente/proveedor') {
            continue;
        }
        /// Columnas : A - O.C | B - Nombre Cliente/Proveedor | C.- No. De Articulo
        /// Columnas : D - Item | E - Descripcion | F - Cod. Almacen | G - Cantidad abierta restante
        /// H - Precio | I - Importe Pendiente | JJ - Fecha OC 

        $OrdenCompra = $conn->real_escape_string($fila['A']);
        $NombreCliente = $conn->real_escape_string($fila['B']);
        $Articulo = $conn->real_escape_string($fila['C']);
        $Fecha = $conn->real_escape_string($fila['J']);

        echo "<br><strong>Orden De Compra [A]: </strong>" . $OrdenCompra; // 
        echo "<br><strong>Nombre Cliente [B]: </strong>" . $NombreCliente;
        echo "<br><strong>No. de Articulo [C]: </strong>" . $Articulo;
        echo "<br><strong>Fecha [J]: </strong>" . $Fecha;

        echo "<br>";

        /* Insertar en tabla compras
        Id, OrdenCompra, NombreCliente, No. Articulo, CodigoItem, Estado */
        $sql = "INSERT INTO supply_compras (OrdenCompra, NombreCliente, CodigoItem, Fecha, Estado) 
                VALUES ('$OrdenCompra', '$NombreCliente', '$Articulo', '$Fecha', 'Registrado')";
        $result = $conn->query($sql);

        if ($result) {
            echo 'Registro en estado [Exitoso]!';
        } else {

            echo 'Registro [Denegado!]';
        } 
            
    }
    
}
header ('Location: ../../Front/compras.php');
exit();
?>