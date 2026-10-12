<?php
session_start();
require_once 'includes/db.php';

// Si ya está logueado → dashboard
if (!empty($_SESSION['rut_usuario'])) {
    header("Location: views/admin/dashboard.php");
    exit;
}

// Si no es POST → vuelve al login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

// Variables para mostrar errores en el formulario
$error_usuario = '';
$error_password = '';
$mensaje_error = '';

if ($usuario === '' || $password === '') {
    $mensaje_error = "Debe ingresar usuario/correo y contraseña";
} else {
    try {
        // Busca por RUT o correo
        $stmt = $pdo->prepare("
            SELECT rut_usuario, nombre, rol, contraseña 
            FROM usuario 
            WHERE rut_usuario = ? OR correo = ? 
            LIMIT 1
        ");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 1. USUARIO NO EXISTE
        if (!$user) {
            $error_usuario = "El RUT o correo no está registrado";
            sleep(1); // anti-bruteforce
        }
        // 2. USUARIO EXISTE → VERIFICAR CONTRASEÑA
        else {
            if (password_verify($password, $user['contraseña'])) {
                // CONTRASEÑA CORRECTA → LOGIN EXITOSO
                if (password_needs_rehash($user['contraseña'], PASSWORD_DEFAULT)) {
                    $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE usuario SET contraseña = ? WHERE rut_usuario = ?")
                        ->execute([$nuevo_hash, $user['rut_usuario']]);
                }

                session_regenerate_id(true);
                $_SESSION['rut_usuario'] = $user['rut_usuario'];
                $_SESSION['nombre']      = $user['nombre'];
                $_SESSION['rol']         = $user['rol'];
                $_SESSION['login_time']  = time();

                header("Location: views/admin/dashboard.php");
                exit;
            } else {
                // CONTRASEÑA INCORRECTA
                $error_password = "La contraseña es incorrecta";
                sleep(1);
            }
        }
    } catch (Exception $e) {
        error_log("Error login: " . $e->getMessage());
        $mensaje_error = "Error del servidor. Inténtalo más tarde.";
    }
}

// Si llegamos aquí → hay error → mostramos el formulario con mensajes
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(to bottom right, #ccfbf1, #99f6e4); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-6">

<div class="bg-white rounded-3xl shadow-2xl p-12 w-full max-w-md">
    <div class="text-center mb-10">
        <h1 class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
            JJVV LA FLECHA
        </h1>
        <p class="text-xl text-gray-600 mt-4">Sistema de Gestión</p>
    </div>

    <?php if ($mensaje_error): ?>
        <div class="bg-red-100 border-4 border-red-400 text-red-800 p-6 rounded-2xl text-center font-bold text-xl mb-8">
            <?= htmlspecialchars($mensaje_error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="space-y-8">
        <div>
            <label class="block text-xl font-bold text-gray-700 mb-3">RUT o Correo</label>
            <div class="relative">
                <input type="text" name="usuario" value="<?= htmlspecialchars($usuario) ?>" required
                       class="w-full pl-14 pr-6 py-5 text-xl rounded-2xl border-4 
                              <?= $error_usuario ? 'border-red-500' : 'border-emerald-300 focus:border-emerald-500' ?> 
                              focus:outline-none shadow-lg transition">
                <i class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-2xl text-emerald-600"></i>
            </div>
            <?php if ($error_usuario): ?>
                <p class="text-red-600 font-bold mt-3 text-lg"><?= htmlspecialchars($error_usuario) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-xl font-bold text-gray-700 mb-3">Contraseña</label>
            <div class="relative">
                <input type="password" name="password" required
                       class="w-full pl-14 pr-6 py-5 text-xl rounded-2xl border-4 
                              <?= $error_password ? 'border-red-500' : 'border-emerald-300 focus:border-emerald-500' ?> 
                              focus:outline-none shadow-lg transition">
                <i class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-2xl text-emerald-600"></i>
            </div>
            <?php if ($error_password): ?>
                <p class="text-red-600 font-bold mt-3 text-lg"><?= htmlspecialchars($error_password) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 
                       text-white font-black text-2xl py-6 rounded-2xl shadow-2xl transition transform hover:scale-105">
            Ingresar
        </button>
    </form>

    <div class="text-center mt-8 text-gray-600">
        <p>¿Olvidaste tu contraseña? <a href="#" class="text-emerald-600 font-bold hover:underline">Recuperar</a></p>
    </div>
</div>

</body>
</html>