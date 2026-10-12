<?php
// config.php
session_start();

$host = 'localhost';
$db   = 'juntavecinos';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin() {
    return isset($_SESSION['admin']);
}

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
    $calc_dv = ($calc_dv == 11) ? '0' : (($calc_dv == 10) ? 'K' : $calc_dv);
    return $dv == $calc_dv;
}