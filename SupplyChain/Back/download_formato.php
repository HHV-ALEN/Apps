<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

/// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


$Id_Salida = $_GET['Id_Salida'];
$Tipo_Doc = $_GET['Tipo_Doc'];
// Obtener fecha de hoy
$date = date('Y-m-d');
/* 
Datos a conseguir:
- Folio (Id_Salida) - Celda H2 | Celda h24
- Tipo de Documento - Celda K4 | Celda k26
- Fecha - Celda L2  | Celda L24
----- Destinatario
- Ciudad De Destino - Celda J7 | Celda J29
- Destinatario - Celda J8 | Celda J30
- Domicilio - Celda J9 | Celda J31
- Colonia - Celda J10 | Celda J32
- Telefono - Celda J11 | Celda J33
- CP - Celda J12 | Celda J34
- RFC - Celda J13 | Celda J35
- Fletera - Celda J14 | Celda J36
---------------
- Contiene ********* De donde se obtiene?
- Flete - Celda D14 | Celda D36
- Método de Pago - Celda D15 | Celda D37
- Empaque : B18 - Cantidad : C18 | 
- Empaque : B19 - Cantidad : C19 |
- Empaque : B20 - Cantidad : C20 |
---------------------------------
- Empaque : B40 - Cantidad : C40 |
- Empaque : B41 - Cantidad : C41 |
- Empaque : B42 - Cantidad : C42 |
---------------------------------




echo "- - - - - Información Para el Documento de Pre Guia - - - - -";
echo "<br>";
echo "<br><strong>- Id Salida :</strong> " . $Id_Salida;
echo "<br><strong>- Tipo de Documento :</strong> " . $Tipo_Doc;
echo "<hr>";
   */
/// Consulta a la tabla salida_refactor
$sql = "SELECT * FROM salidas WHERE Id = $Id_Salida";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($query);
$Id_Cliente = $row['Id_Cliente'];

/// consulta a la tabla preguia_refactor
$sql = "SELECT * FROM preguia WHERE Id_Salida = $Id_Salida";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($query);
$Tipo_Flete = $row['Tipo_Flete'];
$Paqueteria = $row['Paqueteria'];
$Metodo_Pago = $row['Metodo_Pago'];

/// Consulta a la tabla "cliente"
$sql = "SELECT * FROM clientes WHERE Id = $Id_Cliente";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($query);
$nombre = $row['Nombre'];
$rfc = $row['RFC'];
$calle = $row['Calle'] ?? '';
$colonia = $row['Colonia'] ?? '';
$cd_destino = $row['Ciudad'] ?? '';
$estado_destino = $row['Estado'];
$cp = $row['Cp'];
$telefono = $row['Telefono'];



// Consulta a la tabla contenido_refactor
$Arreglo_de_ContenidoEmpaque = array();
$sql = "SELECT * FROM contenido WHERE Id_Salida = $Id_Salida";
$query = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($query)) {
    $Contenedor = $row['Contenedor'];
    $Cantidad = $row['Cantidad'];

    $Arreglo_de_ContenidoEmpaque[] = [
        'Contenedor' => $Contenedor,
        'Cantidad' => $Cantidad
    ];
}

/// Obtener Información del contenido de salidas fusionadas y/o consolidadas

// 1.- Obtener los Folios de las salidas Fusionadas
$Arreglo_de_salidas_fusionadas = array();
$sql_fusion = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = $Id_Salida";
$query_fusion = mysqli_query($conn, $sql_fusion);

while ($row_fusion = mysqli_fetch_array($query_fusion)) {
    $Id_Relacion_Salida = $row_fusion['Id_Relacion_Salida'];
    $Arreglo_de_salidas_fusionadas[] = $Id_Relacion_Salida;
}

