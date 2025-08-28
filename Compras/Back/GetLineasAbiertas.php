<?php
ini_set('memory_limit', '1024M'); // 1GB
require '../../vendor/autoload.php';
include "../../Back/config/config.php";
session_start();

$configHojas = require 'config_hojas.php';       // Mapeado de Tablas de compras

ob_start(); // Inicia el buffer de salida

$configHojas = require 'config_ventas.php';       // Mapeado de tablas de Ventas

$conn = connectMySQLi();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$FechaReg = date("Y-m-d H:i:s");

$uploadDir = __DIR__ . '/Files/';

// Crea la carpeta si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === UPLOAD_ERR_OK) {

    /* 1) Tomo la extensión original */
    $ext = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
    if (!$ext) $ext = 'xlsx';                     // fallback

    /* 2) Construyo el destino fijo */
    $destFile = $uploadDir . 'Macros.' . $ext;

    /* 3) Si ya existe, lo reemplazo */
    if (file_exists($destFile)) {
        unlink($destFile);                        // elimina el viejo
    }

    /* 4) Muevo el archivo subido */
    if (move_uploaded_file($_FILES['archivo_excel']['tmp_name'], $destFile)) {
        echo "✅ Archivo guardado como: $destFile";

        // Procesar con PhpSpreadsheet
        try {
            $spreadsheet = IOFactory::load($destFile);
            // ... tu procesamiento
        } catch (Throwable $e) {
            echo "❌ Error leyendo Excel: " . $e->getMessage();
        }
    } else {
        echo "❌ Error al mover el archivo.";
    }
} else {
    echo "⚠️ No se recibió archivo válido.";
}


