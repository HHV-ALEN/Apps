<?php
session_start();
require '../vendor/autoload.php';
include "../Back/config/config.php";
$conn = connectMySQLi();
/* === País seleccionado (GET) ============ */
$paisSel = $_GET['pais'] ?? 'US';      // país elegido

$ordenCompra = trim($_GET['OrdenCompra']  ?? '');
$numArticulo = trim($_GET['NumArticulo']  ?? '');
$codItem     = trim($_GET['CodItem']      ?? '');
$Cliente = trim($_GET['Cliente'] ?? '');

$conn = connectMySQLi();               // tu función

/* 1️⃣  Tamaño de página y página solicitada */
$perPage = 50;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

/* Construyo WHERE dinámico */
$where = "WHERE Pais = '" . $conn->real_escape_string($paisSel) . "'";

if ($numArticulo !== '') {
    $na = $conn->real_escape_string($numArticulo);
    $where .= " AND NumArticulo LIKE '%$na%'";
}
if ($Cliente !== '') {
    $cli = $conn->real_escape_string($Cliente);
    $where .= " AND Cliente LIKE '%$cli%'";
}


/* 2️⃣  Consulta de TOTAL de filas (para saber cuántas páginas hay) */
$sqlTotal = "SELECT COUNT(*) AS total FROM compras_cargaventas $where";
$resTot   = $conn->query($sqlTotal);
$totalFilas = $resTot->fetch_assoc()['total'];
$totalPag   = (int)ceil($totalFilas / $perPage);

/* Consulta final */
$sql = "
  SELECT *
  FROM   compras_cargaventas
  $where
  ORDER  BY FechaContabilizacion DESC
  LIMIT  $perPage
  OFFSET $offset
";


$result = mysqli_query($conn, $sql);

