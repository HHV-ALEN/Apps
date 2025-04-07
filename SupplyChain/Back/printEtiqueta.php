<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

//print_r($_SESSION);
//echo "<hr>";

$Nombre_Usuario = $_SESSION['Name'];
$Id_Salida = $_GET['Id_Salida'];

$Arreglo_de_ContenidoEmpaque = array();
/*
echo "<h2> Información de La Salida Base</h2>";
echo "<strong>Nombre de Usuario Solicitante:</strong> " . $Nombre_Usuario . "<br>";
echo "<strong>Id de Salida:</strong> " . $Id_Salida . "<br>";

echo "<br><-----------------------Información de la salida ------------------------><br>";
*/
// Consultar la información de la Salida _ Base
$sql_salida = "SELECT * FROM salidas WHERE Id = $Id_Salida";
$query_salida = mysqli_query($conn, $sql_salida);
$row_salida = mysqli_fetch_array($query_salida);
$Nombre_Cliente = $row_salida['Nombre_Cliente'];
$Estado = $row_salida['Estado'];
$Sucursal = $row_salida['Sucursal'];

$sql_bitacora = "SELECT * FROM bitacora WHERE Id_Salida = $Id_Salida AND Accion = 'Registro de Etiqueta'";
$query_bitacora = mysqli_query($conn, $sql_bitacora);
$row_bitacora = mysqli_fetch_array($query_bitacora);
$Fecha_Registro = $row_bitacora['Fecha'];
/// quitar la hora de la fecha
$Fecha_Registro = substr($Fecha_Registro, 0, 10);

/*
echo "<strong>Nombre del cliente: </strong>" . $Nombre_Cliente . "<br>";
echo "<strong>Estado: </strong>" . $Estado . "<br>";
echo "<strong>Sucursal: </strong>" . $Sucursal . "<br>";
// Orden de Venta | Entrega | Partida
echo "<br><------------------- Información de la entrega----------------------------><br>";
*/
$sql_orden = "SELECT * FROM entregas WHERE Id_Salida = $Id_Salida";
$query_orden = mysqli_query($conn, $sql_orden);
$row_orden = mysqli_fetch_array($query_orden);
$Id_Orden_Venta = $row_orden['Id_Orden_Venta'];
$Id_Entrega = $row_orden['Id_Entrega'];
$Partida = $row_orden['Partida'];

/*
echo "<strong>Id de la Orden de Venta: </strong>" . $Id_Orden_Venta . "<br>";
echo "<strong>Id de la Entrega: </strong>" . $Id_Entrega . "<br>";
echo "<strong>Partida: </strong>" . $Partida . "<br>";


echo "<br><------------------- Información de los Empaques ( Contenido )----------------------------><br>";

echo "<hr>";
echo "Id de la Salida: " . $Id_Salida . "<br>";
*/
$sql_empaque = "SELECT * FROM contenido WHERE Id_Salida = $Id_Salida";
$query_empaque = mysqli_query($conn, $sql_empaque);
// Iiterar para conseguir todos los elementos
while ($row_empaque = mysqli_fetch_array($query_empaque)) {
    $Contenedor = $row_empaque['Contenedor'];
    $Cantidad = $row_empaque['Cantidad'];
    // --> $Arreglo_de_ContenidoEmpaque 
    $Arreglo_de_ContenidoEmpaque[] = [
        'Contenedor' => $Contenedor,
        'Cantidad' => $Cantidad
    ];
    //echo "<br>Contenedor: $Contenedor  ";
    //echo "<br>Cantidad: $Cantidad ";
}

/*echo<pre>";
print_$Arreglo_de_ContenidoEmpaque);
echo "</pre>";



<hr>";

echo<h2> Información de La Salidas Fusionadas</h2>";

/*
Orgazar los resultados de las consultas en arreglos para poder trabajar con ellos
1- ARREGLO DE SALIDAS FUSIONADAS
*/

// 1.- Obtener los Folios de las salidas Fusionadas
$Arreglo_de_salidas_fusionadas = array();
$Arreglo_de_entregas_fusionadas = array();
$sql_fusion = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = $Id_Salida";
$query_fusion = mysqli_query($conn, $sql_fusion);

