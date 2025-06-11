<?php
include "../Back/config/config.php";
session_start();
$conn = connectMySQLi();
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
            background-color: #007bff;
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
    <div class="container my-5">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">📚 Gestión de Cursos</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCurso">
                ➕ Nuevo Curso
            </button>
        </div>

        <!-- Tabla de cursos -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Área</th>
                        <th>Capítulos</th>
                        <th>Status</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cursos_query = "SELECT * FROM academy_cursos";
                    $cursos_response = mysqli_query($conn, $cursos_query);
                    while ($row = mysqli_fetch_assoc($cursos_response)) {
                        $idCurso = $row['Id_Curso'];
                    ?>
                        <tr>
                            <td><?= $idCurso ?></td>
                            <td><?= $row['Titulo'] ?></td>
                            <td><?= $row['Descripcion'] ?></td>
                            <td><?= $row['Area'] ?></td>
                            <td>
                                <span class="badge <?= $row['Estado'] == 'Activo' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $row['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <!-- Botón para colapsar info -->
                                <button class="btn btn-info btn-sm btn-custom" data-bs-toggle="collapse" data-bs-target="#collapseCurso<?= $idCurso ?>" aria-expanded="false" aria-controls="collapseCurso<?= $idCurso ?>">
                                    👁️ Detalle
                                </button>
                                <a class="btn btn-primary" href="vista2.php?id_curso=<?php echo $idCurso; ?>&capitulo=1" role="button">
                                    ➕ Capítulo
                                </a>
                            </td>
                        </tr>

                        <!-- Fila colapsable (info del curso) -->
                        <tr class="collapse-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse" id="collapseCurso<?= $idCurso ?>">
                                    <div class="p-4 bg-light border rounded shadow-sm animate__animated animate__fadeIn">
                                        <h6>📘 Información del curso:</h6>
                                        <p><strong>Descripción:</strong> <?= $row['Descripcion'] ?></p>
                                        <p><strong>Área:</strong> <?= $row['Area'] ?></p>
                                        <p><strong>Duración estimada:</strong> <?= $row['Duracion'] ?? '2 hrs' ?></p>
                                        <hr>
                                        <h6>📂 Capítulos:</h6>
                                        <ul>
                                            <?php
                                            // Consulta de capítulos si tienes tabla relacionada
                                            $capQuery = "SELECT * FROM academy_capitulos WHERE Id_Curso = $idCurso";
                                            $capResult = mysqli_query($conn, $capQuery);
                                            if (mysqli_num_rows($capResult) > 0) {
                                                while ($cap = mysqli_fetch_assoc($capResult)) {
                                                    echo "<li><strong>{$cap['Titulo']}</strong> – {$cap['Descripcion']}</li>";
                                                }
                                            } else {
                                                echo "<li><em>Este curso aún no tiene capítulos registrados.</em></li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Registro de Nuevo Curso -->
    <div class="modal fade" id="modalNuevoCurso" tabindex="-1" aria-labelledby="modalNuevoCursoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNuevoCursoLabel">➕ Registrar Nuevo Curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Curso</label>
                            <input type="text" class="form-control" id="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Área</label>
                            <select class="form-select" id="area" required>
                                <option selected disabled>Selecciona un área</option>
                                <option>Producción</option>
                                <option>Ventas</option>
                                <option>Administración</option>
                                <option>Logística</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen para el mapa (opcional)</label>
                            <input type="file" class="form-control" id="imagen">
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" checked>
                            <label class="form-check-label" for="status">Activo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <hr>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>