$Registros = [];
while ($row = $result->fetch_assoc()) {
    $Registros[] = $row;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Líneas abiertas • <?= $paisSel ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/detalles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .table th,
        .table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .nav-pills {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 50px;
        }

        .nav-link {
            border-radius: 50px !important;
            padding: 8px 16px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .nav-link.active {
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-link.active:hover {
            background-color: #0b5ed7;
        }

        .logo {
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.1));
        }

        .nav-link.active .logo {
            filter: brightness(0) invert(1);
        }
    </style>
</head>

<body>
    <?php require '../Front/navbar.php';

    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
    ?>
        <div class="alert alert-<?= $flash['tipo'] ?> alert-dismissible fade show" role="alert">
            <?= $flash['texto'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php
        unset($_SESSION['flash']);          // se muestra una sola vez
    }


    ?>

    <div class="container my-5">

        <!-- Nav tabs mejorado con banderas y logo personalizado -->
        <ul class="nav nav-pills mb-4 justify-content-center">
            <?php
            $paises = [
                'US' => ['nombre' => 'Estados Unidos', 'bandera' => 'us', 'tipo' => 'bandera'],
                'PA' => ['nombre' => 'Panamá', 'bandera' => 'pa', 'tipo' => 'bandera'],
                'GT' => ['nombre' => 'Guatemala', 'bandera' => 'gt', 'tipo' => 'bandera'],
                'MX' => ['nombre' => 'México', 'bandera' => 'mx', 'tipo' => 'bandera']
            ];

            foreach ($paises as $codigo => $info): ?>
                <li class="nav-item me-2">
                    <a class="nav-link d-flex align-items-center <?= $paisSel == $codigo ? 'active bg-primary' : 'text-dark' ?>"
                        href="?pais=<?= $codigo ?>">
                        <?php if ($info['tipo'] === 'bandera'): ?>
                            <img src="https://flagcdn.com/16x12/<?= $info['bandera'] ?>.png"
                                alt="<?= $info['nombre'] ?>"
                                class="me-2"
                                style="width: 16px; height: 12px; object-fit: cover">
                        <?php else: ?>
                            <img src="Files/<?= $info['archivo'] ?>"
                                alt="<?= $info['nombre'] ?>"
                                class="me-2"
                                style="width: 24px; height: 24px; object-fit: contain">
                        <?php endif; ?>
                        <span><?= $info['nombre'] ?></span>
                        <?php if ($paisSel == $codigo): ?>
                            <span class="ms-2"><i class="bi bi-check-circle-fill fs-6"></i></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Filtros -->
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-gradient-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0  text-dark">
                        <i class="bi bi-funnel"></i> Filtros
                    </h5>
                </div>
            </div>
            <div class="card-body text-center">
                <form method="get" class="mb-4">
                    <!-- mantiene el país seleccionado -->
                    <input type="hidden" name="pais" value="<?= htmlspecialchars($paisSel) ?>">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control"
                                name="Cliente"
                                value="<?= htmlspecialchars($_GET['Cliente'] ?? '') ?>"
                                placeholder="Buscar cliente">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Artículo</label>
                            <input type="text" class="form-control"
                                name="NumArticulo"
                                value="<?= htmlspecialchars($numArticulo) ?>"
                                placeholder="Buscar artículo">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <!-- limpia filtros pero mantiene el país -->
                            <a href="?pais=<?= $paisSel ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <br>
        <!-- Tabla con diseño homologado -->
        <div class="card border-0 shadow-lg text-center">
            <div class="card-header bg-gradient-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0  text-dark">
                        <i class="bi bi-cart-check me-2 text-dark"></i>Ventas – <?= htmlspecialchars($paisSel) ?>
                    </h5>
                    <!-- ①  Botón que abre el modal -->
                    <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExportar">
                        <i class="bi bi-file-earmark-arrow-down"></i> Exportar información
                    </button>

                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Cliente</th>
                                <th>Artículo</th>
                                <th class="text-end">Cant. Abierta</th>
                                <th class="text-end">Precio</th>
                                <th>F.Contab.</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($Registros as $index => $r): ?>
                                <tr class="position-relative">
                                    <td class="ps-4 fw-semibold text-muted"><?= $r['Id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">

                                            <div class="flex-grow-1 ms-2">
                                                <?= htmlspecialchars($r['Cliente']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badgebg-opacity-10 text-secondary">
                                            <?= htmlspecialchars($r['NumArticulo']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?= number_format($r['Cantidad_Abierta'], 2) ?>
                                    </td>
                                    <td class="text-end fw-semibold text-success">
                                        $<?= number_format($r['Precio'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= date('d/m/Y', strtotime($r['FechaContabilizacion'])) ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="detallesVentaPestañas.php?id=<?= $r['Id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Pie de tabla con paginación (opcional) -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando <?= count($Registros) ?> registros
                    </div>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">

                            <!-- enlace Anterior -->
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    «
                                </a>
                            </li>

                            <!-- enlaces numéricos -->
                            <?php for ($p = 1; $p <= $totalPag; $p++): ?>
                                <?php
                                // mostrar solo 2 antes/2 después de la página actual
                                if (
                                    $p == 1 || $p == $totalPag ||
                                    ($p >= $page - 5 && $p <= $page + 5)
                                ):
                                ?>
                                    <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php
                                elseif ($p == 2 || $p == $totalPag - 1):
                                    // puntos suspensivos
                                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                                endif;
                                ?>
                            <?php endfor; ?>

                            <!-- enlace Siguiente -->
                            <li class="page-item <?= $page >= $totalPag ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    »
                                </a>
                            </li>

                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- ②  Modal -->
        <div class="modal fade" id="modalExportar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-globe2 me-2"></i> Exportar registros</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <p class="mb-3">Selecciona la plaza que deseas exportar:</p>

                        <!-- Radios -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="pais" id="expGDL" value="GDL">
                            <label class="form-check-label" for="expGDL">Guadalajara</label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="pais" id="expCDMX" value="MX">
                            <label class="form-check-label" for="expCDMX">CDMX</label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="pais" id="expPA" value="PA">
                            <label class="form-check-label" for="expPA">Panamá</label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="pais" id="expGT" value="GT">
                            <label class="form-check-label" for="expGT">Guatemala </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="pais" id="expUS" value="US">
                            <label class="form-check-label" for="expUS">Estados Unidos </label>
                        </div>

                        <hr>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pais" id="expAll" value="ALL" checked>
                            <label class="form-check-label fw-bold" for="expAll">Todos los países</label>
                        </div>

                    </div><!-- /modal‑body -->

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="btnExportar" class="btn btn-success">
                            <i class="bi bi-download me-1"></i> Descargar
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnExportar').addEventListener('click', () => {
            // valor del radio seleccionado
            const pais = document.querySelector('input[name="pais"]:checked').value;
            // redirección a tu script de exportación
            window.location.href = 'exportarVentas.php?pais=' + encodeURIComponent(pais);
        });
    </script>


</body>

</html>