<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();
require '../../vendor/autoload.php';
/// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$Id_Salida = $_GET['Id_Salida'];
//echo "<br><strong>Folio:</strong> $Id_Salida<br>";

$Fecha_Actual = date("Y-m-d");
$suma_total_contenedores = 0;

// Consultar la tabla salidas donde Id = $Id_Salida
$sql = "SELECT * FROM salidas WHERE Id = $Id_Salida";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $Id_Cliente = $row['Id_Cliente'];
    $Nombre_Cliente = $row['Nombre_Cliente'];
    $Id_Sucursal = $row['Id_Sucursal'];
    $Sucursal = $row['Sucursal'];
} else {
    //echo "Error: " . mysqli_error($conn);
}
/*
echo "<br><strong>Detalles de la tabla Salida: </strong>";
echo "<br><strong>Id_Cliente:</strong> $Id_Cliente";
echo "<br><strong>Nombre_Cliente:</strong> $Nombre_Cliente";
echo "<br><strong>Id_Sucursal:</strong> $Id_Sucursal";
echo "<br><strong>Sucursal:</strong> $Sucursal";
*/
/// Obtener Información de la tablal clientes
/// Calle, Colonia, Ciudad, Estado, Cp, Telefono 
$sql = "SELECT * FROM clientes WHERE Id = $Id_Cliente";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $Calle = $row['Calle'];
    $Colonia = $row['Colonia'];
    $Ciudad = $row['Ciudad'];
    $Estado = $row['Estado'];
    $Cp = $row['Cp'];
    $Telefono = $row['Telefono'];
} else {
    echo "Error: " . mysqli_error($conn);
}
/*
echo "<br><br><strong>Detalles de la tabla Clientes: </strong>";
echo "<br><strong>Calle:</strong> $Calle";
echo "<br><strong>Colonia:</strong> $Colonia";
echo "<br><strong>Ciudad:</strong> $Ciudad";
echo "<br><strong>Estado:</strong> $Estado";
echo "<br><strong>Cp:</strong> $Cp";
echo "<br><strong>Telefono:</strong> $Telefono";
*/
// Obtener Información de la talba contenido
// Cantidad, Contenedor
$SQL_contenido = "SELECT * FROM contenido WHERE Id_Salida = $Id_Salida";
$result = mysqli_query($conn, $SQL_contenido);
if ($result) {
    $Arreglo_de_ContenidoEmpaque = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $Cantidad = $row['Cantidad'];
        $Contenedor = $row['Contenedor'];
        $suma_total_contenedores += $Cantidad;
        // Guardar en un arreglo
        $Arreglo_de_ContenidoEmpaque[] = array(
            'Cantidad' => $Cantidad,
            'Contenedor' => $Contenedor
        );
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

/*
echo "<br><br><strong>Detalles de la tabla Contenido: </strong>";
echo "<br><strong>Cantidad:</strong> $Cantidad";
echo "<br><strong>Contenedor:</strong> $Contenedor";
echo "<br><strong>Total de Contenedores:</strong> $suma_total_contenedores";
*/

//echo "<hr>";
//print_r($Arreglo_de_ContenidoEmpaque);

// Obtener Información de la tabla preguia
//Paqueteria, Tipo_Flete, Metodo_Pago, Tipo_Doc 
$sql = "SELECT * FROM preguia WHERE Id_Salida = $Id_Salida";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $Paqueteria = $row['Paqueteria'];
    $Tipo_Flete = $row['Tipo_Flete'];
    $Metodo_Pago = $row['Metodo_Pago'];
    $Tipo_Doc = $row['Tipo_Doc'];
} else {
    echo "Error: " . mysqli_error($conn);
}
/*
echo "<br><br><strong>Detalles de la tabla PreGuia: </strong>";
echo "<br><strong>Paqueteria:</strong> $Paqueteria";
echo "<br><strong>Tipo_Flete:</strong> $Tipo_Flete";
echo "<br><strong>Metodo_Pago:</strong> $Metodo_Pago";
echo "<br><strong>Tipo_Doc:</strong> $Tipo_Doc";
*/

