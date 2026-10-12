<?php
// includes/auth.php → ESTE ES EL ARCHIVO CORRECTO 100%

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si está logueado
function is_admin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Cerrar sesión si viene ?logout=1
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['rol'] )) {
        session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}