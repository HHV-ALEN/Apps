<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

require '../../vendor/autoload.php';
require_once('../../fpdf/fpdf.php');
require_once('../../vendor/setasign/fpdi/src/autoload.php');


use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Dompdf\Options;

$Nombre_Usuario = $_SESSION['Name'];
$Id_Salida = $_GET['Id_Salida'];
$Fecha_Actual = date("Y-m-d H:i:s");

//echo "<br> Parametros Recibidos:";
//echo "<br> Nombre_Usuario: " . $Nombre_Usuario;
//echo "<br> Id_Salida: " . $Id_Salida;

// Obtener Cantidad de Empaques
// Consultar la tabla contenido con Id_Salida como parametro para obtener Cantidad, Contenedor
// Guardar en un arreglo Ordenado array (Cantidad = 1, Contenedor = "Caja")
$Contenido = array();
$sql = "SELECT Cantidad, Contenedor FROM contenido WHERE Id_Salida = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id_Salida);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $Contenido[] = array("Cantidad" => $row['Cantidad'], "Contenedor" => $row['Contenedor']);
}
$stmt->close();
//echo "<br> Contenido de empaques:";
//print_r($Contenido);

$sql_salida = "SELECT * FROM salidas WHERE Id = $Id_Salida";
$query_salida = mysqli_query($conn, $sql_salida);
$row_salida = mysqli_fetch_array($query_salida);
$Nombre_Cliente = $row_salida['Nombre_Cliente'];
$Estado = $row_salida['Estado'];
$Sucursal = $row_salida['Sucursal'];

//echo "<br> --------------------------------------------------------------------------------------------------<br>";
//echo "<br><br> De la Consulta a la tabla <strong>[salidas]</strong> con: Id " .$Id_Salida;
//echo "<br> Nombre_Cliente: " . $Nombre_Cliente;
//echo "<br> Estado: " . $Estado;
//echo "<br> Sucursal: " . $Sucursal;

$sql_bitacora = "SELECT * FROM bitacora WHERE Id_Salida = $Id_Salida AND Accion = 'Registro de Etiqueta'";
$query_bitacora = mysqli_query($conn, $sql_bitacora);
$row_bitacora = mysqli_fetch_array($query_bitacora);
$Fecha_Registro = $row_bitacora['Fecha'];
/// quitar la hora de la fecha
$Fecha_Registro = substr($Fecha_Registro, 0, 10);

//echo "<br> --------------------------------------------------------------------------------------------------<br>";
//echo "<br><br> De la Consulta a la tabla <strong>[bitacora]</strong> con: Id " .$Id_Salida . " AND Accion = 'Registro de Etiqueta'";
//echo "<br> Fecha_Registro: " . $Fecha_Registro;

////// Información de entregas Base:
/* Consultar la tabla entregas para obtener
 los campos Id_Orden_Venta, Id_Entrega, Partida, Id_Factura y guardarlos en el arreglo $entregas_base
 */
$entregas_base = array();

$sql_entregas = "SELECT Id_Orden_Venta, Id_Entrega, Partida FROM entregas WHERE Id_Salida = $Id_Salida";
$query_entregas = mysqli_query($conn, $sql_entregas);
while ($row_entregas = mysqli_fetch_array($query_entregas)) {
    $entregas_base[] = array(
        "Id_Orden_Venta" => $row_entregas['Id_Orden_Venta'],
        "Id_Entrega" => $row_entregas['Id_Entrega'],
        "Partida" => $row_entregas['Partida']
    );
}

//echo "<br><br> <strong>Entregas Base:</strong>";
//echo "<pre>";
//print_r($entregas_base);
//echo "</pre>";

// Información de entregas fusionadas
$entregas_fusionadas = array();

/*
Consultar la tabla etiquetas fusionadas donde Salida_Base es = $Id_Salida y obtener los atributos:
Orden_Venta, Entrega, Partida y guardar en el arreglo $entregas_fusionadas
*/

