<?php
require_once("../../Back/config/config.php"); //Contiene las variables de configuracion para conectar a la base de datos
$conn = connectMySQLi();
session_start();


$busqueda = mysqli_real_escape_string($conn, $_GET['busqueda']);

$query = "SELECT * FROM vacaciones_general WHERE Usuario LIKE '%$busqueda%' ORDER BY Usuario ASC LIMIT 10";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . ucwords(strtolower($row['Usuario'])) . "</td>";
        echo "<td>" . $row['Dias_Restantes'] . "</td>";
        echo "<td>" . $row['Dias_Solicitados'] . "</td>";
        echo "<td>" . $row['Antiguedad'] . "</td>";
        echo '<td><a href="Front/detalles.php?Nombre=' . urlencode($row['Usuario']) . '" class="btn btn-info">Ver Detalles</a></td>';
        echo '<td><button class="btn btn-danger" onclick="deleteVacation(' . $row['Id'] . ')">Eliminar</button></td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No se encontraron resultados.</td></tr>";
}
