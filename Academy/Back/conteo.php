<?php
date_default_timezone_set('America/Mexico_City');
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();

print_r($_GET);
$Nombre = $_SESSION['Name'];
$id_curso = $_GET['id_curso'];
$capitulo = $_GET['capitulo'];
$razon = $_GET['razon'];
$FechaHoy = date('Y-m-d H:i:s');
echo "<br> Razon: " . $razon;

if ($razon == 'Inicio') {

    /// Buscar si tiene ya un registro
    $query = "SELECT * FROM academy_progreso WHERE Usuario = '$Nombre' AND Curso = $id_curso AND Capitulo = $capitulo";
    $result = $conn->query($query);
    if ($result && $result->num_rows == 0) {
        // Si no existe, insertar nuevo progreso
        $sql = "INSERT INTO academy_progreso (Usuario, Curso, Capitulo, Fecha_Inicio) 
            VALUES ('$Nombre', $id_curso, $capitulo, '$FechaHoy')";
        if($conn->query($sql)){
            echo "<br> Inserción de nuevo progreso";
        }else{
            echo "<br> No se Inserto";
        }

    } else {
        $sql = "UPDATE academy_progreso SET Fecha_Inicio = '$FechaHoy' WHERE Usuario = '$Nombre' AND Curso = $id_curso AND Capitulo = $capitulo";
        if($conn->query($sql)){
            echo "<br> Actualizado Correctamente";
        }
    }
    /// Enviar a vista con la info del cap.
    header("location: ../vista.php?id_curso=$id_curso&capitulo=$capitulo");
}