<?php
session_start();
include("../../Back/config/config.php"); // --> Conexión con Base de datos
/// Habilitar la muestra de errores:
require '../../vendor/autoload.php';
$conn = connectMySQLi();
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\IOFactory;

$Nombre_Encuestado = isset($_GET['Nombre']) ? htmlspecialchars($_GET['Nombre']) : '';
$nombreArchivo = isset($_GET['Archivo']) ? basename($_GET['Archivo']) : '';
$periodo = isset($_GET['periodo']) ? basename($_GET['periodo']) : '';
// Validar que tengamos los parámetros necesarios
if (empty($Nombre_Encuestado) || empty($nombreArchivo)) {
    die("Faltan parámetros necesarios para la evaluación");
}

// Ruta base donde se almacenan los archivos (usa rutas absolutas para mayor seguridad)
$directorioArchivos = __DIR__ . '/../Archivos/';

// Construir la ruta completa del archivo
$rutaCompleta = $directorioArchivos . $nombreArchivo;

// Verificar si el archivo existe y es un archivo válido
$archivoExiste = false;
$extensionValida = false;

if (!empty($nombreArchivo)) {
    // Verificar extensión .xlsx
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $extensionValida = ($extension === 'xlsx');

    // Verificar que el archivo exista y sea válido
    $archivoExiste = $extensionValida && file_exists($rutaCompleta) && is_file($rutaCompleta);
}


function extraerDatosEvaluacion($rutaArchivo)
{
    //echo "<br> Archivo Procesado: " . $rutaArchivo;
    try {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();

        $datos = [];
        $filaInicio = 11;
        $ultimaResponsabilidad = '';
        $ultimaPonderacion = 0;

        for ($row = $filaInicio; $row <= $sheet->getHighestRow(); $row++) {
            $responsabilidad = $sheet->getCell('A' . $row)->getValue();
            $indicador = $sheet->getCell('B' . $row)->getValue();
            $objetivo = $sheet->getCell('C' . $row)->getValue();
            $ponderacion = $sheet->getCell('D' . $row)->getValue();

            if (empty($objetivo)) break;

            // Convertir ponderación a entero (30 en lugar de 0.3)
            if (!empty($responsabilidad) && !empty($ponderacion)) {
                // Asegurar conversión a número
                $valorNumerico = floatval($ponderacion);

                // Si viene como 0.3 (porque es celda en formato %) → 0.3 * 100 = 30
                $ultimaPonderacion = (int)($valorNumerico * 100);

                $ultimaResponsabilidad = $responsabilidad;
            }

            $datos[] = [
                'responsabilidad' => $ultimaResponsabilidad,
                'indicador' => $indicador,
                'objetivo' => $objetivo,
                'ponderacion' => $ultimaPonderacion
            ];
        }

        return $datos;
    } catch (Exception $e) {
        error_log("Error al leer archivo Excel: " . $e->getMessage());
        return [];
    }
}


$datosEvaluacion = [];
if ($archivoExiste) {
    $datosEvaluacion = extraerDatosEvaluacion($rutaCompleta);
}

$Responsable = $_GET['Nombre'];
$Periodo = $_GET['periodo'];

$sql_Evidencias = "SELECT Nombre_Archivo FROM rio_evidencias 
                   WHERE Responsable = '$Responsable' 
                   AND Periodo = '$Periodo'";
$result = $conn->query($sql_Evidencias);

$archivos = [];
while ($row = $result->fetch_assoc()) {
    $archivos[] = $row['Nombre_Archivo'];
}