//print_r($Arreglo_de_salidas_fusionadas);
// Consultar la tabla de contenidos con las salidas_fusionadas
foreach ($Arreglo_de_salidas_fusionadas as $salida) {
    $sql_contenidos = "SELECT * FROM contenido WHERE Id_Salida = $salida";
    $query_contenidos = mysqli_query($conn, $sql_contenidos);
    while ($row_contenidos = mysqli_fetch_array($query_contenidos)) {
        $Contenedor = $row_contenidos['Contenedor'];
        $Cantidad = $row_contenidos['Cantidad'];
        $Arreglo_de_ContenidoEmpaque[] = [
            'Contenedor' => $Contenedor,
            'Cantidad' => $Cantidad
        ];
    }
}

//print_r($Arreglo_de_ContenidoEmpaque);

//echo "<hr>";
$totals = [];
/// Empaques: Caja, Paquete, Rollo, Carrete, Tarima, Otro 

$Caja_Total = 0;
$Paquete_Total = 0;
$result_total = 0;
$Carrete_Total = 0;
$Tarima_Total = 0;
$Otro_Total = 0;
$Rollo_Total = 0;

$Nombre_Contenedor = "N/A";
//echo "<br><strong>Empaques:</strong> ";
foreach ($Arreglo_de_ContenidoEmpaque as $item) {
    $type = $item['Contenedor'];
    $quantity = $item['Cantidad'];

    //echo "<br>Item:" . $type;
    //echo "<br>Cantidad:" . $quantity;

    // Update the corresponding variable
    switch ($type) {
        case 'Caja':
            $Caja_Total += $quantity;
            //echo "<br>Total Caja: " . $Caja_Total;
            break;
        case 'Rollo':
            $Rollo_Total += $quantity;
            //echo "<br>Total Rollo: " . $Rollo_Total;
            break;
        case 'Paquete':
            $Paquete_Total += $quantity;
            //echo "<br>Total Paquete: " . $Paquete_Total;
            break;
        case 'Carrete':
            $Carrete_Total += $quantity;
            //echo "<br>Total Carrete: " . $Carrete_Total;
            break;
        case 'Tarima':
            $Tarima_Total += $quantity;
            //echo "<br>Total Tarima: " . $Tarima_Total;
            break;
        default:
            $Otro_Total += $quantity;
            $Nombre_Contenedor = $type;
            echo "<br>Total Otro: " . $Otro_Total;
    }

    //echo "<br>-------------------------------";
}

// Calculate grand total (if needed)
$result_total = $Caja_Total + $Paquete_Total + $Carrete_Total + $Tarima_Total + $Otro_Total;



/*
echo "<br><strong>- Información Del Cliente :</strong> " . $Id_Cliente;
echo "<br><strong>- Nombre :</strong> " . $nombre;
echo "<br><strong>- RFC :</strong> " . $rfc;
echo "<br><strong>- Tipo_Flete :</strong> " . $Tipo_Flete;
echo "<br><strong>- Paqueteria :</strong> " . $Paqueteria;
echo "<br><strong>- Metodo de Pago :</strong> " . $Metodo_Pago;
echo "<br>---- Contenido del Envio: --------<br>";
foreach ($Arreglo_deContenidos as $contenido) {
    echo "<br><strong>- Contenedor :</strong> " . $contenido['Contenedor'];
    echo "<br><strong>- Cantidad :</strong> " . $contenido['Cantidad'];
}
echo "<hr>";

echo "<br><strong>- Ciudad de Destino :</strong> " . $cd_destino;
echo "<br><strong>- Domicilio :</strong> " . $calle;
echo "<br><strong>- Colonia :</strong> " . $colonia;
echo "<br><strong>- Ciudad Destino</strong> " . $cd_destino;
echo "<br><strong>- Estado Destino :</strong> " . $estado_destino;
echo "<br><strong>- Telefono :</strong> " . $telefono;
echo "<br><strong>- CP :</strong> " . $cp;
echo "<hr>";
*/
////// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

