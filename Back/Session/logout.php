<?php
// Step 1: Start the session
session_start();

// Step 2: Destroy the session
session_destroy();

// Step 3: Redirect to the login page
header('Location: /index.php'); // Replace with the path to your login page
exit(); // Ensure no further code is executed after the redirect
?>