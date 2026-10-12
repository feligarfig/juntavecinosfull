<?php
// config/_app.php
define('BASE_URL', 'http://localhost/juntavecinos');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'juntavecinos');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función helper: redirigir
function redirect($url) {
    header("Location: " . BASE_URL . "/" . ltrim($url, '/'));
    exit;
}

// Validar RUT chileno
function validar_rut($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    if (strlen($rut) < 2) return false;
    $dv = strtoupper(substr($rut, -1));
    $num = substr($rut, 0, -1);
    $sum = 0; $mul = 2;
    for ($i = strlen($num) - 1; $i >= 0; $i--) {
        $sum += $num[$i] * $mul;
        $mul = ($mul == 7) ? 2 : $mul + 1;
    }
    $calc_dv = 11 - ($sum % 11);
    $calc_dv = ($calc_dv == 11) ? '0' : ( ($calc_dv == 10) ? 'K' : $calc_dv );
    return $dv == $calc_dv;
}