$sql_fusionadas = "SELECT Orden_Venta, Entrega, Partida FROM etiquetas_fusionadas WHERE Salida_Base = $Id_Salida";
$query_fusionadas = mysqli_query($conn, $sql_fusionadas);
while ($row_fusionadas = mysqli_fetch_array($query_fusionadas)) {
    $entregas_fusionadas[] = array(
        "Orden_Venta" => $row_fusionadas['Orden_Venta'],
        "Entrega" => $row_fusionadas['Entrega'],
        "Partida" => $row_fusionadas['Partida']
    );
}

//echo "<br><br> <strong>Entregas Fusionadas:</strong>";
//echo "<pre>";
//print_r($entregas_fusionadas);
//echo "</pre>";

// Información de entregas Consolidadas
$entregas_Consolidadas = array();

/*
Consultar la tabla consolidados donde Id_Base es = $Id_Salida y obtener los atributos:
Orden_Venta, Id_Entrega, Partida y guardar en el arreglo $entregas_Consolidadas
*/

$sql_consolidadas = "SELECT Orden_Venta, Id_Entrega, Partida FROM consolidados WHERE Id_Base = $Id_Salida";
$query_consolidadas = mysqli_query($conn, $sql_consolidadas);
while ($row_consolidadas = mysqli_fetch_array($query_consolidadas)) {
    $entregas_Consolidadas[] = array(
        "Orden_Venta" => $row_consolidadas['Orden_Venta'],
        "Id_Entrega" => $row_consolidadas['Id_Entrega'],
        "Partida" => $row_consolidadas['Partida']
    );
}

//echo "<br><br> <strong>Entregas Consolidadas:</strong>";
//echo "<pre>";
//print_r($entregas_Consolidadas);
//echo "</pre>";

$CantidadDeContenedores = 0;

// Suma la cantidad de contenedores
foreach ($Contenido as $item) {
    $CantidadDeContenedores += $item['Cantidad'];
}

//echo "<br> Existen $CantidadDeContenedores contenedores.";

/* Información necesaria para La etiqueta
- Fecha de registro = $Fecha_Registro
- Folio =  $Id_Salida
- Nombre del cliente = $Nombre_Cliente
- - - Entregas Base (Orden de Venta, Entrega, Partida)
- - - Entregas Fusionadas (Orden de Venta, Entrega, Partida)
- - - Entregas Consolidadas (Orden de Venta, Entrega, Partida)
- Empaques
- Total de Empaques
- No. de Empaques (Cantidad de Etiquetas [1/3])
*/

// ------------------------------------------------------- Llenado de la etiqueta:
/// Juntar Arreglos de Entregas
$entregas = array_merge($entregas_base, $entregas_fusionadas, $entregas_Consolidadas);


$pdf = new Fpdi();
$pdf->SetFont('Arial', '', 10);

// --------------------------------------------------
// 1) Cargar plantilla una sola vez
// --------------------------------------------------
$pdf->setSourceFile("PlantillaImpresion.pdf");
$templateId = $pdf->importPage(1);
$size = $pdf->getTemplateSize($templateId);
$scale = 210 / $size['width'];

// --------------------------------------------------
// 2) PRECALCULAR CONTENEDORES (antes del loop)
// --------------------------------------------------
$suma_por_contenedor = [];
$suma_total_contenedores = 0;

if (!empty($Contenido) && is_array($Contenido)) {
    foreach ($Contenido as $item) {
        $cont = isset($item['Contenedor']) ? trim($item['Contenedor']) : '';
        $cant = isset($item['Cantidad']) ? (int)$item['Cantidad'] : 0;
        if ($cont === '' || $cant <= 0) continue;

        $suma_por_contenedor[$cont] = ($suma_por_contenedor[$cont] ?? 0) + $cant;
        $suma_total_contenedores += $cant;
    }
}

