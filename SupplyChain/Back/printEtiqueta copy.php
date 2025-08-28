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
/*
echo "<br> Parametros Recibidos:";
echo "<br> Nombre_Usuario: " . $Nombre_Usuario;
echo "<br> Id_Salida: " . $Id_Salida;
*/

$Arreglo_de_ContenidoEmpaque = array();
$Arreglo_de_salidas_totales = array();


/// Se agrega el ID de la salida base al $Arreglo_de_salidas_totales
$Arreglo_de_salidas_totales[] = $Id_Salida;

$sql_salida = "SELECT * FROM salidas WHERE Id = $Id_Salida";
$query_salida = mysqli_query($conn, $sql_salida);
$row_salida = mysqli_fetch_array($query_salida);
$Nombre_Cliente = $row_salida['Nombre_Cliente'];
$Estado = $row_salida['Estado'];
$Sucursal = $row_salida['Sucursal'];
/*
echo "<br> --------------------------------------------------------------------------------------------------<br>";
echo "<br><br> De la Consulta a la tabla <strong>[salidas]</strong> con: Id " .$Id_Salida;
echo "<br> Nombre_Cliente: " . $Nombre_Cliente;
echo "<br> Estado: " . $Estado;
echo "<br> Sucursal: " . $Sucursal;
*/

$sql_bitacora = "SELECT * FROM bitacora WHERE Id_Salida = $Id_Salida AND Accion = 'Registro de Etiqueta'";
$query_bitacora = mysqli_query($conn, $sql_bitacora);
$row_bitacora = mysqli_fetch_array($query_bitacora);
$Fecha_Registro = $row_bitacora['Fecha'];
/// quitar la hora de la fecha
$Fecha_Registro = substr($Fecha_Registro, 0, 10);
/*
echo "<br> --------------------------------------------------------------------------------------------------<br>";
echo "<br><br> De la Consulta a la tabla <strong>[bitacora]</strong> con: Id " .$Id_Salida . " AND Accion = 'Registro de Etiqueta'";
echo "<br> Fecha_Registro: " . $Fecha_Registro;
*/

$sql_orden = "SELECT * FROM entregas WHERE Id_Salida = $Id_Salida";
$query_orden = mysqli_query($conn, $sql_orden);
$row_orden = mysqli_fetch_array($query_orden);
$Id_Orden_Venta = $row_orden['Id_Orden_Venta'];
$Id_Entrega = $row_orden['Id_Entrega'];
$Partida = $row_orden['Partida'];
/*
echo "<br> --------------------------------------------------------------------------------------------------<br>";
echo "<br><br> De la Consulta a la tabla <strong>[entregas]</strong> con: Id " . $Id_Salida;
echo "<br> Fecha_Registro: " . $Fecha_Registro;
*/
// 1.- Obtener los Folios de las salidas Fusionadas

$Arreglo_de_salidas_fusionadas = array();
$Arreglo_de_entregas_fusionadas = array();

$sql_fusion = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = $Id_Salida";
$query_fusion = mysqli_query($conn, $sql_fusion);

while ($row_fusion = mysqli_fetch_array($query_fusion)) {
    $Id_Relacion_Salida = $row_fusion['Id_Relacion_Salida'];
    $Arreglo_de_salidas_totales[] = $Id_Relacion_Salida;

    //    echo "<s//trong>Id de la Salida Fusionada: </strong>" . $Id_Relacion_Salida . "<br>";
    array_push($Arreglo_de_salidas_fusionadas, $Id_Relacion_Salida);

    //  echo "<b//r>-------------- Información de la Salida " . $Id_Relacion_Salida . " ------------------<br>";
    // 2.- Obtener Información de los registros Fusionados
    $sql_salida_fusionada = "SELECT * FROM salidas WHERE Id = $Id_Relacion_Salida";
    $query_salida_fusionada = mysqli_query($conn, $sql_salida_fusionada);
    $row_salida_fusionada = mysqli_fetch_array($query_salida_fusionada);
    $Nombre_Cliente_Fusionada = $row_salida_fusionada['Nombre_Cliente'];
    $Estado_Fusionada = $row_salida_fusionada['Estado'];
    $Sucursal_Fusionada = $row_salida_fusionada['Sucursal'];


    $sql_orden_fusionada = "SELECT * FROM entregas WHERE Id_Salida = $Id_Relacion_Salida";
    $query_orden_fusionada = mysqli_query($conn, $sql_orden_fusionada);
    $row_orden_fusionada = mysqli_fetch_array($query_orden_fusionada);
    $Id_Orden_Venta_Fusionada = $row_orden_fusionada['Id_Orden_Venta'];
    $Id_Entrega_Fusionada = $row_orden_fusionada['Id_Entrega'];
    $Partida_Fusionada = $row_orden_fusionada['Partida'];
    // Guardar los datos en un [Id_Relacion_Salida] => [Id_Orden_Venta_Fusionada, Id_Entrega_Fusionada, Partida_Fusionada]
    $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida] = array($Id_Orden_Venta_Fusionada, $Id_Entrega_Fusionada, $Partida_Fusionada);


    $sql_empaque_fusionada = "SELECT * FROM contenido WHERE Id_Salida = $Id_Relacion_Salida";
    $query_empaque_fusionada = mysqli_query($conn, $sql_empaque_fusionada);
    while ($row_empaque = mysqli_fetch_array($query_empaque_fusionada)) {
        $Contenedor = $row_empaque['Contenedor'];
        $Cantidad = $row_empaque['Cantidad'];
        // --> $Arreglo_de_ContenidoEmpaque 
        $Arreglo_de_ContenidoEmpaque[] = [
            'Contenedor' => $Contenedor,
            'Cantidad' => $Cantidad
        ];
    }
}


