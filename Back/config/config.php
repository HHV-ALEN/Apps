<?php
// Variables para el entorno Local
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'alenapps');

//u617278495_Alen_Apps
//Alen2025
/// Entorno Publico
/*
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'u617278495_Alen_Apps');
define('DB_PASSWORD', 'Alen2025');
define('DB_NAME', 'u617278495_AlenApps');
*/

// Function to establish a PDO connection
function connectDB() {
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);

        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to establish a MySQLi connection
function connectMySQLi() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("MySQLi Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
