<?php
session_start();
include("../../Back/config/config.php"); // --> Conexión con Base de datos
/// Habilitar la muestra de errores:
require '../../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\IOFactory;

$Nombre = isset($_GET['Nombre']) ? htmlspecialchars($_GET['Nombre']) : '';
$nombreArchivo = isset($_GET['Archivo']) ? basename($_GET['Archivo']) : '';

// Validar que tengamos los parámetros necesarios
if (empty($Nombre) || empty($nombreArchivo)) {
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
    try {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();

        $datos = [];
        $filaInicio = 9;
        $ultimaResponsabilidad = '';
        $ultimaPonderacion = 0;

        for ($row = $filaInicio; $row <= $sheet->getHighestRow(); $row++) {
            $responsabilidad = $sheet->getCell('A' . $row)->getValue();
            $indicador = $sheet->getCell('B' . $row)->getValue();
            $objetivo = $sheet->getCell('C' . $row)->getValue();
            $ponderacion = $sheet->getCell('D' . $row)->getValue();
            $pesoIndividual = $sheet->getCell('E' . $row)->getValue();

            if (empty($objetivo)) break;

            // Convertir ponderación a entero (30 en lugar de 0.3)
            if (!empty($responsabilidad) && !empty($ponderacion)) {
                $ultimaPonderacion = (int)($ponderacion * 100);
                $ultimaResponsabilidad = $responsabilidad;
            }

            $datos[] = [
                'responsabilidad' => $ultimaResponsabilidad,
                'indicador' => $indicador,
                'objetivo' => $objetivo,
                'ponderacion' => $ultimaPonderacion, // 30 en lugar de 0.3
                'peso_individual' => (int)$pesoIndividual // Convertir a entero
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

// Después de leer los datos, los agrupamos
$datosAgrupados = [];
foreach ($datosEvaluacion as $item) {
    $responsabilidad = $item['responsabilidad'];
    if (!isset($datosAgrupados[$responsabilidad])) {
        $datosAgrupados[$responsabilidad] = [
            'ponderacion' => $item['ponderacion'],
            'objetivos' => []
        ];
    }
    $datosAgrupados[$responsabilidad]['objetivos'][] = $item;
}

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

    <?php if (!empty($datosAgrupados)): ?>
        <div class="card shadow rounded mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Formulario de Evaluación</h5>
            </div>
            <div class="card-body">
                <form id="formEvaluacion" method="post" action="procesar_evaluacion.php">
                    <?php foreach ($datosAgrupados as $responsabilidad => $grupo): ?>
                        <div class="responsabilidad-group mb-4 border-bottom pb-3">
                            <h5 class="d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($responsabilidad) ?></span>
                                <span class="badge bg-primary">Ponderación: <?= $grupo['ponderacion'] ?>%</span>
                            </h5>

                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Indicador</th>
                                        <th width="40%">Objetivo</th>
                                        <th width="10%">Peso</th>
                                        <th width="25%">Calificación (0-100)</th>
                                        <th width="10%">% Obtenido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grupo['objetivos'] as $index => $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['indicador']) ?></td>
                                            <td><?= htmlspecialchars($item['objetivo']) ?></td>
                                            <td><?= $item['peso_individual'] ?>%</td>
                                            <td>
                                                <input type="number"
                                                    class="form-control calificacion"
                                                    name="calificacion[<?= $responsabilidad ?>][<?= $index ?>]"
                                                    min="0"
                                                    max="100"
                                                    step="1"
                                                    data-ponderacion="<?= $grupo['ponderacion'] ?>"
                                                    data-peso="<?= $item['peso_individual'] ?>"
                                                    data-responsabilidad="<?= htmlspecialchars($responsabilidad) ?>"
                                                    oninput="validarRango(this)"
                                                    required>

                                                <script>
                                                    function validarRango(input) {
                                                        // Convertir a número
                                                        let valor = parseFloat(input.value);

                                                        // Validar rango
                                                        if (isNaN(valor)) {
                                                            input.value = '';
                                                        } else if (valor < 0) {
                                                            input.value = 0;
                                                        } else if (valor > 100) {
                                                            input.value = 100;
                                                        } else {
                                                            // Redondear si es necesario
                                                            input.value = Math.round(valor);
                                                        }

                                                        // Disparar el cálculo de resultados
                                                        calcularResultados();
                                                    }
                                                </script>
                                                <input type="hidden" name="objetivo[<?= $responsabilidad ?>][<?= $index ?>]" value="<?= htmlspecialchars($item['objetivo']) ?>">
                                            </td>
                                            <td class="text-center">
                                                <span class="resultado-objetivo"
                                                    data-responsabilidad="<?= htmlspecialchars($responsabilidad) ?>">0</span>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="text-end">
                                <strong>Resultado <?= htmlspecialchars($responsabilidad) ?>: </strong>
                                <span id="resultado-<?= md5($responsabilidad) ?>" class="fw-bold text-primary">0%</span>
                                <span> (de <?= $grupo['ponderacion'] ?>%)</span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Resultado total -->
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <h4>Resultado Total de Evaluación</h4>
                            <div id="resultadoTotal" class="display-4 fw-bold text-primary">0%</div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Evaluación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Función para calcular los resultados
            function calcularResultados() {
                let resultadosResponsabilidades = {};
                let sumaTotal = 0;
                let ponderacionMaximaTotal = 0;

                // Inicializar objetos por responsabilidad
                <?php foreach ($datosAgrupados as $responsabilidad => $grupo): ?>
                    resultadosResponsabilidades["<?= md5($responsabilidad) ?>"] = {
                        nombre: "<?= htmlspecialchars($responsabilidad) ?>",
                        suma: 0,
                        pesoTotal: 0,
                        ponderacion: <?= $grupo['ponderacion'] ?>
                    };
                <?php endforeach; ?>

                // Calcular para cada objetivo
                document.querySelectorAll('.calificacion').forEach(input => {
                    const calificacion = parseFloat(input.value) || 0;
                    const peso = parseInt(input.dataset.peso);
                    const responsabilidadHash = input.dataset.responsabilidad;

                    // Cálculo para el objetivo individual (0-100%)
                    const resultadoObjetivo = (calificacion * peso) / 100;

                    // Mostrar resultado por objetivo
                    const resultadoObjetivoElement = input.closest('tr').querySelector('.resultado-objetivo');
                    if (resultadoObjetivoElement) {
                        resultadoObjetivoElement.textContent = resultadoObjetivo.toFixed(1);
                    }

                    // Acumular por responsabilidad
                    if (resultadosResponsabilidades[responsabilidadHash]) {
                        resultadosResponsabilidades[responsabilidadHash].suma += resultadoObjetivo;
                        resultadosResponsabilidades[responsabilidadHash].pesoTotal += peso;
                    }
                });

                // Calcular y mostrar por responsabilidad
                for (const [hash, data] of Object.entries(resultadosResponsabilidades)) {

                    // Calcular porcentaje obtenido de la responsabilidad (0-100%)
                    const porcentajeResponsabilidad = (data.pesoTotal > 0) ? (data.suma / data.pesoTotal) * 100 : 0;
                    console.log("Porcentaje Por Responsabilidad: ", porcentajeResponsabilidad);

                    // Mostrar resultado para la responsabilidad
                    const resultadoElement = document.getElementById(`resultado-${hash}`);
                    if (resultadoElement) {
                        resultadoElement.textContent = `${porcentajeResponsabilidad.toFixed(1)}%`;
                    }

                    // Calcular aporte al total general (ponderado)
                    sumaTotal += (porcentajeResponsabilidad * data.ponderacion) / 100;
                    ponderacionMaximaTotal += data.ponderacion;
                }

                // Calcular resultado total
                const resultadoFinal = (ponderacionMaximaTotal > 0) ? (sumaTotal / ponderacionMaximaTotal) * 100 : 0;
                const resultadoTotalElement = document.getElementById('resultadoTotal');
                if (resultadoTotalElement) {
                    resultadoTotalElement.textContent = `${resultadoFinal.toFixed(1)}%`;
                }
            }

            // Escuchar cambios en los inputs
            document.querySelectorAll('.calificacion').forEach(input => {
                input.addEventListener('input', calcularResultados);
                input.addEventListener('change', calcularResultados);
            });

            // Calcular al cargar la página (por si hay valores precargados)
            document.addEventListener('DOMContentLoaded', calcularResultados);
        </script>
    <?php endif; ?>

</body>

</html>