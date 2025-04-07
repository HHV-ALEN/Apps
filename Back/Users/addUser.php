<?php
include '../config/config.php';
session_start();

$Nombre_Responsable = $_SESSION['Name'];

$pdo = connectDB(); 
$conn = connectMySQLi();

echo "<br> ----------------------------------------> <strong> Información del Formulario: </strong>";
$Nombre = $_POST['nombre'];
$Username = $_POST['Username'];
$Correo = $_POST['Correo'];
$Contraseña = $_POST['Contraseña'];
$Antiguedad = $_POST['Antiguedad'];
$Rol = $_POST['Rol'];
$Area = $_POST['Area'];
$Puesto = $_POST['Puesto'];
$Sucursal = $_POST['Sucursal'];
$Jerarquia = $_POST['Jerarquia'];

echo "<br> <strong>Información de Usuario: </strong>";
echo "<br> ---> <strong> Nombre: </strong>" . $Nombre;
echo "<br> ---> <strong> Username: </strong>". $Username;
echo "<br> ---> <strong> Correo: </strong>". $Correo;
echo "<br> ---> <strong> Contraseña: </strong>". $Contraseña;
echo "<br> ---> <strong> Antiguedad: </strong>". $Antiguedad;
echo "<br> ---> <strong> Rol: </strong>". $Rol;
echo "<br> ---> <strong> Area: </strong>". $Area;
echo "<br> ---> <strong> Puesto: </strong>". $Puesto;
echo "<br> ---> <strong> Sucursal: </strong>". $Sucursal;
echo "<br> ---> <strong> Jerarquia: </strong>". $Jerarquia;

$encrypted = password_hash($Contraseña, PASSWORD_DEFAULT);
echo "<br> Contraseña Encrypto: " . $encrypted;
$Fecha_Actual = date("Y-m-d H:i:s");
echo "<br> ----------------------------------------> <strong> Registro del Usuario: </strong>";
/// Insertar Información de Usuario en la tabla: Usuarios
$query = "INSERT INTO usuarios (Nombre, Username, Email, Fecha_Ingreso, Jerarquia, Password, Area, Rol, Sucursal, Puesto, Estado) VALUES
    ('$Nombre', '$Username', '$Correo', '$Antiguedad', '$Jerarquia', '$encrypted', '$Area', '$Rol', '$Sucursal', '$Puesto', 'Activo')";
    mysqli_query($conn, $query);
    if (mysqli_affected_rows($conn) > 0) {
        echo "<br> Registro Exitoso! <br>";

        $Mensaje_Registro = "El usuario: " . $Nombre_Responsable . " Realizo el registro de : " . $Nombre . " Correctamente";
        $queryBitacora = "INSERT INTO bitacora (Accion, Fecha, Responsable) VALUES
        ('$Mensaje_Registro', NOW(), '$Nombre_Responsable')";
        mysqli_query($conn, $queryBitacora);
        mysqli_close($conn);
        header("Location: ../../Front/Users/Users.php");
    } else {
        echo "<br> Error al Registrar! <br>";
    }

?>
