<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../../Back/config/config.php");
$conn = connectMySQLi();
session_start();

echo "<h1>Información de la Session</h1>";
$firstname = $_SESSION['Name'];
echo "<br> Nombre: " . $firstname;
echo "<h1>Información del POST</h1>";
$id_salida = $_POST['id_salida'] ?? $_GET['id_salida'];
echo "<br> ID Salida: " . $id_salida;

// Rebir archivo de imagen
echo "<h1>Información del Archivo</h1>";
$target_dir = "../Files/img/";
$uploaded_files = $_FILES['imagenes']['name'];
$Fecha = date("Y-m-d H:i:s");

print_r($uploaded_files);

// Iterar sobre los archivos

foreach ($uploaded_files as $key => $Image) {
    echo "<br> Imagen: " . $Image;
    // Verificar si existe l Archivo dentro de la carpeta "target_dir"
    if (file_exists($target_dir . $Image)) {
        //header("Location: ../detalles.php?id_salida=$id_salida");
        echo "<br> El archivo ya existe";

        // Insertar el registro en la base de datos
        $query = "INSERT INTO imagen (id_salida, nombre, status, Responsable)
        VALUES ('$id_salida', '$Image', 'Activo', '$firstname')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo "<br> Registro insertado correctamente";

            // Actualizar Bitacora
            $Bitacora_Query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
('$id_salida', 'Se Registro la Imagen: $Image', '$Fecha', '$firstname')";
            $Bitacora_Result = mysqli_query($conn, $Bitacora_Query);
            if (!$Bitacora_Result) {
                die('Query Failed Bitacora.');
            } else {
                echo "Se insertó correctamente en la tabla Bitacora<br>";
            }
            /// header a: ../detalles2.php?id_salida=$id_salida
            //header("Location: ../../Front/detalles.php?id=".$id_salida);
        } else {
            echo "<br> Error al insertar el registro";
        }
    } else {
        $archivoTemporal = $_FILES['imagenes']['tmp_name'][$key];
        $archivoDestino = $target_dir . $Image;

        // Llama a la función que comprime
        if (comprimirImagen($archivoTemporal, $archivoDestino, 50)) {
            echo "<br> La imagen $Image ha sido comprimida y guardada correctamente";
        } else {
            echo "<br> Hubo un error al comprimir la imagen $Image";
        }
        // Insertar el registro en la base de datos
        $query = "INSERT INTO imagen (id_salida, nombre, status, Responsable)
            VALUES ('$id_salida', '$Image', 'Activo', '$firstname')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            echo "<br> Registro insertado correctamente";

            // Actualizar Bitacora
            $Bitacora_Query = "INSERT INTO bitacora (Id_Salida, Accion, Fecha, Responsable) VALUES
    ('$id_salida', 'Se Registro la Imagen: $Image', '$Fecha', '$firstname')";
            $Bitacora_Result = mysqli_query($conn, $Bitacora_Query);
            if (!$Bitacora_Result) {
                die('Query Failed Bitacora.');
            } else {
                echo "Se insertó correctamente en la tabla Bitacora<br>";
            }
            /// header a: ../detalles2.php?id_salida=$id_salida
            //header("Location: ../../Front/detalles.php?id=".$id_salida);
        } else {
            echo "<br> Error al insertar el registro";
        }
    }
}



function comprimirImagen($origen, $destino, $calidad)
{
    $info = getimagesize($origen);

    if ($info['mime'] == 'image/jpeg') {
        $imagen = imagecreatefromjpeg($origen);
        imagejpeg($imagen, $destino, $calidad);
    } elseif ($info['mime'] == 'image/png') {
        $imagen = imagecreatefrompng($origen);
        // Convertimos PNG a JPEG para mayor compresión (opcional)
        imagejpeg($imagen, $destino, $calidad);
    } elseif ($info['mime'] == 'image/webp') {
        $imagen = imagecreatefromwebp($origen);
        imagewebp($imagen, $destino, $calidad);
    }
    return $destino;
}


header("Location: ../../Front/detalles.php?id=" . $id_salida);
