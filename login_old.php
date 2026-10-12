<?php
require_once 'config.php';

// Solo procesa si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar variables
    $usuario   = trim($_POST['usuario'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = "Debes ingresar usuario/email y contraseña.";
    } else {
        // Busca por RUT o por email
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE (rut = ? OR email = ?) AND activo = 1 LIMIT 1");
        $stmt->execute([$usuario, $usuario]);
        $user = $stmt->fetch();

        if ($user) {
            // Si la contraseña está en texto plano (como tienes ahora)
            if ($password === $user['password']) {
                // Login exitoso
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id']  = $user['rut'];
                $_SESSION['nombre']   = $user['nombre'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['rol']      = $user['rol'];

                // Redirige al panel
                header("Location: dashboard.php");
                exit;
            }
            // Si en el futuro usas hash, descomenta esto:
            // } else if (password_verify($password, $user['password'])) {
            //     ... mismo código de arriba ...
            else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado o está inactivo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Junta La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-600 to-emerald-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-md">
        <h1 class="text-4xl font-bold text-center text-green-800 mb-2">La Flecha</h1>
        <p class="text-center text-green-600 mb-8">Junta de Vecinos</p>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">RUT o Email</label>
                <input type="text" name="usuario" required autofocus
                       class="w-full px-4 py-3 border rounded-lg focus:ring-4 focus:ring-green-400"
                       placeholder="11.111.111-1 o tu@email.com">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">Contraseña</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border rounded-lg focus:ring-4 focus:ring-green-400">
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg text-xl transition">
                Ingresar al Panel
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-8">
            © 2025 Junta de Vecinos La Flecha
        </p>
    </div>
</body>
</html>