$Arreglo_de_salidas_consolidadas = array();
$Arreglo_de_entregas_consolidadas = array();


$sql_consolidados = "SELECT * FROM consolidados WHERE Id_Base = $Id_Salida";
$query_process = mysqli_query($conn, $sql_consolidados);
while ($row_consolidado = mysqli_fetch_array($query_process)) {
    $Id_salida_consolidado = $row_consolidado['Id_salida_consolidada'];
    //echo "<br>" . $Id_salida_consolidado;
    $Arreglo_de_salidas_consolidadas[] = $Id_salida_consolidado;
    $Arreglo_de_salidas_totales[] = $Id_salida_consolidado;
}



foreach ($Arreglo_de_salidas_consolidadas as $Salida_consolidada) {
    //echo "<br> - Salida Id " . $Salida_consolidada;
    /// Consultar la tabla de entrega_factura_refactor
    $sql_orden_fusionada = "SELECT * FROM entregas WHERE Id_Salida = $Salida_consolidada";
    $query_orden_fusionada = mysqli_query($conn, $sql_orden_fusionada);
    $row_orden_fusionada = mysqli_fetch_array($query_orden_fusionada);
    $Id_Orden_Venta_Fusionada = $row_orden_fusionada['Id_Orden_Venta'];
    $Id_Entrega_Fusionada = $row_orden_fusionada['Id_Entrega'];
    $Partida_Fusionada = $row_orden_fusionada['Partida'];

    // Guardar los datos en un [Id_Relacion_Salida] => [Id_Orden_Venta_Fusionada, Id_Entrega_Fusionada, Partida_Fusionada]
    $Arreglo_de_entregas_consolidadas[$Salida_consolidada] = array($Id_Orden_Venta_Fusionada, $Id_Entrega_Fusionada, $Partida_Fusionada);
    /// Consultar la tabla contenido_refactor
}


$suma_total_contenedores = 0;

$Fecha_Actual = date("Y-m-d H:i:s");





// Asegurarte de que son enteros y evitar inyecciones
$ids = array_map('intval', $Arreglo_de_salidas_totales);

// Convertir a cadena separada por comas
$ids_sql = implode(',', $ids);

// Query para traer todos los contenidos de esas salidas
$sql_empaque = "SELECT * FROM contenido WHERE Id_Salida IN ($ids_sql)";
$query_empaque = mysqli_query($conn, $sql_empaque);

// Organizar los resultados por salida
$Arreglo_de_ContenidoEmpaque = [];

while ($row_empaque = mysqli_fetch_assoc($query_empaque)) {
    $idSalida = $row_empaque['Id_Salida'];

    $Contenedor = $row_empaque['Contenedor'];
    $Cantidad = $row_empaque['Cantidad'];
    // --> $Arreglo_de_ContenidoEmpaque 
    $Arreglo_de_ContenidoEmpaque[] = [
        'Contenedor' => $Contenedor,
        'Cantidad' => $Cantidad
    ];

    $suma_total_contenedores += $Cantidad;
}

//echo "<br> <strong>Suma Contenedores Totales: </strong>" . $suma_total_contenedores;