/* print_r($archivos);
echo "<pre>";

print_r($datosEvaluacion);

echo "</pre>"
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Evaluación</title>
</head>

<body>
    <?php include "../../Front/navbar.php"; ?>
    <form method="post" action="../Back/guardar_evaluacion.php?periodo=<?= $periodo ?>">
        <div class="container py-4">

            <!-- Encabezado -->
            <div class="text-center mb-5">
                <h3 class="fw-bold text-dark">
                    Evaluación RIO de <span class="text-primary"><?= $Nombre_Encuestado; ?></span>
                </h3>
                <p class="text-muted">Periodo: <?= $periodo; ?></p>
                <button id="abrirArchivos" type="button" class="btn btn-outline-primary btn-sm mb-3">
                    <i class="bi bi-folder2-open"></i> Abrir Evidencias
                </button>
                <hr class="mt-4">
            </div>

            <?php
            // Agrupar objetivos por responsabilidad
            $agrupados = [];
            foreach ($datosEvaluacion as $item) {
                $agrupados[$item['responsabilidad']][] = $item;
            }

            // Dibujar bloques por responsabilidad
            foreach ($agrupados as $nombre_responsabilidad => $objetivos) {
                $totalObjetivos = count($objetivos);
                $ponderacion_total = $objetivos[0]['ponderacion']; // misma en todos
            ?>

                <!-- Card por responsabilidad -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-clipboard-check text-primary"></i>
                            <?= htmlspecialchars($nombre_responsabilidad) ?>
                        </h5>
                        <span class="badge bg-primary fs-6">
                            Ponderación: <?= $ponderacion_total ?>%
                        </span>
                    </div>
                    <div class="card-body">

                        <?php foreach ($objetivos as $index => $objetivo):
                            $peso_individual = $ponderacion_total / $totalObjetivos;
                        ?>
                            <div class="row align-items-center mb-3 evaluacion-item"> <!-- MOVÍ LA CLASE AQUÍ -->
                                <div class="col-md-8">
                                    <strong><?= $objetivo['objetivo'] ?></strong><br>
                                </div>
                                <div class="col-md-4">
                                    <input type="number"
                                        name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][calificacion]"
                                        class="form-control" min="0" max="100" placeholder="0 - 100" required
                                        oninput="validarRango(this)">

                                    <!-- Justificación -->
                                    <input type="text"
                                        name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][justificacion]"
                                        class="form-control mt-2 justificacion-input"
                                        placeholder="Justifique calificación baja"
                                        style="display:none;">

                                    <!-- Hidden fields -->
                                    <input type="hidden" name="Nombre_Encuestado" value="<?= $Nombre_Encuestado ?>">
                                    <input type="hidden" name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][indicador]" value="<?= $objetivo['indicador']; ?>">
                                    <input type="hidden" name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][ponderacion]" value="<?= $ponderacion_total ?>">
                                    <input type="hidden" name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][peso]" value="<?= $peso_individual ?>">
                                    <input type="hidden" name="evaluacion[<?= $nombre_responsabilidad ?>][<?= $index ?>][objetivo]" value="<?= htmlspecialchars($objetivo['objetivo']) ?>">
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>
            <?php } ?>

            <!-- Botón guardar -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-success px-5 py-2 shadow-sm">
                    <i class="bi bi-save"></i> Guardar Evaluación
                </button>
            </div>

        </div>
    </form>


    <script>
        function validarRango(input) {
            let valor = parseInt(input.value);

            // Correcciones de límites
            if (valor > 100) {
                input.value = 100;
                valor = 100;
            } else if (valor < 0) {
                input.value = 0;
                valor = 0;
            } else if (isNaN(valor)) {
                input.value = '';
                valor = null;
            }

            // Buscar dentro del mismo contenedor .evaluacion-item
            const contenedor = input.closest('.evaluacion-item');
            const justificacionInput = contenedor.querySelector('.justificacion-input');

            if (valor !== null && valor <= 50) {
                justificacionInput.style.display = 'block';
                justificacionInput.required = true;
            } else {
                justificacionInput.style.display = 'none';
                justificacionInput.required = false;
                justificacionInput.value = ''; // limpiar si no se necesita
            }
        }
    </script>


</body>

</html>