<?php
// /Ia/back/procesamiento.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php'; // Asegúrate de que apunta bien
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

// 1. Obtenemos el archivo subido
if (isset($_FILES['archivo_incidencias'])) {
    $archivo = $_FILES['archivo_incidencias'];
    $nombre_Archivo = $archivo['name'];
    $archivo_tmp = $archivo['tmp_name'];
    echo "Archivo recibido: $nombre_Archivo\n";
    // 2. Ruta donde lo vamos a guardar
    $ruta_destino = "uploads/" . $nombre_Archivo;
    echo "Archivo movido a: $ruta_destino\n";
    // 3. Movemos el archivo temporal a la carpeta 'uploads'
    move_uploaded_file($archivo_tmp, $ruta_destino);

    // 4. Ruta absoluta para pasar al script de Python
    $ruta_absoluta = getcwd() . "/" . $ruta_destino;

    $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);


    if ($ext !== 'xlsx') {
        die("Solo se permiten archivos .xlsx");
    }
    echo "Cargando archivo Excel...\n";
    $spreadsheet = IOFactory::load($ruta_destino);
    $hoja = $spreadsheet->getActiveSheet();
    $datos = $hoja->toArray();

    $exito = 0;
    $errores = 0;

    echo "Procesando filas...\n";
    // Saltamos la cabecera
    for ($i = 1; $i < count($datos); $i++) {
        $fila = $datos[$i];

        // Validación básica
        if (count($fila) < 8) {
            $errores++;
            echo "Fila $i incompleta. Se omite.\n";
            continue;
        }

        list($fecha, $Soporte, $Usuario_incidente, $Incidente, $prioridad, $responsable, $estatus, $observaciones) = $fila;

        $mes = date('m', strtotime($fecha));
        $anio = date('Y', strtotime($fecha));
        $fecha_formateada = date('Y-m-d', strtotime($fecha));

        echo "Insertando fila $i: $fecha_formateada, $Soporte, $Incidente\n";

        // Preparamos la consulta segura
        $stmt = $conn->prepare("INSERT INTO ia_incidencias 
        (Fecha, Soporte, Incidente, Prioridad, Usuario, Estatus, Responsable, Observaciones, Mes, Anio, Archivo_original) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            echo "Error al preparar statement para fila $i\n";
            $errores++;
            continue;
        }

        // Tipos:        s     s      s       i     s     s      s       s     s   i     s
        $stmt->bind_param(
            "sssisssssis",
            $fecha_formateada,
            $Soporte,
            $Incidente,
            $prioridad,
            $Usuario_incidente,
            $estatus,
            $responsable,
            $observaciones,
            $mes,
            $anio,
            $nombre_Archivo
        );

        if ($stmt->execute()) {
            $exito++;
            echo "Fila $i insertada correctamente.\n";
        } else {
            echo "Error al insertar fila $i: " . $stmt->error . "\n";
            $errores++;
        }

        $stmt->close();
    }


    echo "\nResumen:\n$exito filas insertadas correctamente. $errores errores.\n";


    // Guardamos el mensaje para la siguiente página
    $_SESSION['status'] = ($exito > 0) ? 'success' : 'error';
    $_SESSION['message'] = "$exito filas insertadas correctamente. $errores errores.";

    echo "\nEjecutando script de Python...\n";
    // 5. Ejecutamos el script de Python con la ruta del archivo
    $comando = "python procesamiento.py \"$ruta_absoluta\"";

    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];

    $process = proc_open($comando, $descriptorspec, $pipes);

    if (is_resource($process)) {
        // Cierra la entrada
        fclose($pipes[0]);

        echo "<pre>";
        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line !== false) {
                echo htmlspecialchars($line);
                ob_flush();
                flush(); // 👈 ¡Para que lo veas en tiempo real!
            }
        }
        fclose($pipes[1]);

        // También mostramos errores (stderr)
        while (!feof($pipes[2])) {
            $line = fgets($pipes[2]);
            if ($line !== false) {
                echo "<span style='color:red;'>" . htmlspecialchars($line) . "</span>";
                ob_flush();
                flush();
            }
        }
        fclose($pipes[2]);

        proc_close($process);
        echo "</pre>";
    }
    // 6. Mostramos resultados
    echo "\n--- Output del script de Python ---\n";
    echo $salida_python;
    echo "\n--- Fin del script ---\n";
    $_SESSION['analisis_resultado'] = $salida_python;

    header("Location: ../Front/Dashboard.php");
    exit();
}
