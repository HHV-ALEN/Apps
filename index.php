<?php 
session_start();
// Check if there's an error message in the session
if (isset($_SESSION['error'])) {
  $errorMessage = $_SESSION['error'];
  unset($_SESSION['error']); // Clear the error message after assigning it to $errorMessage
} else {
  $errorMessage = ''; // No error message
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alen Apps</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- import css -->
   <link rel="stylesheet" href="Front/css/login.css">
   <link rel="icon" type="image/png" href="Front/Img/Icono-A.png" />
</head>
<body>
  <div class="login-card card p-4 shadow" id="loginCard" style="max-width: 400px; width: 100%;">
    <!-- Logo -->
    <img src="Back/SystemFiles/Alen.png" alt="Logo" class="img-fluid mx-auto d-block mb-4" style="max-width: 250px;">
        <!-- Error Message Display -->
  <!-- Error Message Display -->
  <?php if (!empty($errorMessage)): ?>
      <div class="error-label" id="errorLabel">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="Back/Session/login.php" method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Nombre de Usuario:</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Ingrese Nombre de Usuario" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña:</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su Contraseña" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
    </form>
    <hr class="my-4">
    <div class="text-center">
      <a href="" class="text-decoration-none text-primary">¿Olvidaste la contraseña?</a>
    </div>
  </div>

  <!-- Bootstrap JS (Optional, if you need Bootstrap JS components) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JS for fade-in animation -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const loginCard = document.getElementById("loginCard");
      setTimeout(() => {
        loginCard.classList.add("show");
      }, 100); // Delay the animation slightly for a smoother effect
    });

    document.addEventListener("DOMContentLoaded", function() {
      const errorLabel = document.getElementById("errorLabel");

      // Fade out the error label after 5 seconds (if it exists)
      if (errorLabel) {
        setTimeout(() => {
          errorLabel.classList.add("fade-out"); // Add fade-out animation
          setTimeout(() => {
            errorLabel.remove(); // Remove the label from the DOM after animation
          }, 500); // Wait for the animation to finish
        }, 5000); // Start fade-out after 5 seconds
      }
    });


  </script>
</body>
</html>