while ($row_fusion = mysqli_fetch_array($query_fusion)) {
    $Id_Relacion_Salida = $row_fusion['Id_Relacion_Salida'];

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


    /*echo "<strong>Nombre del cliente: </strong>" . $Nombre_Cliente_Fusionada . "<br>";
    echo "<strong>Estado: </strong>" . $Estado_Fusionada . "<br>";
    echo "<strong>Sucursal: </strong>" . $Sucursal_Fusionada . "<br>";


    /// 3.- Obtener Información de la Orden de Venta | Entrega | Partidas

    //echo "<b//r>-------------- Información de la Entrega " . $Id_Relacion_Salida . " ------------------<br>";

    */
    $sql_orden_fusionada = "SELECT * FROM entregas WHERE Id_Salida = $Id_Relacion_Salida";
    $query_orden_fusionada = mysqli_query($conn, $sql_orden_fusionada);
    $row_orden_fusionada = mysqli_fetch_array($query_orden_fusionada);
    $Id_Orden_Venta_Fusionada = $row_orden_fusionada['Id_Orden_Venta'];
    $Id_Entrega_Fusionada = $row_orden_fusionada['Id_Entrega'];
    $Partida_Fusionada = $row_orden_fusionada['Partida'];
    // Guardar los datos en un [Id_Relacion_Salida] => [Id_Orden_Venta_Fusionada, Id_Entrega_Fusionada, Partida_Fusionada]
    $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida] = array($Id_Orden_Venta_Fusionada, $Id_Entrega_Fusionada, $Partida_Fusionada);


    /*echo "<strong>Id de la Orden de Venta: </strong>" . $Id_Orden_Venta_Fusionada . "<br>";
    echo "<strong>Id de la Entrega: </strong>" . $Id_Entrega_Fusionada . "<br>";
    echo "<strong>Partida: </strong>" . $Partida_Fusionada . "<br>";

    // 4.- Obtener Información de los Empaques ( Contenido )
    echo "<br>-------------- Información de los Empaques ( Contenido ) " . $Id_Relacion_Salida . " ------------------<br>";

    */
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
        //echo "<b//r>Contenedor: $Contenedor  ";
        //echo "<b//r>Cantidad: $Cantidad ";
    }
    //echo "<h//r>";
}

//echo "<hr>";
//echo "<br> Información de Los registros Consolidados Relacionados";
$Arreglo_de_salidas_consolidadas = array();
$Arreglo_de_entregas_consolidadas = array();
$sql_consolidados = "SELECT * FROM consolidados WHERE Id_Base = $Id_Salida";
$query_process = mysqli_query($conn, $sql_consolidados);


while ($row_consolidado = mysqli_fetch_array($query_process)) {
    $Id_salida_consolidado = $row_consolidado['Id_salida_consolidada'];
    //echo "<br>" . $Id_salida_consolidado;
    $Arreglo_de_salidas_consolidadas[] = $Id_salida_consolidado;
}

//print_r($Arreglo_de_salidas_consolidadas);
//echo "<hr>";

/// ------------- Consulta  de Entrega -----------



foreach ($Arreglo_de_salidas_consolidadas as $Salida_consolidada) {
    //echo "<br> - Salida Id " . $Salida_consolidada;
    /// Consultar la tabla de entrega_factura_refactor
    $sql_orden_fusionada = "SELECT * FROM entregas WHERE Id_Salida = $Salida_consolidada";
    $query_orden_fusionada = mysqli_query($conn, $sql_orden_fusionada);
    $row_orden_fusionada = mysqli_fetch_array($query_orden_fusionada);
    $Id_Orden_Venta_Fusionada = $row_orden_fusionada['Id_Orden_Venta'];
    $Id_Entrega_Fusionada = $row_orden_fusionada['Id_Entrega'];
    $Partida_Fusionada = $row_orden_fusionada['Partida'];
    /*
    echo "<br> - Id Orden Venta: " . $Id_Orden_Venta_Fusionada;
    echo "<br> - Id Entrega : " . $Id_Entrega_Fusionada;
    echo "<br> - Partida Fusionada : " . $Partida_Fusionada;
    echo "<br> --------------------------------------------------------- <br>";
    */

    // Guardar los datos en un [Id_Relacion_Salida] => [Id_Orden_Venta_Fusionada, Id_Entrega_Fusionada, Partida_Fusionada]
    $Arreglo_de_entregas_consolidadas[$Salida_consolidada] = array($Id_Orden_Venta_Fusionada, $Id_Entrega_Fusionada, $Partida_Fusionada);
    /// Consultar la tabla contenido_refactor
}

$suma_total_contenedores = 0;
//echo "<hr>";
//print_r($Arreglo_de_ContenidoEmpaque);

/// del arreglo de contenido, sumar "Cantidad" 
foreach ($Arreglo_de_ContenidoEmpaque as $Contenido) {
    $suma_total_contenedores += $Contenido['Cantidad'];
}



