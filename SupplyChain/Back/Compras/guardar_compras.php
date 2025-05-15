<?php
include "../../../Back/config/config.php";
$conn = connectMySQLi();
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
ob_start();

//print_r($_POST['data']);
echo "<br><br>";


if (isset($_POST['data'])) {
    foreach ($_POST['data'] as $fila) {

       // Array ( [A] => O.C [B] => Nombre de cliente/proveedor [C] => Número de artículo [D] => Código Item [E] => Descripción artículo/serv. [G] => Cantidad abierta restante [H] => Precio [I] => Importe pendiente [J] => [K] => ) [5] => Array ( [A] => 58758 [B] => 3M MEXICO .S.A DE C.V. [C] => 105-ITCSN110048 [D] => 80610229637 [E] => TUBO TERMOCONTRACTIL PARED GRUESA CAL 2-4/0 [G] => 5 [H] => 43.26 [I] => 4425.5 [J] => 2025-02-25 [K] => 2025-04-11 )

        // Skip si alguna fila esta vacia o si detecta que es la fila de cabeceras
        if (empty($fila['A']) || empty($fila['B']) || empty($fila['C']) || empty($fila['J'])) {
            continue; // Skip this row
        } elseif ($fila['A'] == 'ORDEN DE VENTA' || $fila['B'] == 'O.C') {
            continue;
        }

        /* Columnas : A - O.C 
        | B - Nombre Cliente/Proveedor 
        | C.- No. De Articulo
        | D - Item 
        | E - Descripcion 
        | G - Cantidad abierta restante
        | H - Precio 
        | I - Importe Pendiente 
        | J - Fecha OC
        | 
        | P - Titular
        | Q - fecha
        | R - STATUS

        $columnasDeseadas = ['A', 'B', 'C', 'D', 'F', 'H', 'I', 'J', 'K', 'L', 'Q', 'R'];

         [5] => Array ( [A] => 1 [B] => 58758 [C] => 3M MEXICO .S.A DE C.V. [D] => 105-ITCSN110048 [F] => TUBO TERMOCONTRACTIL PARED GRUESA CAL 2-4/0 
         [H] => 5 [I] => 43.26 [J] => 4425.5 [K] => 2025-02-25 [L] => 2025-04-11 [Q] => Berenice Salazar [R] => cerrada 
    */

        $OrdenVenta = $conn->real_escape_string($fila['A']);
        $OrdenCompra = $conn->real_escape_string($fila['B']);
        $NombreCliente = $conn->real_escape_string($fila['C']);
        $NoArticulo = $conn->real_escape_string($fila['D']);
        $Descripcion = $conn->real_escape_string($fila['F']);
        $CantidadAbiertaRestante =  $conn->real_escape_string($fila['H']);
        $Precio = $conn->real_escape_string($fila['I']);
        $ImportePendiente = $conn->real_escape_string($fila['J']);
        $FechaEntregaCliente= $conn->real_escape_string($fila['L']);
        $Titular = $conn->real_escape_string($fila['Q']);
        $Fecha_Titular = $conn->real_escape_string($fila['R']);

        $Fecha_Hoy = date("Y-m-d");

        echo "<br><strong> Orden de Venta: </strong>" . $OrdenVenta;
        echo "<br><strong> Orden de compra: </strong>" . $OrdenCompra;
        echo "<br><strong> Nombre del cliente: </strong>" . $NombreCliente;
        echo "<br><strong> Número de Articulo: </strong>" . $NoArticulo;
        echo "<br><strong> Descripción: </strong>" . $Descripcion;
        echo "<br><strong> Cantidad Abierta restante: </strong>" . $CantidadAbiertaRestante;
        echo "<br><strong> Precio: </strong>" . $Precio;
        echo "<br><strong> Importe Pendiente: </strong>" . $ImportePendiente;
        echo "<br><strong> Fecha entrega al cliente: </strong>" .  $FechaEntregaCliente;
        echo "<br><strong> Titular : </strong>" . $Titular;
        echo "<br><strong> Fecha (Titular ) </strong>" . $Fecha_Titular;
        echo "<br><br>";

        /* Insertar en tabla compras
        Id, OrdenCompra, NombreCliente, No. Articulo, CodigoItem, Estado */
        
        $sql = "INSERT INTO supply_compras (OrdenCompra, NombreCliente, FechaEntregaCliente,
        NoDeArticulo, Descripcion, CantidadAbiertaRestante, Precio, ImportePendiente, Titular, Fecha_Titular,
        FechaDeRegistro, Estado, OrdenVenta) VALUES 
        ('$OrdenCompra', '$NombreCliente', '$FechaEntregaCliente',
        '$NoArticulo', '$Descripcion', '$CantidadAbiertaRestante', '$Precio', '$ImportePendiente', '$Titular',
        '$Fecha_Titular', '$Fecha_Hoy', 'Registrado', '$OrdenVenta')";

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