/*
/// Fusionadas ----------------------------------
echo "<br><br> <h1> Etiquetas Fusionadas: </h1> "; 
print_r($Arreglo_de_salidas_fusionadas);
echo "<br><br> <h1> Entregas Fusionadas: </h1> "; 
print_r($Arreglo_de_entregas_fusionadas);

/// Salidas Consolidadadas ----------------------------------
echo "<br><br> <h1> Arreglo de Salidas Consolidados: </h1> "; 
print_r($Arreglo_de_salidas_consolidadas);

echo "<hr>";

/// Entregas Consolidadadas ----------------------------------
echo "<br><br> <h1> Arreglo de Entregas Consolidades: </h1> "; 
print_r($Arreglo_de_entregas_consolidadas);

/// Salidas Totales ----------------------------------
echo "<br><br> <h1> Arreglo de Salidas Totales: </h1> "; 
print_r($Arreglo_de_salidas_totales);
*/

$pdf = new Fpdi();

// --- Función para cargar plantilla ---
function cargarPlantilla($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque) {
    $pdf->AddPage('P', 'A4');
    $pdf->setSourceFile("PlantillaImpresion.pdf");
    $template = $pdf->importPage(1);

    $size = $pdf->getTemplateSize($template);
    $scale = 210 / $size['width'];
    $newHeight = $size['height'] * $scale;

    $pdf->useTemplate($template, 0, 0, 210, $newHeight);

    $pdf->SetFont('Arial', 'B', 16);
    // Encabezado
    $pdf->SetXY(170, 63);
    $pdf->Cell(0, 10, $Id_Salida, 0, 1);

    $pdf->SetXY(160, 52);
    $pdf->Cell(0, 10, "$Fecha_Registro", 0, 1);

    $pdf->SetXY(75, 77);
    $pdf->Cell(0, 10, "$Nombre_Cliente", 0, 1);


    // Primera row fija
    $pdf->SetXY(10, 97);
    $pdf->Cell(0, 10, "$Id_Orden_Venta", 0, 1);

    $pdf->SetXY(75, 97);
    $pdf->Cell(0, 10, "$Id_Entrega", 0, 1);

    $pdf->SetXY(140, 97);
    $pdf->Cell(0, 10, "$Partida", 0, 1);
      // 🔑 Ahora cada vez que se carga la plantilla, se pintan también los contenedores
    imprimirContenedores($pdf, $Arreglo_de_ContenidoEmpaque);
}

// --- Función para imprimir una fila ---
function imprimirFila($pdf, $x1, $x2, $x3, $y, $val1, $val2, $val3) {
    $pdf->SetXY($x1, $y);
    $pdf->Cell(0, 10, $val1, 0, 1);

    $pdf->SetXY($x2, $y);
    $pdf->Cell(0, 10, $val2, 0, 1);

    $pdf->SetXY($x3, $y);
    $pdf->Cell(0, 10, $val3, 0, 1);
}

// --- Función para imprimir contenedores ---
function imprimirContenedores($pdf, $Arreglo_de_ContenidoEmpaque) {
    $suma_por_contenedor = [];
    $suma_total_contenedores = 0;

    // Agrupar cantidades
    foreach ($Arreglo_de_ContenidoEmpaque as $Contenido) {
        $Contenedor = $Contenido['Contenedor'];
        $Cantidad = $Contenido['Cantidad'];

        if (isset($suma_por_contenedor[$Contenedor])) {
            $suma_por_contenedor[$Contenedor] += $Cantidad;
        } else {
            $suma_por_contenedor[$Contenedor] = $Cantidad;
        }
    }

    // Sumar totales
    foreach ($suma_por_contenedor as $Cantidad) {
        $suma_total_contenedores += $Cantidad;
    }

    $espaciosExtra = 0;

    foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
        if ($Contenedor == 'Caja') {
            $pdf->SetXY(20, 205);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Rollo') {
            $pdf->SetXY(20, 215);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Tarima') {
            $pdf->SetXY(20, 225);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Carrete') {
            $pdf->SetXY(120, 215);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Paquete') {
            $pdf->SetXY(120, 205);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } else {
            // Espacios extra
            if ($espaciosExtra == 0) {
                $pdf->SetXY(147, 225);
                $pdf->Cell(0, 10, "$Contenedor", 0, 1);

                $pdf->SetXY(120, 225);
                $pdf->Cell(0, 10, "$Cantidad", 0, 1);
                $espaciosExtra++;
            } elseif ($espaciosExtra == 1) {
                $pdf->SetXY(55, 235);
                $pdf->Cell(0, 10, "$Contenedor", 0, 1);

                $pdf->SetXY(20, 235);
                $pdf->Cell(0, 10, "$Cantidad", 0, 1);
                $espaciosExtra++;
            } else {
                $pdf->SetXY(155, 247);
                $pdf->Cell(0, 10, "Otros: $Contenedor ($Cantidad)", 0, 1);
            }
        }
    }

    // Total de paquetes
    $pdf->SetXY(25, 255);
    $pdf->Cell(0, 5, "$suma_total_contenedores", 0, 1);

    return $suma_total_contenedores;
}

