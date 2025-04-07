<?php
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
        // Mover el archivo a la carpeta "target_dir"
        if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$key], $target_dir . $Image)) {
            echo "<br> El archivo " . $Image . " ha sido subido correctamente";

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
            echo "<br> Error al subir el archivo";
        }
    }
}
header("Location: ../../Front/detalles.php?id=".$id_salida);

?>