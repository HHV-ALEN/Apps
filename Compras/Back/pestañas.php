<?php
ini_set('memory_limit', '1024M'); // 1GB
require '../../vendor/autoload.php';
include "../../Back/config/config.php";
ob_start();
$configHojas = require 'config_hojas.php';       // el mapa que hicimos antes

session_start();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$conn = connectMySQLi();


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

    

//Mensaje flash 
$_SESSION['flash'] = [
    'tipo'  => 'success',                                       // success | danger
    'texto' => "✅ Archivo procesado."
];

ob_end_clean();


// Redirección al listado (o donde quieras volver) 
header("Location: pestañas_ventas.php");

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