// --- Función para cargar hoja del proceso ---
function cargarHojaProceso($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque) {
    $pdf->AddPage('P', 'A4');
    $pdf->setSourceFile("PlantillaImpresion.pdf"); // si es otra plantilla, cámbiala
    $template = $pdf->importPage(1);

    $size = $pdf->getTemplateSize($template);
    $scale = 210 / $size['width'];
    $newHeight = $size['height'] * $scale;

    $pdf->useTemplate($template, 0, 0, 210, $newHeight);

    $pdf->SetFont('Arial', 'B', 16);

    // Encabezado básico
    $pdf->SetXY(170, 63);
    $pdf->Cell(0, 10, $Id_Salida, 0, 1);

    $pdf->SetXY(160, 52);
    $pdf->Cell(0, 10, "$Fecha_Registro", 0, 1);

    $pdf->SetXY(75, 77);
    $pdf->Cell(0, 10, "$Nombre_Cliente", 0, 1);

    // Primera row fija
    $pdf->SetXY(10, 97);
    $pdf->Cell(0, 10, "$Id_Orden_Venta", 0, 1);

    $pdf->SetXY(75, 97);
    $pdf->Cell(0, 10, "$Id_Entrega", 0, 1);

    $pdf->SetXY(140, 97);
    $pdf->Cell(0, 10, "$Partida", 0, 1);

    // Si quieres más datos aquí, se agregan...
}
// ----------------- INICIO GENERACIÓN ----------------- //

// Primero calculamos la suma total de contenedores
$suma_total_contenedores = 0;
foreach ($Arreglo_de_ContenidoEmpaque as $contenido) {
    $suma_total_contenedores += $contenido['Cantidad'];
}

// Unificamos los arreglos que tienen el mismo formato (hacer esto UNA sola vez)
$registros = [];

// Fusionadas
foreach ($Arreglo_de_salidas_fusionadas as $Id_Relacion_Salida) {
    $registros[] = [
        $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][0],
        $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][1],
        $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][2],
    ];
}

// Consolidadas
foreach ($Arreglo_de_entregas_consolidadas as $entrega) {
    $registros[] = [$entrega[0], $entrega[1], $entrega[2]];
}

// Imprimir la misma etiqueta $suma_total_contenedores veces
for ($copias = 0; $copias <= $suma_total_contenedores; $copias++) {
    
    // Cargar plantilla principal (ESTA función ya agrega una nueva página)
    cargarPlantilla($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque);
    
    // Reiniciar variables de posición para CADA etiqueta
    $y = 110; // primera fila disponible
    $filasPorPagina = 9;
    $contadorFilas = 0;
    
    // Imprimir todos los registros en la plantilla actual
    foreach ($registros as $row) {
        if ($contadorFilas == $filasPorPagina) {
            // Si se llena la página, crear nueva página con la misma plantilla
            cargarPlantilla($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque);
            $y = 110;
            $contadorFilas = 0;
        }
        
        imprimirFila($pdf, 10, 75, 140, $y, $row[0], $row[1], $row[2]);
        $y += 10;
        $contadorFilas++;
    }
    
    $masUnoCopias = $copias + 1;
    $suma_total_contenedoresMasUno = $suma_total_contenedores + 1;
    // Numero de impresión (ejemplo de 1 / n)
    $pdf->SetXY(65, 255);
    $pdf->Cell(0, 10, "$masUnoCopias / $suma_total_contenedoresMasUno", 0, 1);
    
    // Agregar la hoja de proceso al final de cada copia (SOLO UNA vez por etiqueta)
    // cargarHojaProceso($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque);
}

// Agregar UNA etiqueta extra para documentar el proceso
//cargarPlantilla($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque);

// Hoja de proceso para la copia extra
//cargarHojaProceso($pdf, $Id_Salida, $Fecha_Registro, $Nombre_Cliente, $Id_Orden_Venta, $Id_Entrega, $Partida, $Arreglo_de_ContenidoEmpaque);

// Output final
$pdf->Output("I", "Etiqueta_Completa_$Id_Salida.pdf");

echo "<br><hr>";
echo "Total de copias a imprimir: " . $suma_total_contenedores . " + 1 copia de proceso";

//print_r($Arreglo_de_ContenidoEmpaque);
// Crear líneas horizontales
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
// Guardar el PDF con la info escrita