/* echo "<br> <strong>Suma Contenedores Totales: </strong>" . $suma_total_contenedores;
//$Estado
echo "<br> Cuando el folio se encuentra en estado de 'Empaque' -> Este sera actualizado a 'Facturación' </br>";
echo "<br> El estado de la Salida con Id:" . $Id_Salida . "</br>";
echo "<br> El estado de la Salida es: " . $Estado;

*/
//echo "<br> El estado de la Salida es: " . $Estado;
$Fecha_Actual = date("Y-m-d H:i:s");

if ($Estado == 'Empaque') {
    // Actualizar el estado en la tabla salida_refactor
    $query_update = "UPDATE salidas SET Estado = 'Facturacion', Id_Status = 23 WHERE Id = $Id_Salida";
    $result = mysqli_query($conn, $query_update);

    if ($result) {
        //echo "<br> El estado de la Salida ha sido actualizado a 'Facturación'";

        // Registrar en la tabla actualizaciones_bitacora_nueva
        $query_insert_bitacora = "INSERT INTO bitacora 
        (Id_Salida, Accion, Fecha, Responsable) VALUES 
        ($Id_Salida, 'Entrega A Facturación', '$Fecha_Actual', '$Nombre_Usuario')";

        $result_bitacora = mysqli_query($conn, $query_insert_bitacora);

        if ($result_bitacora) {
            //echo "<br> Se ha registrado la actualización en la bitácora";
        } else {
            //echo "<br> Error al registrar la actualización en la bitácora: " . mysqli_error($con);
        }
    } else {
        //echo "<br> Error al actualizar el estado: " . mysqli_error($con);
    }
}


//echo "<hr>";

require_once('../../fpdf/fpdf.php');
require_once('../../vendor/setasign/fpdi/src/autoload.php');


use setasign\Fpdi\Fpdi;
$pdf = new Fpdi();

for ($i = 1; $i <= $suma_total_contenedores; $i++) {
    $pdf->AddPage();
    $pdf->setSourceFile("PlantillaImpresion.pdf");
    $template = $pdf->importPage(1);
    $pdf->useTemplate($template, 0, 0, 220);


    // Definir fuente
    $pdf->SetFont('Arial', 'B', 16);

    // Número de Folio
    $pdf->SetXY(170, 67); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, $Id_Salida, 0, 1);

    // Número de Folio
    $pdf->SetXY(160, 55); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Fecha_Registro", 0, 1);

    // Nombre del Cliente
    $pdf->SetXY(75, 80); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Nombre_Cliente", 0, 1);

    /// --------------------- Primera row: Info de la salida base ---------------------

    $pdf->SetXY(10, 102); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Id_Orden_Venta", 0, 1);

    $pdf->SetXY(75, 102); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Id_Entrega", 0, 1);

    $pdf->SetXY(140, 102); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Partida", 0, 1);

    /// --------------------- Continuación de Rows: Info de la salidas Fusionadas ---------------------

    $y = 115; // Posición inicial de la primera fila

    foreach ($Arreglo_de_salidas_fusionadas as $Id_Relacion_Salida) {
        $Id_Orden_Venta_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][0];
        $Id_Entrega_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][1];
        $Partida_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][2];

        $pdf->SetXY(10, $y); // (x, y) ajusta según el diseño
        $pdf->Cell(0, 10, "$Id_Orden_Venta_Fusionada", 0, 1);

        $pdf->SetXY(75, $y); // (x, y) ajusta según el diseño
        $pdf->Cell(0, 10, "$Id_Entrega_Fusionada", 0, 1);

        $pdf->SetXY(140, $y); // (x, y) ajusta según el diseño
        $pdf->Cell(0, 10, "$Partida_Fusionada", 0, 1);

        $y += 10; // Ajustar la posición de la siguiente fila
    }


    foreach ($Arreglo_de_entregas_consolidadas as $entrega) {
        $Id_Orden_Venta_Consolidada = $entrega[0];
        $Id_Entrega_Consolidada = $entrega[1];
        $Partida_Consolidada = $entrega[2];

        $pdf->SetXY(10, $y);
        $pdf->Cell(0, 10, "$Id_Orden_Venta_Consolidada", 0, 1);

        $pdf->SetXY(75, $y);
        $pdf->Cell(0, 10, "$Id_Entrega_Consolidada", 0, 1);

        $pdf->SetXY(140, $y);
        $pdf->Cell(0, 10, "$Partida_Consolidada", 0, 1);

        $y += 10;
    }


    /// --------------------- Continuación de Rows: Info de los Contenedores ---------------------

    // Initialize an associative array to group quantities by Contenedor
    $suma_por_contenedor = [];

    // Iterate through the array to sum quantities
    foreach ($Arreglo_de_ContenidoEmpaque as $Contenido) {
        $Contenedor = $Contenido['Contenedor'];
        $Cantidad = $Contenido['Cantidad'];

        // If the Contenedor already exists in the array, add the Cantidad
        if (isset($suma_por_contenedor[$Contenedor])) {
            $suma_por_contenedor[$Contenedor] += $Cantidad;
        } else {
            // Otherwise, initialize the Contenedor with the current Cantidad
            $suma_por_contenedor[$Contenedor] = $Cantidad;
        }
    }

    $suma_total_contenedores = 0;
    // Print the summed quantities for each Contenedor
    foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
        //  echo "Contenedor: $Contenedor, Cantidad Total: $Cantidad<br>";
        $suma_total_contenedores += $Cantidad;
    }

    //echo "<br> <strong>Suma Contenedores Totales: </strong>" . $suma_total_contenedores;

    // Example for your PDF output
    $espaciosExtra = 0; // Para llevar el control de los espacios ocupados

    foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
        if ($Contenedor == 'Caja') {
            $pdf->SetXY(20, 215);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Rollo') {
            $pdf->SetXY(20, 225);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Tarima') {
            $pdf->SetXY(20, 235);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Carrete') {
            $pdf->SetXY(120, 225);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } elseif ($Contenedor == 'Paquete') {
            $pdf->SetXY(120, 215);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
        } else {
            // Manejar Contenedores Extra
            if ($espaciosExtra == 0) {
                // Primer espacio extra
                $pdf->SetXY(60, 247);
                $pdf->Cell(0, 10, "$Contenedor", 0, 1);

                $pdf->SetXY(30, 247);
                $pdf->Cell(0, 10, "$Cantidad", 0, 1);
                $espaciosExtra++;
            } elseif ($espaciosExtra == 1) {
                // Segundo espacio extra
                $pdf->SetXY(155, 235); // Ajusta a la posición del segundo espacio
                $pdf->Cell(0, 10, "$Contenedor", 0, 1);

                $pdf->SetXY(120, 235);
                $pdf->Cell(0, 10, "$Cantidad", 0, 1);
                $espaciosExtra++;
            } else {
                // Mostrar aviso si hay más de dos
                $pdf->SetXY(155, 247); // Ajusta la posición para la advertencia
                $pdf->Cell(0, 10, "Otros: $Contenedor ($Cantidad)", 0, 1);
            }
        }
    }

    // Número de impresión (Solo si NO es la última página)
    if ($i <= $suma_total_contenedores) {
        $pdf->SetXY(65, 264);
        $pdf->Cell(0, 10, "$i / $suma_total_contenedores", 0, 1);
    }

    /// Número de paquetes
    $pageHeight = $pdf->GetPageHeight();
    $y = $pageHeight - 30; // 15mm desde el borde inferior

    $pdf->SetXY(25, $y);
    $pdf->Cell(0, 5, "$suma_total_contenedores", 0, 1);
}

