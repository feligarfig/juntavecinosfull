<?php
// config.php  → FUNCIONA SÍ O SÍ con php -S localhost:8001
session_start();

// FORZAMOS conexión por TCP/IP + puerto explícito
$host     = '127.0.0.1';
$port     = 3306;           // casi siempre es 3306
$dbname   = 'juntavecinos';
$username = 'root';
$password = '12345';             // vacío en localhost

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Si llegas aquí → ¡CONECTÓ!
    // (Puedes borrar estas dos líneas cuando ya funcione)
    // echo "<small style='position:fixed;bottom:10px;right:10px;background:green;color:white;padding:5px;z-index:9999;'>DB OK</small>";

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . 
        "<br><br>→ Usa Laragon o XAMPP si sigue fallando (es más fácil)");
}

function redirect($url) {
    header("Location: index.php?url=$url");
    exit;
}

function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}
?>