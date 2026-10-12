<?php
// config.php – 100 % FUNCIONANDO EN TU LARAGON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = '127.0.0.1:3307';
$dbname   = 'juntavecinos';   // ← nombre correcto
$username = 'root';
$password = '12345';          // ← contraseña correcta

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function redirect($url) {
    header("Location: index.php?url=$url");
    exit;
}
function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}
?>