if (isset($_POST['procesar'])) {

    // Limpiar la tabla antes de insertar nuevos datos:
    $conn->query("TRUNCATE TABLE compras_lineasabiertas");
     $conn->query("TRUNCATE TABLE compras_cargacompras");
      $conn->query("TRUNCATE TABLE compras_cargaventas");
    // Procesar el archivo Excel

    $archivoTmp = $_FILES['archivo_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($destFile);

    // Obtenemos la PRIMERA hoja
    $hoja = $spreadsheet->getSheet(0);
    $datos = $hoja->toArray(null, true, true, true);

    // Columnas que necesitas
    $columnasDeseadas = ['A', 'B', 'C', 'D', 'F', 'H', 'I', 'L', 'Q', 'R', 'S', 'V'];

    foreach ($datos as $index => $fila) {
        if ($index == 1) continue; // Saltar encabezado

        $OrdenCompra = $conn->real_escape_string($fila['A']);
        $Cliente = $conn->real_escape_string($fila['B']);
        $NoArticulo = $conn->real_escape_string($fila['C']);
        $CodItem = $conn->real_escape_string($fila['D']);
        $Descripcion = $conn->real_escape_string($fila['E']);
        $CodAlmacen = $conn->real_escape_string($fila['G']);
        $cantAbiertRestante = $conn->real_escape_string($fila['H']);
        $Precio = $conn->real_escape_string($fila['I']);
        $Importe = $conn->real_escape_string($fila['E']);
        $FechaConta = $conn->real_escape_string($fila['L']);
        $Titular = $conn->real_escape_string($fila['Q']);
        $Pais = $conn->real_escape_string($fila['R']);
        $OV = $conn->real_escape_string($fila['S']);
        $Comprometido = $conn->real_escape_string($fila['T']);
        $Moneda = $conn->real_escape_string($fila['V']);
        $PrecioOV = $conn->real_escape_string($fila['W']);
        $Vendedor = $conn->real_escape_string($fila['X']);
        $Utilidad = $conn->real_escape_string($fila['Y']);

        $sql = "INSERT INTO compras_lineasabiertas
        (OrdenCompra, Cliente, NumArticulo, CodItem, Descripcion, CodAlmacen,
         Cantidad_Abierta, Precio, importe, Fecha_Contabilizacion, Titular,
         Pais, OrdenVenta, Comprometido, Moneda, PrecioOv, Vendedor, Utilidad, Fecha_Registro)
        VALUES
        ('$OrdenCompra', '$Cliente', '$NoArticulo', '$CodItem', '$Descripcion', '$CodAlmacen',
         '$cantAbiertRestante', '$Precio', '$Importe', '$FechaConta', '$Titular',
         '$Pais', '$OV', '$Comprometido','$Moneda', '$PrecioOV', '$Vendedor', '$Utilidad','$FechaReg')";

        if ($conn->query($sql)) {
            //echo "✔️ Insert OK<br>";
        } else {
            //echo "❌ Error: " . $conn->error . "<br>";
        }
        // Conversión de fechas si están en formato Excel
        //$FechaConta = is_numeric($fila['L']) ? Date::excelToDateTimeObject($fila['L'])->format('Y-m-d') : null;

        //echo "<br><strong>O.C.: </strong> $OrdenCompra - <strong>Cliente: </strong> $Cliente - <strong>No. Articulo: </strong> $NoArticulo - <strong> Fecha: </strong> $FechaConta";

        //$sql = "INSERT INTO supply_compras (OrdenVenta, OrdenCompra, NombreCliente, NoDeArticulo, Descripcion, CantidadAbiertaRestante, Precio, ImportePendiente, FechaEntregaCliente, Titular, Fecha_Titular, FechaDeRegistro, Estado)
        //       VALUES ('$OrdenVenta', '$OrdenCompra', '$NombreCliente', '$NoArticulo', '$Descripcion', '$CantidadAbierta', '$Precio', '$Importe', '$FechaEntrega', '$Titular', '$FechaTitular', NOW(), 'Pendiente')";

        //$conn->query($sql);
    }


    ///_---------------------------------------------------------------------------------------------------------


    $insertados =  0;   // si los llevaste
    $omitidos   =  0;

    $ruta = '../Files/Macros.xlsm';   // Ajusta ruta/nombre
    $spreadsheet = IOFactory::load($ruta);

    $hojas = $spreadsheet->getSheetNames();   // Devuelve array con los nombres

    foreach ($hojas as $index => $nombre) {
        echo "[$index] $nombre<br>";
    }

    /* Recorremos las hojas que quieras procesar */
    foreach ($configHojas as $nombreHoja => $cfg) {

        $hoja = $spreadsheet->getSheetByName($nombreHoja);
        if (!$hoja) {
            echo "❌ Falta $nombreHoja<br>";
            continue;
        }

        $rows = leerHojaExcel($hoja, $cfg['columnas']);

        /* añadimos Pais y HojaOrigen */
        $fixed = [
            'Pais'       => $cfg['pais'],        // US / PA / GT
            'HojaOrigen' => $nombreHoja
        ];
        insertarLoteEnUnicaTabla($conn, $nombreHoja, $rows, $fixed);

        echo "✅ $nombreHoja : " . count($rows) . " filas<br>";
    }

    //echo "<div class='alert alert-success mt-3 text-center'>✅ Archivo procesado y datos guardados en BD.</div>";
    header("Location: pestañas.php"); // Redirigir a la página de proveedores
}


function leerHojaExcel(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, array $mapaColumnas): array
{
    $arrayFinal = [];
    // 5.º parámetro = true  →  devuelve la celda “ya formateada”
    $datos = $hoja->toArray(null, true, true, true);
    foreach ($datos as $i => $fila) {
        if ($i === 1) continue; // saltar encabezado

        $registro = [];
        foreach ($mapaColumnas as $alias => $colLetra) {
            $valorCelda = $fila[$colLetra] ?? '';

            /* ── Si es la columna Precio/Importe y llega como texto ───────── */
            if (in_array($alias, ['Precio', 'ImportePendiente', 'Cantidad_Abierta'])) {
                // quita símbolo de moneda, paréntesis, comas, espacios…
                $valorCelda = str_replace([',', ' ', '$', '(', ')'], '', $valorCelda);
                if ($valorCelda === '') {
                    $valorCelda = 0;
                } elseif (is_numeric($valorCelda)) {
                    $valorCelda = (float) $valorCelda;
                }
            }

            $registro[$alias] = $valorCelda;
        }
        $arrayFinal[] = $registro;
    }
    return $arrayFinal;
}

function insertarLoteEnUnicaTabla(
    mysqli $conn,
    string $hojaOrigen,
    array $registros,
    array $extra = []
): void {
    if (!$registros) return;

    /* Campos obligatorios y umbral de vacíos */
    $clavesObligatorias = [
        'OrdenCompra',
        'OrdenVenta',
        'Cliente',
        'NumArticulo',
        'CodItem',
        'Descripcion',
        'Cantidad_Abierta',
        'Precio',
        'ImportePendiente'
    ];
    $umbralVacios = 4;

    /* Añade campos extra (Pais, HojaOrigen, etc.) */
    foreach ($registros as &$r)  $r = array_merge($r, $extra);
    unset($r);

    $campos = array_keys($registros[0]);
    $place  = '(' . rtrim(str_repeat('?,', count($campos)), ',') . ')';
    $sql    = "INSERT INTO compras_cargacompras (" . implode(',', $campos) . ")
               VALUES $place";
    $stmt   = $conn->prepare($sql);
    $tipos  = str_repeat('s', count($campos));

    foreach ($registros as $reg) {

        /* ── 1. Filas casi vacías (≥ $umbralVacios) ── */
        $vacias = 0;
        foreach ($clavesObligatorias as $k) {
            if (!isset($reg[$k]) || trim((string)$reg[$k]) === '') $vacias++;
        }
        if ($vacias >= $umbralVacios) {
            echo "❌ Fila omitida (vacía)<br>";
            continue;
        }

        /* ── 2. Cantidad, precio e importe = 0 ─────────── */
        $cant   = (float)($reg['Cantidad_Abierta']  ?? 0);
        $precio = (float)($reg['Precio']            ?? 0);
        $importe = (float)($reg['ImportePendiente']  ?? 0);

        if ($cant == 0 && $precio == 0 && $importe == 0) {
            echo "❌ Fila omitida (valores numéricos en 0)<br>";
            continue;
        }

        /* ── 3. Insertar ── */
        $stmt->bind_param($tipos, ...array_values($reg));
        $stmt->execute();
        echo "✅ {$reg['OrdenCompra']} / {$reg['NumArticulo']}<br>";
    }
}