// Información del Remitente
$Remitente_Ciudad = "Zapopan, Jalisco";
$Remitente_Nombre = "ALEN INTELLIGENT SA DE CV";
$Remitente_Direccion = "Calz. de las Palmas 45";
$Remitente_Colonia = "Ciudad Granga";
$Remitente_Telefono = "33 3627 5332";
$Remitente_Rfc = "EIA 960415 DN0";


require_once('../../fpdf/fpdf.php');
require_once('../../vendor/setasign/fpdi/src/autoload.php');


use setasign\Fpdi\Fpdi;

$pdf = new Fpdi();

// Configuración para la edición del PDF
$pdf->AddPage();
$pdf->setSourceFile("Plantilla-envio.pdf");
$template = $pdf->importPage(1);
$pdf->useTemplate($template, 0, 0, 220);
$pdf->SetFont('Arial', 'B', 12);  // Definir fuente

////  Inserción de INformación --------------------------------

// Número de Folio
$pdf->SetXY(135, 15); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, $Id_Salida, 0, 1);

$pdf->SetXY(135, 150); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, $Id_Salida, 0, 1);

// Fecha 
$pdf->SetXY(172, 150);
$pdf->Cell(0, 10, $date, 0, 1);

$pdf->SetXY(172, 15);
$pdf->Cell(0, 10, $date, 0, 1);

// Tipo_Doc 
$pdf->SetXY(171, 25);
$pdf->Cell(0, 10, $Tipo_Doc, 0, 1);

$pdf->SetXY(170, 160);
$pdf->Cell(0, 10, $Tipo_Doc, 0, 1);

/// --- Información del Remitente
$pdf->SetFont('Arial', '', 10);  // Definir fuente
// Ciudad
$pdf->SetXY(50, 42);
$pdf->Cell(0, 10, $Remitente_Ciudad, 0, 1);

$pdf->SetXY(50, 178);
$pdf->Cell(0, 10, $Remitente_Ciudad, 0, 1);
// Nombre
$pdf->SetXY(50, 49);
$pdf->Cell(0, 10, $Remitente_Nombre, 0, 1);

$pdf->SetXY(50, 186);
$pdf->Cell(0, 10, $Remitente_Nombre, 0, 1);
// Dirección
$pdf->SetXY(50, 56);
$pdf->Cell(0, 10, $Remitente_Direccion, 0, 1);

$pdf->SetXY(50, 192);
$pdf->Cell(0, 10, $Remitente_Direccion, 0, 1);
// Colonia
$pdf->SetXY(50, 63);
$pdf->Cell(0, 10, $Remitente_Colonia, 0, 1);
$pdf->SetXY(50, 200);
$pdf->Cell(0, 10, $Remitente_Colonia, 0, 1);

// Telefono
$pdf->SetXY(50, 70);
$pdf->Cell(0, 10, $Remitente_Telefono, 0, 1);
$pdf->SetXY(50, 207);
$pdf->Cell(0, 10, $Remitente_Telefono, 0, 1);
// RFC
$pdf->SetXY(50, 77);
$pdf->Cell(0, 10, $Remitente_Rfc, 0, 1);
$pdf->SetXY(50, 213);
$pdf->Cell(0, 10, $Remitente_Rfc, 0, 1);