/// ----------- Ultimo Doc

$pdf->AddPage();
$pdf->setSourceFile("PlantillaImpresion.pdf");
$template = $pdf->importPage(1);
$pdf->useTemplate($template, 0, 0, 220);


// Definir fuente
$pdf->SetFont('Arial', 'B', 16);

// Número de Folio
$pdf->SetXY(170, 67); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, $Id_Salida, 0, 1);

// Número de Folio
$pdf->SetXY(160, 55); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, "$Fecha_Registro", 0, 1);

// Nombre del Cliente
$pdf->SetXY(75, 80); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, "$Nombre_Cliente", 0, 1);

/// --------------------- Primera row: Info de la salida base ---------------------

$pdf->SetXY(10, 102); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, "$Id_Orden_Venta", 0, 1);

$pdf->SetXY(75, 102); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, "$Id_Entrega", 0, 1);

$pdf->SetXY(140, 102); // (x, y) ajusta según el diseño
$pdf->Cell(0, 10, "$Partida", 0, 1);

/// --------------------- Continuación de Rows: Info de la salidas Fusionadas ---------------------

$y = 115; // Posición inicial de la primera fila

foreach ($Arreglo_de_salidas_fusionadas as $Id_Relacion_Salida) {
    $Id_Orden_Venta_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][0];
    $Id_Entrega_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][1];
    $Partida_Fusionada = $Arreglo_de_entregas_fusionadas[$Id_Relacion_Salida][2];

    $pdf->SetXY(10, $y); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Id_Orden_Venta_Fusionada", 0, 1);

    $pdf->SetXY(75, $y); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Id_Entrega_Fusionada", 0, 1);

    $pdf->SetXY(140, $y); // (x, y) ajusta según el diseño
    $pdf->Cell(0, 10, "$Partida_Fusionada", 0, 1);

    $y += 10; // Ajustar la posición de la siguiente fila
}


