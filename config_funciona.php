<?php
// config.php – CON PROTECCIÓN ANTI-REDECLARACIÓN (nunca más ese error)
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    // Solo inicia sesión si no está ya iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

}

    $host     = 'localhost';//'127.0.0.1:3307';   // ← puerto correcto de Laragon
    $dbname   = 'juntavecinos';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
    //var_dump($pdo);
//exit('hola');
    // Funciones protegidas
    if (!function_exists('redirect')) {
        function redirect($url) {
            header("Location: index.php?url=$url");
            exit;
        }
    }

    if (!function_exists('is_admin')) {
        function is_admin() {
            return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
        }
    }

?>