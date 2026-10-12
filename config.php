<?php
// includes/config.php
if (defined('CONFIG_LOADED')) {
    return; // evita doble inclusión
}
define('CONFIG_LOADED', true);

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos (Laragon)
$host     = 'localhost';
$dbname   = 'juntavecinos';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de base de datos. Contacta al administrador.");
}

// Funciones útiles
function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}
?>