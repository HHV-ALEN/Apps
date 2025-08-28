<?php
ini_set('memory_limit', '1024M'); // 1GB
date_default_timezone_set('America/Mexico_City');
require '../../vendor/autoload.php';
include "../../Back/config/config.php";
ob_start(); // Inicia el buffer de salida

$configHojas = require 'config_ventas.php';       // el mapa que hicimos antes

session_start();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$conn = connectMySQLi();

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
            if (in_array($alias, ['Precio'])) {
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
        'Cliente',
        'Precio',
    ];
    $umbralVacios = 4;

    /* Añade campos extra incluyendo la fecha */
    $extra['Fecha_Registro'] = date('Y-m-d H:i:s'); // Agrega la fecha actual

    /* Añade campos extra (excepto Fecha_Registro) */
    foreach ($registros as &$r)  $r = array_merge($r, $extra);
    unset($r);

    $campos = array_keys($registros[0]);

    // Prepara el SQL con NOW() para Fecha_Registro
    $sql = "INSERT INTO compras_cargaventas (" . implode(',', $campos) . ")
            VALUES (" . rtrim(str_repeat('?,', count($campos)), ',') . ")";

    $stmt = $conn->prepare($sql);
    $tipos = str_repeat('s', count($campos));

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

        if ($cant == 0 && $precio == 0) {
            echo "❌ Fila omitida (valores numéricos en 0)<br>";
            continue;
        }

        /* ── 3. Insertar ── */
        $stmt->bind_param($tipos, ...array_values($reg));
        $stmt->execute();

        echo "✅ {$reg['NumArticulo']}<br>";
    }

    /*  Mensaje flash */
    $_SESSION['flash'] = [
        'tipo'  => 'success',                                       // success | danger
        'texto' => "✅ Archivo procesado.<br>"
    ];


    /* Limpia el buffer antes de redireccionar */
    ob_end_clean();

    /* Redirección */
    header("Location: ../compras.php");

    $stmt->close();
}
