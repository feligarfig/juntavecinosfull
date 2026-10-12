<?php
require_once 'config.php';

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Buscar usuario por RUT o email
$stmt = $pdo->prepare("SELECT * FROM usuario
                       WHERE (rut = :usuario OR correo = :usuario)
                       AND activo = 1
                       LIMIT 1");
$stmt->execute(['usuario' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Usuario no existe
if (!$user) {
    redirect("login.php?error=Usuario no encontrado");
    exit;
}

// Comparar contraseña SIN hash (tu tabla usa texto plano)
if ($password !== $user['password']) {
    redirect("login.php?error=Contraseña incorrecta");
    exit;
}

// Guardar sesión REAL
$_SESSION['admin'] = true;
$_SESSION['rut'] = $user['rut'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['rol'] = $user['rol'];

redirect("dashboard.php");
