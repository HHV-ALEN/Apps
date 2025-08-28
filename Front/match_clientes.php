<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
/* ---------- CONFIG ---------- */
$pdo   = new PDO(
          'mysql:host=localhost;dbname=alenapps;charset=utf8',
          'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
$file  = __DIR__.'/match.xlsx';
$scoreThreshold = 0.75;          // ≥ 0.75 se considera match “aceptable”
/* ---------------------------- */

/* PRE-CARGA clientes existentes */
$sheet    = IOFactory::load($file)->getActiveSheet();
$stmt     = $pdo->query("SELECT id, Nombre, Clave_Sap FROM clientes");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------- HELPERS ---------------- */
function normalize($str) {
    $str = iconv('UTF-8','ASCII//TRANSLIT',$str);
    $str = preg_replace('/[^A-Za-z0-9 ]/', '', $str);
    return strtoupper(trim($str));
}

function insertCliente(PDO $pdo, array $data): int
{
    $ins = $pdo->prepare(
        "INSERT INTO clientes (Nombre, RFC, Clave_Sap, Calle, Ciudad, Estado, Telefono)
         VALUES (?,?,?,?,?,?,?)"
    );
    $ins->execute([
        $data['nombre'], $data['rfc'], $data['clave'],
        $data['calle'],  $data['ciudad'], $data['estado'],
        $data['telefono']
    ]);
    return (int) $pdo->lastInsertId();
}

function insertAsignado(PDO $pdo, ?int $idCliente, string $Nombre_Cliente,string $clave, string $encargado,
                         string $metodo, float $score): void
{
    $ins = $pdo->prepare(
        "INSERT INTO clientes_asignados
         (id_cliente, Nombre_Cliente,clave_sap, encargado, match_method, match_score)
         VALUES (?,?,?,?,?,?)"
    );
    $ins->execute([$idCliente, $Nombre_Cliente ,$clave, $encargado, $metodo, $score]);
}
/* ----------------------------------------- */

/* BUFFERS PARA REPORTE HTML */
$matches = [];
$misses  = [];

/* ---------- LOOP EXCEL ---------- */
foreach ($sheet->getRowIterator(2) as $row) {

    /* === Lectura de columnas === */
    $nombreXls   = trim($sheet->getCell('A'.$row->getRowIndex())->getValue() ?: '');
    $claveXls    = trim($sheet->getCell('B'.$row->getRowIndex())->getValue() ?: '');
    $RFCXls      = trim($sheet->getCell('C'.$row->getRowIndex())->getValue() ?: '');
    $encargado   = trim($sheet->getCell('J'.$row->getRowIndex())->getValue() ?: '');
    $telefonoXls = trim($sheet->getCell('O'.$row->getRowIndex())->getValue() ?: '');
    $calleXls    = trim($sheet->getCell('Q'.$row->getRowIndex())->getValue() ?: '');
    $ciudadXls   = trim($sheet->getCell('R'.$row->getRowIndex())->getValue() ?: '');
    $estadoXls   = trim($sheet->getCell('S'.$row->getRowIndex())->getValue() ?: '');

    if ($nombreXls === '' && $claveXls === '') continue;

    /** 1) MATCH EXACTO POR CLAVE *************************************/
    if ($claveXls !== '') {
        $q = $pdo->prepare("SELECT id, Nombre FROM clientes WHERE Clave_Sap = ?");
        $q->execute([$claveXls]);
        if ($c = $q->fetch(PDO::FETCH_ASSOC)) {
            $id_cliente = $c['id'] ?? '';

            $matches[] = [
                'xls'    => $nombreXls,
                'db'     => $c['Nombre'],
                'clave'  => $claveXls,
                'method' => 'clave',
                'score'  => 1
            ];

            insertAsignado($pdo, $c['id'], $nombreXls,$claveXls, $encargado, 'clave', 1);
            continue;
        }
    }

    /** 2) MATCH “Fuzzy” POR NOMBRE **********************************/
    $best = null; $bestSc = 0; $target = normalize($nombreXls);
    foreach ($clientes as $c) {
        $cmp   = normalize($c['Nombre']);
        $dist  = levenshtein($target, $cmp);
        $max   = max(strlen($target), strlen($cmp)) ?: 1;
        $score = 1 - ($dist / $max);
        if ($score > $bestSc) { $bestSc = $score; $best = $c; }
    }

    if ($bestSc >= $scoreThreshold) {
        /* ——— match “fuzzy” encontrado ——— */
        $matches[] = [
            'xls'    => $nombreXls,
            'db'     => $best['Nombre'],
            'clave'  => $claveXls ?: $best['Clave_Sap'],
            'method' => 'nombre',
            'score'  => round($bestSc, 2)
        ];
        insertAsignado($pdo, $best['id'], $nombreXls,
                       $claveXls ?: $best['Clave_Sap'],
                       $encargado, 'nombre', $bestSc);

    } else {
        /* ================ NO MATCH ================ */
        try {
            $pdo->beginTransaction();

            /* 2.1 Insertamos cliente nuevo */
            $nuevoId = insertCliente($pdo, [
                'nombre'   => $nombreXls,
                'rfc'      => $RFCXls,
                'clave'    => $claveXls,
                'calle'    => $calleXls,
                'ciudad'   => $ciudadXls,
                'estado'   => $estadoXls,
                'telefono' => $telefonoXls
            ]);

            /* 2.2 Lo asignamos */
            insertAsignado($pdo, $nuevoId, $nombreXls,$claveXls, $encargado, 'nuevo', 0);

            $pdo->commit();

            $matches[] = [
                'xls'    => $nombreXls,
                'db'     => $nombreXls . ' (NUEVO)',
                'clave'  => $claveXls,
                'method' => 'nuevo',
                'score'  => 0
            ];

        } catch (Throwable $e) {
            $pdo->rollBack();
            $misses[] = [
                'xls'   => $nombreXls,
                'clave' => $claveXls,
                'enc'   => $encargado,
                'error' => $e->getMessage()
            ];
        }
    }
}

/* ---------- OUTPUT HTML ---------- */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Pre-match clientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body{margin:2rem}
    h2{margin-top:3rem}
    .table-sm td,.table-sm th{padding:.35rem}
</style>
</head>
<body>
<h1 class="mb-4">Vista previa de asignaciones</h1>

<!-- tabla de matches -->
<h2>Coincidencias (<?=count($matches)?>)</h2>
<table class="table table-bordered table-sm align-middle">
  <thead class="table-success">
    <tr>
      <th>Cliente Excel</th>
      <th>Cliente BD</th>
      <th>Clave SAP</th>
      <th>Método</th>
      <th class="text-end">Score</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($matches as $m): ?>
      <tr class="<?= $m['method']==='clave' ? 'table-success' : 'table-warning' ?>">
        <td><?=htmlspecialchars($m['xls'])?></td>
        <td><?=htmlspecialchars($m['db'])?></td>
        <td><?=htmlspecialchars($m['clave'])?></td>
        <td><?=htmlspecialchars($m['method'])?></td>
        <td class="text-end"><?=number_format($m['score'],2)?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- tabla de misses -->
<h2>Sin coincidencia (<?=count($misses)?>)</h2>
<table class="table table-bordered table-sm align-middle">
  <thead class="table-danger">
    <tr>
      <th>Cliente Excel</th>
      <th>Clave SAP (Excel)</th>
      <th>Encargado</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($misses as $m): ?>
      <tr class="table-danger">
        <td><?=htmlspecialchars($m['xls'])?></td>
        <td><?=htmlspecialchars($m['clave'])?></td>
        <td><?=htmlspecialchars($m['enc'])?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p class="text-muted fst-italic">*Esta vista es solo de prueba (dry-run). Cuando estés conforme, descomenta la función <code>insertMatch()</code> para insertar en <code>clientes_asignados</code>.</p>
</body>
</html>