<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();
$Area = $_SESSION['Area'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Alen Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .table thead th {
            color: white;
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }

        .btn-custom {
            min-width: 90px;
        }
    </style>
</head>

<body>
    <?php include "../Front/navbar.php"; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Mis Cursos</h2>
        <img src="Front/img/Alenitos/AlienChill.png" alt="Alien cool" style="height: 80px;" />
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover ">
            <thead class="bg-dark">
                <tr>
                    <th>Nombre del Curso</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT c.Titulo, c.Descripcion, c.Area, c.Id_Curso,
                            IFNULL(MAX(p.Capitulo), 0) AS ultimo_completado
                        FROM academy_cursos c
                        LEFT JOIN academy_progreso p ON c.Id_Curso = p.Curso AND p.Usuario = ?
                        WHERE c.Area = ?
                        GROUP BY c.Id_Curso
                    ");
                    $stmt->bind_param("ss", $usuario, $Area); // Asegúrate de tener $usuario definido
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $siguiente_capitulo = $row['ultimo_completado'] + 1; // siguiente capítulo
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Titulo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Descripcion']) . "</td>";
                            echo "<td><a class='btn btn-dark' href='dashboard.php?id_curso=" . urlencode($row['Id_Curso']) . "&capitulo=" . $siguiente_capitulo . "' class='btn btn-primary btn-custom'>Entrar al curso</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay cursos disponibles en tu área.</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>