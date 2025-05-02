<?php
//print_r($_SESSION);
$Nombre = $_SESSION['Name'];
// Cortar Solo la primera palabra del nombre
$Nombre = explode(" ", $Nombre)[0];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Imagen de icono -->
    <link rel="icon" type="image/png" href="Front/Img/Icono-A.png" />
    <style>
        /* Custom styling */
        .user-greeting {
            font-family: 'Segoe UI', Roboto, sans-serif;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            display: inline-flex;
            align-items: center;
        }

        /* Hover effect */
        .user-greeting:hover {
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }

        /* For mobile view */
        @media (max-width: 991.98px) {
            .user-greeting {
                margin-left: auto;
                margin-right: 1rem;
            }
        }

        /* Ajustar tamaño del logo */
        .navbar-brand img {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        /* Navbar styling */
        .navbar {
            background: linear-gradient(135deg, #343a40 0%, #212529 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
        }

        /* Nav items styling */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Logout button styling */
        .btn-logout {
            background-color: #dc3545;
            color: white;
            font-weight: 500;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-logout:hover,
        .btn-logout:focus {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }


        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                padding: 1rem 0;
            }

            .navbar-nav {
                gap: 0.5rem;
            }

            .btn-logout {
                margin-top: 0.5rem;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/Front/dashboard.php">
                <img src="/Front/Img/logo.png" alt="Alen Apps" style="height: 40px;">
            </a>
            <!-- User Greeting - Hidden on mobile -->
            <span class="navbar-text ms-3 d-none d-lg-flex">
                <span class="user-greeting">
                    <i class="bi bi-person-circle me-2"></i>
                    <span class="">Bienvenido,&nbsp;</span>
                    <span class="fw-semibold text-white"> <?php echo htmlspecialchars($Nombre); ?></span>
                </span>
            </span>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".navbar-collapse" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/Front/dashboard.php">
                            <i class="bi bi-house-heart-fill"></i> Inicio
                        </a>
                    </li>
                    <?php
                    if ($_SESSION['Area'] == 'Cadena De Suministros') {
                    ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/SupplyChain/index.php">
                                <i class="bi bi-list-task"></i> Listado General
                            </a>
                        </li>
                    <?php }
                    if ($_SESSION['Departamento'] == 'Recursos Humanos') { ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-list-ul"></i> Listados Generales
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/Front/Users/Users.php"><i class="bi bi-people"></i> Listado de Usuarios</a></li>
                                <li><a class="dropdown-item" href="/Vacaciones/index.php"><i class="bi bi-calendar-heart"></i> Listado de Vacaciones</a></li>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-logout" href="/Back/Session/logout.php">
                            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>

</html>