/// --- Información del Remitente
// Ciudad Destino
$pdf->SetXY(145, 42);
$pdf->Cell(0, 10, $cd_destino, 0, 1);
$pdf->SetXY(145, 179);
$pdf->Cell(0, 10, $cd_destino, 0, 1);
// Destinatario - nombre
$pdf->SetXY(145, 49);
$pdf->Cell(0, 10, $nombre, 0, 1);
$pdf->SetXY(145, 185);
$pdf->Cell(0, 10, $nombre, 0, 1);
// Dirección Destino
$pdf->SetXY(145, 56);
$pdf->Cell(0, 10, $calle, 0, 1);
$pdf->SetXY(145, 192);
$pdf->Cell(0, 10, $calle, 0, 1);
// Colonia Destino
$pdf->SetXY(145, 63);
$pdf->Cell(0, 10, $colonia, 0, 1);
$pdf->SetXY(145, 199);
$pdf->Cell(0, 10, $colonia, 0, 1);
// Télefono
$pdf->SetXY(145, 70);
$pdf->Cell(0, 10, $telefono, 0, 1);
$pdf->SetXY(145, 205);
$pdf->Cell(0, 10, $telefono, 0, 1);
// Código Postal
$pdf->SetXY(145, 77);
$pdf->Cell(0, 10, $cp, 0, 1);
$pdf->SetXY(145, 213);
$pdf->Cell(0, 10, $cp, 0, 1);
// RFC
$pdf->SetXY(145, 84);
$pdf->Cell(0, 10, $rfc, 0, 1);
$pdf->SetXY(145, 220);
$pdf->Cell(0, 10, $rfc, 0, 1);
// Fletera 
$pdf->SetXY(145, 91);
$pdf->Cell(0, 10, $Paqueteria, 0, 1);
$pdf->SetXY(145, 226);
$pdf->Cell(0, 10, $Paqueteria, 0, 1);


/// --------------------
// Flete Tipo_Flete
$pdf->SetXY(50, 224);
$pdf->Cell(0, 10, $Tipo_Flete, 0, 1);
$pdf->SetXY(50, 88);
$pdf->Cell(0, 10, $Tipo_Flete, 0, 1);
// Método de Pago
$pdf->SetXY(50, 94);
$pdf->Cell(0, 10, $Metodo_Pago, 0, 1);
$pdf->SetXY(50, 230);
$pdf->Cell(0, 10, $Metodo_Pago, 0, 1);

/// --------------------- Continuación de Rows: Info de los Contenedores ---------------------

$pdf->SetXY(41, 112);
$pdf->Cell(0, 10, "$Caja_Total", 0, 1);
$pdf->SetXY(41, 247);
$pdf->Cell(0, 10, "$Caja_Total", 0, 1);

$pdf->SetXY(41, 118);
$pdf->Cell(0, 10, "$Paquete_Total", 0, 1);
$pdf->SetXY(41, 253);
$pdf->Cell(0, 10, "$Paquete_Total", 0, 1);

$pdf->SetXY(41, 124);
$pdf->Cell(0, 10, "$Rollo_Total", 0, 1);
$pdf->SetXY(41, 259);
$pdf->Cell(0, 10, "$Rollo_Total", 0, 1);

$pdf->SetXY(80, 112);
$pdf->Cell(0, 10, "$Carrete_Total", 0, 1);
$pdf->SetXY(80, 247);
$pdf->Cell(0, 10, "$Carrete_Total", 0, 1);

$pdf->SetXY(80, 118);
$pdf->Cell(0, 10, "$Tarima_Total", 0, 1);
$pdf->SetXY(80, 253);
$pdf->Cell(0, 10, "$Tarima_Total", 0, 1);

$pdf->SetXY(80, 124);
$pdf->Cell(0, 10, "$Otro_Total", 0, 1);
$pdf->SetXY(80, 259);
$pdf->Cell(0, 10, "$Otro_Total", 0, 1);

$pdf->SetXY(66, 124);
$pdf->Cell(0, 10, "$Nombre_Contenedor", 0, 1);
$pdf->SetXY(66, 259);
$pdf->Cell(0, 10, "$Nombre_Contenedor", 0, 1);



/*
////  Modelado de X y Y --------------------------------
for ($y = 10; $y <= 280; $y += 10) {
    $pdf->Text(5, $y, "Y: $y");
    $pdf->Line(15, $y, 190, $y);
}

// Crear líneas verticales
for ($x = 10; $x <= 250; $x += 10) {
    $pdf->Text($x, 5, "X: $x");
    $pdf->Line($x, 10, $x, 200);
}*/

