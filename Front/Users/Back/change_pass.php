<?php 
include '../../../Back/config/config.php';
$conn = connectMySQLi();
session_start();
session_start(); // ¡No olvides esto!

$Nombre = $_SESSION['Name'];
$currentPassword = $_POST['currentPassword'];
$newPassword = $_POST['newPassword'];
$confirmPassword = $_POST['confirmPassword'];

// 1. Buscar usuario con prepared statement
$stmt = $conn->prepare("SELECT Password FROM usuarios WHERE Nombre = ?");
$stmt->bind_param("s", $Nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $storedPassword = $row['Password'];

    // 2. Verificar contraseña actual
    if (password_verify($currentPassword, $storedPassword)) {

        // 3. Validar que las nuevas coincidan
        if ($newPassword === $confirmPassword) {
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // 4. Actualizar contraseña con prepared statement
            $updateStmt = $conn->prepare("UPDATE usuarios SET Password = ? WHERE Nombre = ?");
            $updateStmt->bind_param("ss", $hashedNewPassword, $Nombre);

            if ($updateStmt->execute()) {
                $_SESSION['mensaje'] = "✅ <strong>Contraseña actualizada correctamente.</strong>";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ <strong>Error al actualizar la contraseña.</strong> Intenta nuevamente.";
                $_SESSION['tipo_mensaje'] = "danger";
            }

        } else {
            $_SESSION['mensaje'] = "⚠️ <strong>Las nuevas contraseñas no coinciden.</strong>";
            $_SESSION['tipo_mensaje'] = "warning";
        }

    } else {
        $_SESSION['mensaje'] = "❌ <strong>La contraseña actual no es correcta.</strong>";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} else {
    $_SESSION['mensaje'] = "❌ <strong>Usuario no encontrado.</strong>";
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirige de regreso al formulario
header("Location: ../../dashboard.php");
exit();


?>