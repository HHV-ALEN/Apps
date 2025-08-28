<?php
ob_start();
$total_final = 0;
session_start();
include "../../Back/config/config.php";


$conn = connectMySQLi();

echo "<pre>";
print_r($_POST['evaluacion']);
echo "</pre>";

$resultados = [];

$Nombre_Encuestado = $_POST['Nombre_Encuestado'];
$Nombre_Encuestador = $_SESSION['Name'];
echo "<hr>";
echo "<br> <strong>Nombre Encuestado:</strong> " . $Nombre_Encuestado;
echo "<br> <strong>Nombre Encuestador:</strong> " . $Nombre_Encuestador;

$periodo = $_GET['periodo'];
echo "<br> <strong> periodo:</strong> " . $periodo;

foreach ($_POST['evaluacion'] as $responsabilidad => $items) {
    $total_ponderacion = 0;
    $resultado_final = 0;

    foreach ($items as $item) {
        $Objetivo = $item['objetivo'];
        $indicador = $item['indicador'];
        $Justificacion = $item['justificacion'];
        $calificacion = floatval($item['calificacion']);
        $peso = floatval($item['peso']); // ← ahora sí viene del form
        $ponderacion = floatval($item['ponderacion']);

        $valor = ($calificacion / 100) * $peso;

        $resultado_final += $valor;
        $total_ponderacion = $ponderacion;

        echo "<h1> Información de Registros:</h1> ";
        echo "<br><strong>Responsabilidad: </strong>" . $responsabilidad;
        echo "<br><strong>Indicador: </strong>" . $indicador;
        echo "<br><strong>Objetivo: </strong>" . $Objetivo;
        echo "<br><strong>Ponderacion Total: </strong>" . $ponderacion;
        echo "<br><strong>Peso Individual: </strong>" . $peso;
        echo "<br><strong>Justificación: </strong>" . $Justificacion;
        echo "<br><strong>Calificacion: </strong>" . $calificacion;
        echo "<hr>";


        echo "<strong>Resultado final ($responsabilidad): </strong>" . $resultado_final;

        // 3. Preparar la consulta
        $stmt = $conn->prepare("
    INSERT INTO rio_objetivos_individuales 
    (Nombre_Evaluador, Nombre_Evaluado, Calificacion_Obtenida, Justificacion, Responsabilidad, Indicador, Objetivo, Ponderacion, Peso, Peso_Obtenido, Periodo)
    VALUES (?,?,?,?,?,?,?,?,?,?,?) ");

    /*
        Nombre_Evaluador, = s
        Nombre_Evaluado, = s
        Calificacion_Obtenida, = d 
        Justificacion,  = s
        Responsabilidad, = s
        Indicador, = s
        Objetivo, = s
        Ponderacion, = d
        Peso, = d
        Peso_Obtenido, = d
         Periodo = s


    */
        // 4. Vincular parámetros
        // Cambia de "sssdsssd" a "sssdsssddds" (11 caracteres)
$stmt->bind_param(
    "sssssssddds", // Ahora son 11 tipos para 11 variables
    $Nombre_Encuestador,
    $Nombre_Encuestado,
    $calificacion,
    $Justificacion,
    $responsabilidad,
    $indicador,
    $Objetivo,
    $ponderacion,    // d (double)
    $peso,           // d (double) 
    $valor,          // d (double)
    $periodo         // s (string)
);
    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar: " . $stmt->error;
        }


        $resultados[$responsabilidad] = [
            'responsabilidad' => $responsabilidad,
            'obtenido' => round($resultado_final, 2),
            'total' => $total_ponderacion,
            'porcentaje' => round(($resultado_final / $total_ponderacion) * 100, 2)
        ];
        
    }
}



$total_general = 0;

foreach ($resultados as $res) {
    $total_general += $res['obtenido']; // suma los puntos ganados en cada responsabilidad

    //echo "<pre>";
    //print_r($res);
    //echo " </pre>";

    $responsabilidad = $res['responsabilidad'];
    $obtenido = $res['obtenido'];
    $total = $res['total'];
    $porcentaje = $res['porcentaje'];

    echo "<br> <strong> Responsabilidad </strong> " .  $responsabilidad;
    echo "<br> <strong> obtenido </strong> " .  $obtenido;
    echo "<br> <strong> total </strong> " .  $total;
    echo "<br> <strong> porcentaje </strong> " .  $porcentaje;

    $stmt = $conn->prepare("
    INSERT INTO rio_resultado_general
    (Nombre_Evaluado, Nombre_Evaluador, Responsabilidad, Obtenido, Total_Categoria, Porcentaje_Obtenido, Periodo) 
    VALUES (?,?,?,?,?,?, ?) ");

    // 4. Vincular parámetros
    $stmt->bind_param(
        "sssdids", // Tipos de datos (s=string, d=double, i=integer)
        $Nombre_Encuestado,
        $Nombre_Encuestador,
        $responsabilidad,
        $obtenido,
        $total,
        $porcentaje,
        $periodo
    );


    if ($stmt->execute()) {
        echo "Datos insertados correctamente.";
    } else {
        echo "Error al insertar: " . $stmt->error;
    }
}
header('Location: ../Front/resultados.php?NombreEvaluado=' . urlencode($Nombre_Encuestado) . '&Periodo=' . urlencode($periodo));
ob_end_flush();

// 6. Cerrar conexión
$stmt->close();
$conn->close();