foreach ($Arreglo_de_entregas_consolidadas as $entrega) {
    $Id_Orden_Venta_Consolidada = $entrega[0];
    $Id_Entrega_Consolidada = $entrega[1];
    $Partida_Consolidada = $entrega[2];

    $pdf->SetXY(10, $y);
    $pdf->Cell(0, 10, "$Id_Orden_Venta_Consolidada", 0, 1);

    $pdf->SetXY(75, $y);
    $pdf->Cell(0, 10, "$Id_Entrega_Consolidada", 0, 1);

    $pdf->SetXY(140, $y);
    $pdf->Cell(0, 10, "$Partida_Consolidada", 0, 1);

    $y += 10;
}


/// --------------------- Continuación de Rows: Info de los Contenedores ---------------------

// Initialize an associative array to group quantities by Contenedor
$suma_por_contenedor = [];

// Iterate through the array to sum quantities
foreach ($Arreglo_de_ContenidoEmpaque as $Contenido) {
    $Contenedor = $Contenido['Contenedor'];
    $Cantidad = $Contenido['Cantidad'];

    // If the Contenedor already exists in the array, add the Cantidad
    if (isset($suma_por_contenedor[$Contenedor])) {
        $suma_por_contenedor[$Contenedor] += $Cantidad;
    } else {
        // Otherwise, initialize the Contenedor with the current Cantidad
        $suma_por_contenedor[$Contenedor] = $Cantidad;
    }
}

$suma_total_contenedores = 0;
// Print the summed quantities for each Contenedor
foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
    //  echo "Contenedor: $Contenedor, Cantidad Total: $Cantidad<br>";
    $suma_total_contenedores += $Cantidad;
}

//echo "<br> <strong>Suma Contenedores Totales: </strong>" . $suma_total_contenedores;

// Example for your PDF output
$espaciosExtra = 0; // Para llevar el control de los espacios ocupados

foreach ($suma_por_contenedor as $Contenedor => $Cantidad) {
    if ($Contenedor == 'Caja') {
        $pdf->SetXY(20, 215);
        $pdf->Cell(0, 10, "$Cantidad", 0, 1);
    } elseif ($Contenedor == 'Rollo') {
        $pdf->SetXY(20, 225);
        $pdf->Cell(0, 10, "$Cantidad", 0, 1);
    } elseif ($Contenedor == 'Tarima') {
        $pdf->SetXY(20, 235);
        $pdf->Cell(0, 10, "$Cantidad", 0, 1);
    } elseif ($Contenedor == 'Carrete') {
        $pdf->SetXY(120, 225);
        $pdf->Cell(0, 10, "$Cantidad", 0, 1);
    } elseif ($Contenedor == 'Paquete') {
        $pdf->SetXY(120, 215);
        $pdf->Cell(0, 10, "$Cantidad", 0, 1);
    } else {
        // Manejar Contenedores Extra
        if ($espaciosExtra == 0) {
            // Primer espacio extra
            $pdf->SetXY(60, 247);
            $pdf->Cell(0, 10, "$Contenedor", 0, 1);

            $pdf->SetXY(30, 247);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
            $espaciosExtra++;
        } elseif ($espaciosExtra == 1) {
            // Segundo espacio extra
            $pdf->SetXY(155, 235); // Ajusta a la posición del segundo espacio
            $pdf->Cell(0, 10, "$Contenedor", 0, 1);

            $pdf->SetXY(120, 235);
            $pdf->Cell(0, 10, "$Cantidad", 0, 1);
            $espaciosExtra++;
        } else {
            // Mostrar aviso si hay más de dos
            $pdf->SetXY(155, 247); // Ajusta la posición para la advertencia
            $pdf->Cell(0, 10, "Otros: $Contenedor ($Cantidad)", 0, 1);
        }
    }
}

/// Número de paquetes
$pageHeight = $pdf->GetPageHeight();
$y = $pageHeight - 30; // 15mm desde el borde inferior

$pdf->SetXY(25, $y);
$pdf->Cell(0, 5, "$suma_total_contenedores", 0, 1);

$pdf->SetXY(75, $y);
$pdf->Cell(0, 5, "1", 0, 1);

// Generar un solo archivo PDF
$pdf->Output("I", "Etiqueta_Completa_$Id_Salida.pdf");


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



/*

echo "<hr>";
echo "<h2> Información de los Arreglos de Salidas Fusionadas</h2>";
print_r($Arreglo_de_salidas_fusionadas);
*/



?>