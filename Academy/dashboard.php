
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
  <title>Alen Academy - Mapa de Curso</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f0f4f8;
    }

    .map-container {
      position: relative;
      width: 100%;
      max-width: 1200px;
      margin: auto;
      overflow: hidden;
    }

    .map-bg {
      
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .modulo {
      position: absolute;
      width: 90px;
      height: 90px;
      cursor: pointer;
      transition: transform 0.3s ease;
      z-index: 2;
    }

    .modulo img {
      width: 100%;
      height: 100%;
    }

    .modulo:hover {
      transform: scale(1.1);
    }

    .locked img {
      filter: grayscale(100%) brightness(0.6);
    }

    /* Responsivo */
    @media (max-width: 768px) {
      .modulo {
        width: 32px;
        height: 32px;
      }
    }
  </style>
</head>
<body>
<?php include "../Front/navbar.php"; ?>

  <div class="map-container">
    <!-- Mapa de fondo (Tamaño completo) -->
    <img src="Front/img/Campus.png" alt="Mapa del Curso" class="map-bg">

    <!-- Módulo 1 (desbloqueado) -->
    <div class="modulo" id="modulo1" style="top: 1%; left: 21%;">
      <img src="Front/img/cofre.png" alt="Electrica Basica" style="cursor:pointer; width:100px;" 
     data-bs-toggle="modal" 
     data-bs-target="#modalElectrica">
    </div>

    <!-- Módulo 2 (bloqueado) -->
    <div class="modulo locked" id="modulo2" style="top: 22%; left: 45%;">
      <img src="Front/img/cofre.png" alt="Módulo 2">
    </div>

    <!-- Módulo 3 (bloqueado) -->
    <div class="modulo locked" id="modulo3" style="top: 38%; left: 78%;">
      <img src="Front/img/cofre.png" alt="Módulo 3">
    </div>

     <!-- Módulo 4 (bloqueado) -->
     <div class="modulo locked" id="modulo3" style="top: 65%; left: 75%;">
      <img src="Front/img/cofre.png" alt="Módulo 3">
    </div>

    <!-- Línea SVG de conexión -->
    <svg viewBox="0 0 1000 1000" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
      <path d="M 35 100 Q 500 270 450 300 Q 900 400 1050 450 Q 1000 700 1020 650 " stroke="#007bff" stroke-width="4" fill="none" stroke-dasharray="12 6">
        <animate attributeName="stroke-dashoffset" from="0" to="-100" dur="6s" repeatCount="indefinite" />
      </path>
    </svg>
  </div>


  <!-- Modal -->
<div class="modal fade" id="modalElectrica" tabindex="-1" aria-labelledby="modalElectricaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalElectricaLabel">Eléctrica Básica <i class="bi bi-lightning-fill"></i></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong><i class="bi bi-pencil-fill"></i> Descripción:</strong> Módulo para conocer sobre la electrónica básica que se genera en la empresa Alen.</p>
        <p><strong><i class="bi bi-clock"></i>Duración:</strong> 2 horas</p>
      </div>
      <div class="modal-footer justify-content-between">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <a href="modulos/electrica.php" class="btn btn-primary">Iniciar Módulo</a>
      </div>
    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simular que se desbloquea el módulo 2 después de 2 segundos
    setTimeout(() => {
      const modulo2 = document.getElementById('modulo2');
      modulo2.classList.remove('locked');
    }, 8000);
  </script>

</body>
</html>  
