<?php
require_once("../../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
date_default_timezone_set('America/Mexico_City');
$conn = connectMySQLi();
session_start();

print_r($_GET);

$correcta = $_GET['correcta'];

if($correcta){
    echo "<br> Entra por que es correcta";
} else {
     echo "<br> No Entra por que es correcta";
}

$id_pregunta = $_GET['pregunta_id'];
$respuesta = $_GET['respuesta'];
$Id_curso = $_GET['curso_id'];
$Id_Capitulo = $_GET['capitulo_id'];
$Usuario = $_SESSION['Name'];
$Fecha_hoy = date("Y-m-d H:i:s");

echo "<br> Id_pregunta: " . $id_pregunta;
echo "<br> Respuesta: " . $respuesta;
echo "<br> Id_curso: " . $Id_curso;
echo "<br> Id_Capitulo: " . $Id_Capitulo;

/// ETAPA DE VERIFICACIÓN - ¿ES LA RESPUESTA CORRRECTOA??
$queryRespuesta = "SELECT * FROM academy_respuestas WHERE Pregunta = $id_pregunta AND Es_Correcta = 1";
$resultRespuesta = mysqli_query($conn, $queryRespuesta);
$respuestaCorrecta = mysqli_fetch_array($resultRespuesta);
$correcta = $respuestaCorrecta['Respuesta'];
$pregunta = $respuestaCorrecta['Pregunta'];

echo "<br> - Respuesta Correcta: " . $correcta;
echo "<br> - Respuesta Seleccionada: " . $respuesta;

// segunda revisión - correcta:
if (strcmp($respuesta, $correcta) === 0) {
    echo "<br>Coorrecto";
    // Cuando es la correcta, se guarda en academy_progreso (Usuario, Curso, Capitulo, Completado, Fecha_Completado)
    echo "<br> Progreso guardado correctamente";
    /// Consultar por el siguiente capitulo
    echo "<br> Id Curso: " . $Id_curso;
    echo "<br> Id: " . $Id_Capitulo;


    $queryCapitulo = "SELECT * FROM academy_capitulos WHERE Id_Curso = $Id_curso AND Id > $Id_Capitulo ORDER BY Id ASC LIMIT 1";
    $resultCapitulo = mysqli_query($conn, $queryCapitulo);
    if (mysqli_num_rows($resultCapitulo) > 0) {

        echo "<br> - Entro porque si hay ese capitulo";
        $capituloSiguiente = mysqli_fetch_array($resultCapitulo);
        $Id_Capitulo_Siguiente = $capituloSiguiente['Id'];

        echo "<br><br> Capítulo siguiente: " . $Id_Capitulo_Siguiente;
        // Redirigir al siguiente capítulo


        /// Actualizar la tabla academy_progreso ->Completado = 1, Fecha_Completado = NOW(), Fecha_fin = NOW()
        $sql_update_progress = "UPDATE academy_progreso SET Completado = 1, Fecha_Completado = '$Fecha_hoy', Fecha_fin = '$Fecha_hoy' WHERE Usuario = '$Usuario' AND Curso = $Id_curso AND Capitulo = $Id_Capitulo";
        if ($conn->query($sql_update_progress) === TRUE) {
            echo "<br> <strong>Nombre de cliente actualizado correctamente en la tabla salidas</strong>";
            /// Insertar registro con la respuesta y detalles
        } else {
            echo "<br> <strong>Error al actualizar el nombre de cliente en la tabla salidas: </strong>" . $conn->error;
        }

        /// GuardarRespuestaCorrecta
        $insert_Response = "INSERT INTO academy_responses (Nombre, Pregunta, Respuesta,Estado, Fecha, Curso, Capitulo) 
            VALUES ('$Usuario', $pregunta, '$respuesta','Correcto', '$Fecha_hoy', $Id_curso, $Id_Capitulo)";
        if ($conn->query($insert_Response)) {
            echo "<br><strong>Registrada: Respuesta Correcta</strong>";
        } else {
            echo "<br><strong>No Registrada la Respuesta Correcta</strong>";
        }

        header("Location: ../../vista.php?id_curso=$Id_curso&capitulo=$Id_Capitulo_Siguiente");
        exit();
    } else {
        // Aqui realizar la inserción 

                /// Actualizar la tabla academy_progreso ->Completado = 1, Fecha_Completado = NOW(), Fecha_fin = NOW()
        $sql_update_progress = "UPDATE academy_progreso SET Completado = 1, Fecha_Completado = '$Fecha_hoy', Fecha_fin = '$Fecha_hoy' WHERE Usuario = '$Usuario' AND Curso = $Id_curso AND Capitulo = $Id_Capitulo ";
        if ($conn->query($sql_update_progress) === TRUE) {
            echo "<br> <strong>Nombre de cliente actualizado correctamente en la tabla salidas</strong>";
            /// Insertar registro con la respuesta y detalles
        } else {
            echo "<br> <strong>Error al actualizar el nombre de cliente en la tabla salidas: </strong>" . $conn->error;
        }

        /// GuardarRespuestaCorrecta
        $insert_Response = "INSERT INTO academy_responses (Nombre, Pregunta, Respuesta,Estado, Fecha, Curso, Capitulo) 
            VALUES ('$Usuario', $pregunta, '$respuesta','Correcto', '$Fecha_hoy', $Id_curso, $Id_Capitulo)";
        if ($conn->query($insert_Response)) {
            echo "<br><strong>Registrada: Respuesta Correcta</strong>";
        } else {
            echo "<br><strong>No Registrada la Respuesta Correcta</strong>";
        }



        echo "<br> No hay más capítulos";
        // Si el ultimo capitulo se encuentra Terminado
        $Verify_Last_Cap_Is_Finished = "SELECT * FROM academy_progreso WHERE Usuario = '$Usuario' AND Capitulo = $Id_Capitulo AND Curso = $Id_curso AND Completado = 1";
        $resultVerify = mysqli_query($conn, $Verify_Last_Cap_Is_Finished);
        if (mysqli_num_rows($resultCapitulo) > 1) { 
            header("Location: ../../Final.php?id_curso=$Id_curso");
        }else {
            header("Location: ../../vista.php?id_curso=$Id_curso&capitulo=$Id_Capitulo");
        }

       
        // exit();
    }
        
} 