// --------------------------------------------------
// 3) Imprimir ENTREGAS (10 por página) + CONTENEDORES en cada página
// --------------------------------------------------
$maxFilas = 10;
$filaActual = 0;
$startY = 98;
$stepY  = 10;

// Calcular total de juegos = contenedores + 1
$totalJuegos = $suma_total_contenedores + 1;

for ($juego = 1; $juego <= $totalJuegos; $juego++) {

    // Reiniciar fila
    $filaActual = 0;

    foreach ($entregas as $index => $entrega) {
        // Si es la primera fila de una hoja o ya llenamos 10 filas → nueva página
        if ($filaActual % $maxFilas == 0) {
            $pdf->AddPage('P', 'A4');
            $pdf->setSourceFile("PlantillaImpresion.pdf");
            $templateId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($templateId);
            $scale = 210 / $size['width'];
            $pdf->useTemplate($templateId, 0, 0, 210, $size['height'] * $scale);

            // Encabezados (estos se repiten en cada hoja)
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetXY(160, 52);
            $pdf->Cell(0, 10, utf8_decode($Fecha_Registro), 0, 1);

            $pdf->SetXY(170, 63);
            $pdf->Cell(0, 10, utf8_decode($Id_Salida), 0, 1);

            $pdf->SetXY(75, 77);
            $pdf->Cell(0, 10, utf8_decode($Nombre_Cliente), 0, 1);

            // Aquí pintamos los CONTENEDORES también en cada página
            foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
                switch ($Contenedor) {
                    case 'Caja':
                        $pdf->SetXY(20, 205); $pdf->Cell(0, 10, "$Cantidad", 0, 1); break;
                    case 'Rollo':
                        $pdf->SetXY(20, 215); $pdf->Cell(0, 10, "$Cantidad", 0, 1); break;
                    case 'Tarima':
                        $pdf->SetXY(20, 225); $pdf->Cell(0, 10, "$Cantidad", 0, 1); break;
                    case 'Carrete':
                        $pdf->SetXY(120, 215); $pdf->Cell(0, 10, "$Cantidad", 0, 1); break;
                    case 'Paquete':
                        $pdf->SetXY(120, 205); $pdf->Cell(0, 10, "$Cantidad", 0, 1); break;
                }
            }

            // Total de contenedores
            $pdf->SetXY(25, 255);
            $pdf->Cell(0, 5, "$suma_total_contenedores", 0, 1);

            // No. de empaque:
            // (Actual) / (Cantidad de Empaques)
   // 🔑 Número de etiqueta (ejemplo: 1/3, 2/3, ..., 4/4)
            $pdf->SetXY(60, 255); // ajusta esta coordenada a donde esté tu celda de índice
            $pdf->Cell(0, 5, "$juego/$totalJuegos", 0, 1);

            // Reiniciar fila para las entregas
            $filaActual = 0;
        }

        // Coordenada Y para esta fila
        $y = $startY + ($filaActual * $stepY);

        // --- Orden de venta ---
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(15, $y);
        $pdf->Cell(0, 10, utf8_decode($entrega['Orden_Venta'] ?? $entrega['Id_Orden_Venta']), 0, 1);

        // --- Entrega ---
        $pdf->SetXY(80, $y);
        $pdf->Cell(0, 10, utf8_decode($entrega['Entrega'] ?? $entrega['Id_Entrega']), 0, 1);

        // --- Partida ---
        $pdf->SetXY(140, $y);
        $pdf->Cell(0, 10, utf8_decode($entrega['Partida']), 0, 1);

        $filaActual++;
    }
}

// --------------------------------------------------
// 4) (Opcional) limpiar buffer antes de enviar PDF
//    Evita el “Some data has already been output...” si hubo warnings previos.
// --------------------------------------------------
// if (ob_get_length()) { ob_end_clean(); }

$pdf->Output('I', 'Etiqueta_Completa.pdf');


/// 10 espacios en vertical