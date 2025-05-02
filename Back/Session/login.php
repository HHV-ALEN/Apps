<?php
session_start();
require_once '../config/config.php';
$pdo = connectDB(); 

$username = trim($_POST['username']);
$password = trim($_POST['password']);

try {
    
    $query = "SELECT * FROM usuarios WHERE Username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['User_Id'] = $user['Id'];
            $_SESSION['Name'] = $user['Nombre'];
            $_SESSION['Username'] = $user['Username']; 
            $_SESSION['Mail'] = $user['Email'];
            $_SESSION['Date'] = $user['Fecha_Ingreso'];
            $_SESSION['Area'] = $user['Area'];
            $_SESSION['Role'] = $user['Rol'];
            $_SESSION['Sucursal'] = $user['Sucursal'];
            $_SESSION['Departamento'] = $user['Departamento'];
            echo "<br> ---> <strong> Login successful! Welcome, " . $_SESSION['Name'] . ".</strong>";

            header('Location: ../../Front/dashboard.php');
            exit();
             
        } else {
            // back to login -> Sending "Incorrect Password"
            echo "<br> ---> <strong> Incorrect password! </strong>";
            $_SESSION['error'] = "Contraseña Incorrecta!";
            header('Location: ../../index.php');
        }
    } else {
        echo "<br> ---> <strong> Username not found! </strong>";
        // back to login -> Sending "Username not found"
        $_SESSION['error'] = "Usuario No encontrado!";
        header('Location: ../../index.php');
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