// Generar un solo archivo PDF
$pdf->Output("I", "Hola_Envio_$Id_Salida.pdf");












////// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

/// Metodo para la Construcción del documento en Excel
/*
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('plantilla-preguia.xlsx');

// Cargar la plantilla de Excel
$sheet = $spreadsheet->getActiveSheet();

// Datos a insertar
$sheet->setCellValue('H2', $Id_Salida);
$sheet->setCellValue('K4', $Tipo_Doc);
$sheet->setCellValue('L2', date('Y-m-d'));

$sheet->setCellValue('J7', $cd_destino);
$sheet->setCellValue('J8', $nombre);
$sheet->setCellValue('J9', $calle);
$sheet->setCellValue('J10', $colonia);
$sheet->setCellValue('J11', $telefono);
$sheet->setCellValue('J12', $cp);
$sheet->setCellValue('J13', $rfc);
$sheet->setCellValue('J14', $Paqueteria);
$sheet->setCellValue('D14', $Tipo_Flete);
$sheet->setCellValue('D15', $Metodo_Pago);

$columnasBase = ['B', 'C', 'E', 'F', 'H', 'I', 'K', 'L']; // Pares de columnas disponibles

foreach ($Arreglo_deContenidos as $key => $contenido) {
    $colIndex = (int) ($key / 3) * 2; // Calcula la pareja de columnas (cada 3 registros, cambia)
    $fila = 18 + ($key % 3); // Mantiene la fila dentro del rango (18, 19, 20)

    // Define las columnas dinámicamente
    $colContenedor = $columnasBase[$colIndex];
    $colCantidad = $columnasBase[$colIndex + 1];

    // Asigna valores en la celda correspondiente
    $sheet->setCellValue($colContenedor . $fila, $contenido['Contenedor']);
    $sheet->setCellValue($colCantidad . $fila, $contenido['Cantidad']);
}

/// Segunda Parte de la tabla

$sheet->setCellValue('h24', $Id_Salida);
$sheet->setCellValue('k26', $Tipo_Doc);
$sheet->setCellValue('L24', date('Y-m-d'));
$sheet->setCellValue('J29', $cd_destino);
$sheet->setCellValue('J30', $nombre);
$sheet->setCellValue('J31', $calle);
$sheet->setCellValue('J32', $colonia);
$sheet->setCellValue('J33', $telefono);
$sheet->setCellValue('J34', $cp);
$sheet->setCellValue('J35', $rfc);
$sheet->setCellValue('J36', $Paqueteria);

$sheet->setCellValue('D36', $Tipo_Flete);
$sheet->setCellValue('D37', $Metodo_Pago);

$columnasBase = ['B', 'C', 'E', 'F', 'H', 'I', 'K', 'L']; // Pares de columnas disponibles
for ($i = 0; $i < count($Arreglo_deContenidos); $i++) {
    $colIndex = (int) ($i / 3) * 2; // Calcula la pareja de columnas (cada 3 registros, cambia)
    $fila = 40 + ($i % 3); // Mantiene la fila dentro del rango (40, 41, 42)

    // Define las columnas dinámicamente
    $colContenedor = $columnasBase[$colIndex];
    $colCantidad = $columnasBase[$colIndex + 1];

    // Asigna valores en la celda correspondiente
    $sheet->setCellValue($colContenedor . $fila, $Arreglo_deContenidos[$i]['Contenedor']);
    $sheet->setCellValue($colCantidad . $fila, $Arreglo_deContenidos[$i]['Cantidad']);
}


/// Nomenclatura del nombre del archivo: PreGuia + Id_Salida + Fecha . xlsx
$nombreArchivo = 'PreGuia_' . $Id_Salida . '_' . date('Y-m-d') . '.xlsx';
// Guardar el archivo
$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
*/
