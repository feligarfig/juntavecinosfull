<?php
require_once 'config.php';

// --- LOGIN NORMAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $rut = trim($_POST['email'] ?? '');  // Usamos el campo como RUT
    $pass = $_POST['pass'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rut = ?");
    $stmt->execute([$rut]);
    $user = $stmt->fetch();

    if ($user && $pass === $user['contraseña']) {
        $_SESSION['admin'] = true;
        $_SESSION['nombre'] = $user['nombre'] ?? 'Directiva';
        $_SESSION['rut'] = $user['rut'];
        redirect('dashboard');
    } else {
        $error = "RUT o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Junta Vecinal La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-green-50 via-emerald-100 to-teal-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-md">
        <!-- Logo / Título -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-green-800">La Flecha</h1>
            <p class="text-green-600 text-lg">Junta de Vecinos</p>
        </div>

        <!-- Mensaje de error -->
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Mensaje de recuperación -->
        <?php if (isset($mensaje)): ?>
            <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-4 rounded-lg mb-6 text-center">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <!-- Formulario Login -->
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">RUT (sin puntos ni guion)</label>
                <input type="text" name="email" required placeholder="111111111" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña</label>
                <input type="password" name="pass" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold py-4 rounded-lg hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition duration-200 shadow-lg">
                Ingresar al Panel
            </button>
        </form>

        <!-- Recuperar contraseña -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600 mb-4">¿Olvidaste tu contraseña?</p>
            <form method="POST" class="space-y-4">
                <input type="text" name="rut_recuperar" required placeholder="Ingresa tu RUT" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center">
                <button type="submit" name="recuperar" 
                        class="text-green-600 font-semibold hover:text-green-800 underline">
                    Recuperar contraseña
                </button>
            </form>
            <?php if (isset($error_recuperar)): ?>
                <p class="text-red-600 text-sm mt-3"><?= $error_recuperar ?></p>
            <?php endif; ?>
        </div>

        <div class="mt-10 text-center text-xs text-gray-500">
            <p>© 2025 Junta de Vecinos La Flecha • Coihueco</p>
        </div>
    </div>
</body>
</html>