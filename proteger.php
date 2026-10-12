<?php
// incluye/proteger.php
if (!defined('CONFIG_LOADED')) {
    require_once '../config.php';
}

// SI NO ESTÁ LOGUEADO COMO ADMIN → LO ECHAMOS AL INICIO
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Opcional: mensaje en consola para que sepas que alguien intentó entrar
    error_log("Acceso denegado a página protegida desde IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Lo mandamos al inicio con mensaje
    $_SESSION['error'] = "Acceso restringido. Debes iniciar sesión como administrador.";
    header("Location: ../index.php");
    exit;
}
?>