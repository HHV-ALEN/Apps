<?php 
include "../Back/config/config.php";
require '../vendor/autoload.php'; // Asegúrate de que la ruta sea correcta


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

session_start();
$conn = connectMySQLi();

$pais = $_GET['pais'] ?? 'ALL'; 

/* 2️⃣ Consulta -------------------------------------------------------- */
if ($pais === 'ALL') {
    $sql = "SELECT * FROM compras_cargacompras ORDER BY Pais, OrdenCompra";
    $stmt = $conn ->query($sql);
} else {
    $sql  = "SELECT * FROM compras_cargacompras WHERE Pais = ? ORDER BY OrdenCompra";
    $stmt = $conn ->prepare($sql);
    $stmt->bind_param('s', $pais);
    $stmt->execute();
    $stmt = $stmt->get_result();
}

/* 3️⃣ Crear hoja de cálculo ------------------------------------------ */
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Compras');

/* Encabezados */
$encabezados = [
    'A1' => 'ID',      'B1' => 'Orden Compra', 'C1' => 'Cliente',
    'D1' => 'Artículo', 'E1' => 'Código item', 'F1' => 'Descripción',
    'G1' => 'Cant.',   'H1' => 'Precio',       'I1' => 'Importe',
    'J1' => 'F. Contab.', 'K1' => 'Pais'
];
foreach ($encabezados as $celda => $texto) {
    $sheet->setCellValue($celda, $texto);
}

/* Estilo encabezado */
$headerStyle = [
    'font' => ['bold'=>true, 'color'=>['rgb'=>'FFFFFF']],
    'fill' => ['fillType'=>Fill::FILL_SOLID, 'startColor'=>['rgb'=>'4472C4']],
];
$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

/* Datos */
$fila = 2;
while ($r = $stmt->fetch_assoc()) {
    $sheet->fromArray([
        $r['Id'],
        $r['OrdenCompra'],
        $r['Cliente'],
        $r['NumArticulo'],
        $r['CodItem'],
        $r['Descripcion'],
        $r['Cantidad_Abierta'],
        $r['Precio'],
        $r['ImportePendiente'],
        $r['FechaContabiliz'],
        $r['Pais']
    ], null, "A$fila");
    $fila++;
}

/* Formato numérico */
$sheet->getStyle("H2:H{$fila}")
      ->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle("I2:I{$fila}")
      ->getNumberFormat()->setFormatCode('#,##0.00');

/* Auto‑ajustar ancho */
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* 4️⃣ Descargar ------------------------------------------------------- */
$nombreArchivo = 'compras_' . strtolower($pais) . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;


?>