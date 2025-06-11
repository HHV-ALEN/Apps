<?php 
include '../../../Back/config/config.php';
$conn = connectMySQLi();
session_start(); // ¡No olvides esto!
print_r($_POST); // Para depurar, puedes eliminarlo después

$id_usuario = $_POST['id_usuario'];
$Nombre = $_POST['nombre'];
$Correo = $_POST['email'];
$Fecha_Ingreso = $_POST['antiguedad'];
$Jerarquia = $_POST['jerarquia'];
$Area = $_POST['area'];
$Rol = $_POST['rol'];
$Sucursal = $_POST['sucursal'];
$Departamento = $_POST['departamento'];

echo "<strong>Información del Formulario</strong>";
echo "<br><strong>-------------------------------------</strong>";
echo "<br><strong>ID Usuario: </strong>" . htmlspecialchars($id_usuario);
echo "<br><strong>Nombre: </strong>" . htmlspecialchars($Nombre);
echo "<br><strong>Correo: </strong>" . htmlspecialchars($Correo);
echo "<br><strong>Fecha de Ingreso: </strong>" . htmlspecialchars($Fecha_Ingreso);
echo "<br><strong>Jerarquía: </strong>" . htmlspecialchars($Jerarquia);
echo "<br><strong>Área: </strong>" . htmlspecialchars($Area);
echo "<br><strong>Rol: </strong>" . htmlspecialchars($Rol);
echo "<br><strong>Sucursal: </strong>" . htmlspecialchars($Sucursal);
echo "<br><strong>Departamento: </strong>" . htmlspecialchars($Departamento);
echo "<hr>";

// Actualizar el usuario en la base de datos
$sql = "UPDATE usuarios SET Nombre='$Nombre', Email='$Correo', Fecha_Ingreso='$Fecha_Ingreso', Jerarquia='$Jerarquia', Area='$Area', Rol='$Rol', Sucursal='$Sucursal', Departamento='$Departamento' WHERE Id='$id_usuario'";
$result = $conn->query($sql);

if ($result) {
    echo "Usuario actualizado correctamente.";
    $_SESSION['respuesta'] = "✅ El usuario <strong>$Nombre</strong> fue actualizado correctamente.";
    $_SESSION['tipo_respuesta'] = "success";

} else {
    echo "Error al actualizar el usuario: " . $conn->error;
    $_SESSION['respuesta'] = "❌ Error al actualizar el usuario <strong>$Nombre</strong>. Intenta nuevamente.";
    $_SESSION['tipo_respuesta'] = "danger";
}

header ("Location: ../Users.php");


?>