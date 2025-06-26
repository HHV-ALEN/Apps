<?php
include "../Back/config/config.php";
session_start();
//print_r($_SESSION);
$conn = connectMySQLi();
//echo $_SESSION['Date'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>IA de Incidencias - AlenApps</title>
    <link rel="stylesheet" href="Front/css/IA.css">
    <link rel="icon" type="image/png" href="../Front/Img/Icono-A.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include "../Front/navbar.php"; ?>
    <br>

    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Predicción: </h5>
                        <p class="card-text">Análisis y predicción de Incidencias del area de TI. </p>
                        <a href="prediccion.php" class="btn btn-primary">Entrar - Modulo: Predicción</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Special title treatment</h5>
                        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <br>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>