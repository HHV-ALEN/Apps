<?php
session_start();
include "../../Back/config/config.php";

$conn = connectMySQLi();

$Nombre_Evidenciador = $_SESSION['Name'];

$responsabilidades = $_POST['responsabilidades'];
$evidencias = $_FILES['evidencias'];
$Periodo = $_POST['Periodo'];
$Fecha_hoy = date("Y-m-d H:i:s");

print_r($Periodo);
function sanitizarNombre($texto)
{
    // Paso 1: Convertir caracteres acentuados a su versión sin acento
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

    // Paso 2: Reemplazar caracteres raros (opcional, por si TRANSLIT falla)
    $texto = preg_replace('/[^a-zA-Z0-9_-]/', '', $texto); // Elimina lo que no sea alfanumérico

    return $texto;
}


// Ejemplo de uso:
$nombre_indicador = "Evaluación Óptima 123!";
$nombre_sanitizado = sanitizarNombre($nombre_indicador); // Resultado: "Evaluacion Optima 123"

foreach ($responsabilidades as $index => $nombre_indicador) {
    // Validar si hay archivos para este índice
    if (isset($evidencias['name'][$index])) {
        $total_archivos = count($evidencias['name'][$index]);

        for ($i = 0; $i < $total_archivos; $i++) {
            $nombre_archivo = $evidencias['name'][$index][$i];
            $tipo_archivo = $evidencias['type'][$index][$i];
            $tmp_archivo = $evidencias['tmp_name'][$index][$i];
            $error_archivo = $evidencias['error'][$index][$i];
            $size_archivo = $evidencias['size'][$index][$i];
            echo "<br><strong> Nombre Archivo: </strong>" . $nombre_archivo;
            echo "<br><strong> Responsabilidad: </strong>" . $responsabilidades[$index];

            // Verifica si el archivo se subió sin errores
            if ($error_archivo === UPLOAD_ERR_OK) {
                // Define ruta destino (puedes personalizar según el indicador)
                $nombre_sanitizado = sanitizarNombre($nombre_indicador);
                $nombre_Archivo_conformato = "{$nombre_sanitizado}_{$Nombre_Evidenciador}_$nombre_archivo";
                $ruta_destino = "../Archivos/Evidencias/{$nombre_sanitizado}_{$Nombre_Evidenciador}_$nombre_archivo";

                // Mueve el archivo al servidor
                if (move_uploaded_file($tmp_archivo, $ruta_destino)) {
                    // Aquí puedes guardar en la BD si gustas
                    // ejemplo: registrar_evidencia($nombre_indicador, $nombre_archivo, $ruta_destino);
                    echo "✅ Subido: $nombre_archivo para $nombre_indicador<br>";
                    /// Registrar Evidencias en BD

                    // 3. Preparar la consulta
                    $stmt = $conn->prepare("
                        INSERT INTO rio_evidencias 
                        (Responsable, Nombre_Archivo, Responsabilidad, Periodo, Fecha_Registro) 
                        VALUES (?,?,?,?,?) ");

                    // 4. Vincular parámetros
                    $stmt->bind_param(
                        "sssss", // Tipos de datos (s=string, d=double, i=integer)
                        $Nombre_Evidenciador,
                         $nombre_Archivo_conformato,
                         $responsabilidades[$index],
                         $Periodo,
                         $Fecha_hoy
                    );


                    if ($stmt->execute()) {
                        echo "Datos insertados correctamente.";
                    } else {
                        echo "Error al insertar: " . $stmt->error;
                    }
                } else {
                    echo "❌ Error al mover archivo: $nombre_archivo<br>";
                }
            } else {
                echo "❌ Error al subir archivo: $nombre_archivo (Código: $error_archivo)<br>";
            }
        }
    }
}


header('Location: ../Front/vistaAsignado.php');