//print_r($Arreglo_de_ContenidoEmpaque);

require_once('../../fpdf/fpdf.php');
require_once('../../vendor/setasign/fpdi/src/autoload.php');

use setasign\Fpdi\Fpdi;

$pdf = new Fpdi();

/// Crear x veces la etiqueta, dependiendo de la cantidad de contenedores
for ($i = 1; $i <= $suma_total_contenedores; $i++) {
    $pdf->AddPage();
    $pdf->setSourceFile("plantilla-detalles.pdf");
    $template = $pdf->importPage(1);
    $pdf->useTemplate($template, 0, 0, 220);

    // Definir fuente
    $pdf->SetFont('Arial', 'B', 16);

    // Número de Folio
    $pdf->SetXY(45, 50); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Id_Salida, 0, 1);

    // Sucursal
    $pdf->SetXY(140, 50); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Sucursal, 0, 1);

    // Fecha
    $pdf->SetXY(140, 60); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Fecha_Actual, 0, 1);

    /// Calle
    $pdf->SetXY(22, 79);
    $pdf->Cell(0, 10, "Calle: " . $Calle, 0, 1);

    /// Colonia
    $pdf->SetXY(22, 92);
    $pdf->Cell(0, 10, "Colonia: " . $Colonia, 0, 1);

    /// Ciudad
    $pdf->SetXY(22, 105);
    $pdf->Cell(0, 10, "Ciudad: " . $Ciudad, 0, 1);

    /// Estado
    $pdf->SetXY(22, 117);
    $pdf->Cell(0, 10, "Estado: " . $Estado, 0, 1);

    /// CP
    $pdf->SetXY(22, 130);
    $pdf->Cell(0, 10, "C.P.: " . $Cp, 0, 1);

    /// Tipo de Flete
    $pdf->SetXY(155, 150); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Tipo_Flete, 0, 1);


    /// Paqueteria 
    $pdf->SetXY(22, 160); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Paqueteria, 0, 1);

    /// Iterar el arreglo de Contenido y dependiendo del contenedor, colocar la cantidad en una zona especifica

    foreach ($Arreglo_de_ContenidoEmpaque as $key => $value) {
        $Contenedor = $value['Contenedor'];
        $Cantidad = $value['Cantidad'];
        //echo "<br><strong>Contenedor:</strong> $Contenedor <strong>Cantidad:</strong> $Cantidad";
        if ($Contenedor == "Cajas") {
            // Caja
            $pdf->SetXY(85, 180); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        } elseif ($Contenedor == "Carrete") {
            // Sobre
            $pdf->SetXY(165, 182); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        } elseif ($Contenedor == "Rollo") {
            // Pallet
            $pdf->SetXY(85, 190); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        } elseif ($Contenedor == "Tarima") {
            // Tarima
            $pdf->SetXY(85, 205); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        } elseif ($Contenedor == "Paquete") {
            // Paquete
            $pdf->SetXY(85, 215); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        } else {
            // Otro [NOMBRE]
            $pdf->SetXY(122, 192); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Contenedor, 0, 1);

            // Otro [CANTIDAD]
            $pdf->SetXY(165, 192); // (x, y) ajusta según el diseño
            $pdf->Cell(0, 10, $Cantidad, 0, 1);
        }
    }

    /// Total de Contenedores:
    $pdf->SetXY(50, 235); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $suma_total_contenedores, 0, 1);

    // Número de Paquete
    $pdf->SetXY(120, 235); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $i, 0, 1);

    /*
    for ($y = 10; $y <= 200; $y += 10) {
        $pdf->Text(5, $y, "Y: $y");
        $pdf->Line(15, $y, 190, $y);
    }
    
    // Crear líneas verticales
    for ($x = 10; $x <= 190; $x += 10) {
        $pdf->Text($x, 5, "X: $x");
        $pdf->Line($x, 10, $x, 200);
    }
*/
}

$pdf->Output('I', 'etiquetas.pdf'); // 'I' muestra en navegador. También puedes usar 'D' para descarga directa.
