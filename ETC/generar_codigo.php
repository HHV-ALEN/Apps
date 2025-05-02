<?php 
require '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

$codigo = $_POST['codigo'] ?? null;
$numeroArticulo = $codigo;

// Generar código de barras como imagen (sin texto)
$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($numeroArticulo, $generator::TYPE_CODE_128);

// Crear imagen a partir del código de barras
$barcodeImage = imagecreatefromstring($barcode);
$barcodeWidth = imagesx($barcodeImage);
$barcodeHeight = imagesy($barcodeImage);

// Altura extra para el texto (20px)
$fontSize = 4; // Fuente integrada (1 a 5)
$textHeight = imagefontheight($fontSize);
$textWidth = imagefontwidth($fontSize) * strlen($numeroArticulo);

// Crear imagen final (código + espacio para texto)
$finalImage = imagecreatetruecolor($barcodeWidth, $barcodeHeight + $textHeight + 5);
$white = imagecolorallocate($finalImage, 255, 255, 255);
$black = imagecolorallocate($finalImage, 0, 0, 0);

// Fondo blanco
imagefill($finalImage, 0, 0, $white);

// Copiar código de barras
imagecopy($finalImage, $barcodeImage, 0, 0, 0, 0, $barcodeWidth, $barcodeHeight);

// Agregar el texto centrado
$textX = ($barcodeWidth - $textWidth) / 2;
$textY = $barcodeHeight + 2;
imagestring($finalImage, $fontSize, $textX, $textY, $numeroArticulo, $black);

// Mostrar imagen o guardarla
header('Content-Type: image/png');
imagepng($finalImage);

// También puedes guardarla:
file_put_contents($numeroArticulo . '.png', $barcode);
imagepng($finalImage, '../codigos_barras/' . $numeroArticulo . '_text.png');

// Liberar memoria
imagedestroy($barcodeImage);
imagedestroy($finalImage);


?>
